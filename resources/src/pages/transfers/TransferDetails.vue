<template>
  <div class="page">
    <PageHeader :title="$t('TransferDetail')" :breadcrumb="[$t('ListTransfers'), $t('TransferDetail')]">
      <template #actions>
        <a-space wrap>
          <a-button @click="$router.push('/transfers')">
            <template #icon><ArrowLeftOutlined /></template>
            {{ $t('Back') }}
          </a-button>
          <a-button :loading="downloadingPdf" @click="downloadPdf">
            <template #icon><FilePdfOutlined /></template>
            {{ $t('DownloadPdf') }}
          </a-button>
          <a-button v-if="transfer.can_change_dispatch && auth.can('transfer_edit')" @click="$router.push(`/transfers/${transfer.id}/edit`)">
            <template #icon><EditOutlined /></template>
            {{ $t('Edit') }}
          </a-button>
        </a-space>
      </template>
    </PageHeader>

    <div v-if="loading" style="display: flex; justify-content: center; padding: 96px 0">
      <a-spin size="large" />
    </div>

    <template v-else>
      <a-card size="small" style="margin-bottom: 16px">
        <a-descriptions :column="{ xs: 1, md: 3 }" size="small">
          <a-descriptions-item :label="$t('Reference')">
            <strong>{{ transfer.Ref }}</strong>
          </a-descriptions-item>
          <a-descriptions-item :label="$t('date')">{{ dateTime(transfer.date) }}</a-descriptions-item>
          <a-descriptions-item :label="$t('Items')">{{ transfer.items }}</a-descriptions-item>
          <a-descriptions-item :label="$t('FromWarehouse')">{{ transfer.from_warehouse }}</a-descriptions-item>
          <a-descriptions-item :label="$t('ToWarehouse')">{{ transfer.to_warehouse }}</a-descriptions-item>
          <a-descriptions-item :label="$t('Total')">{{ money(transfer.GrandTotal) }}</a-descriptions-item>
          <a-descriptions-item :label="$t('Status')">
            <a-tag :color="receiptStatusColor(transfer.receipt_status) || (transfer.statut === 'completed' ? 'success' : 'warning')">
              {{ receiptStatusLabel(transfer.receipt_status, locale) || $t(transfer.statut === 'completed' ? 'complete' : transfer.statut === 'sent' ? 'Sent' : 'Pending') }}
            </a-tag>
          </a-descriptions-item>
          <a-descriptions-item v-if="transfer.dispatched_at" :label="labels.dispatchedBy">
            {{ transfer.dispatched_by }} · {{ dateTime(transfer.dispatched_at) }}
          </a-descriptions-item>
          <a-descriptions-item :label="$t('Approval')">
            <a-tag :color="!transfer.approval_status || transfer.approval_status === 'approved' ? 'success' : transfer.approval_status === 'pending' ? 'warning' : 'error'">
              {{
                !transfer.approval_status || transfer.approval_status === 'approved'
                  ? $t('Approved')
                  : transfer.approval_status === 'pending' ? $t('Pending_Approval') : $t('Rejected')
              }}
            </a-tag>
          </a-descriptions-item>
        </a-descriptions>
        <div v-if="transfer.note" style="margin-top: 8px; color: rgba(0, 0, 0, 0.45)">{{ transfer.note }}</div>
      </a-card>

      <a-card v-if="transfer.can_receive" size="small" :title="labels.checkReceipt" class="receipt-card">
        <p>{{ labels.instructions }}</p>
        <a-button :disabled="submitting" @click="matchAll">{{ labels.matchAll }}</a-button>
        <a-table
          :columns="receiptColumns" :data-source="outstandingLines" row-key="id"
          :pagination="false" :scroll="{ x: 'max-content' }" size="small" class="receipt-table"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'product'">{{ record.name }} <span class="muted">{{ record.code }}</span></template>
            <template v-else-if="column.key === 'expected'">{{ remaining(record) }} {{ record.unit }}</template>
            <template v-else-if="column.key === 'counted'">
              <a-input-number
                v-model:value="counts[record.id]" :min="0" :max="999999999.999" :precision="3"
                :disabled="submitting" :aria-label="`${labels.counted} ${record.code}`"
              />
            </template>
            <template v-else-if="column.key === 'variance'">{{ variance(record) ?? '—' }}</template>
          </template>
        </a-table>
        <a-alert v-if="hasExcess" type="warning" :message="labels.excess" show-icon class="receipt-card" />
        <a-form layout="vertical">
          <a-form-item :label="labels.notes">
            <a-textarea v-model:value="receiptNotes" :maxlength="2000" :rows="2" :disabled="submitting" />
          </a-form-item>
        </a-form>
        <a-space wrap>
          <a-button type="primary" :loading="submitting" :disabled="!allCounted" @click="submitReceipt('receive')">
            {{ labels.saveReceipt }}
          </a-button>
          <a-popconfirm :title="labels.rejectConfirm" :ok-text="labels.rejectRemaining" @confirm="submitReceipt('reject')">
            <a-button danger :disabled="submitting || !receiptNotes.trim()">{{ labels.rejectRemaining }}</a-button>
          </a-popconfirm>
        </a-space>
      </a-card>

      <a-card size="small" :body-style="{ padding: 0 }">
        <a-table
          :columns="itemColumns"
          :data-source="details"
          :pagination="false"
          size="middle"
          row-key="id"
          :scroll="{ x: 'max-content' }"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'product'">
              <div style="font-weight: 500">{{ record.name }}</div>
              <div class="muted">{{ record.code }}</div>
              <div v-if="record.is_batch_tracked && (record.batches || []).length">
                <a-tag v-for="(b, i) in record.batches" :key="i" style="margin-top: 2px">
                  {{ b.batch_number || b.batch_no || b }}
                </a-tag>
              </div>
            </template>
            <template v-else-if="column.key === 'quantity'">{{ record.quantity }} {{ record.unit }}</template>
            <template v-else-if="column.key === 'received'">{{ record.received_quantity }} {{ record.unit }}</template>
            <template v-else-if="column.key === 'remaining'">{{ remaining(record) }} {{ record.unit }}</template>
            <template v-else-if="column.key === 'total'">{{ money(record.total) }}</template>
          </template>
        </a-table>
      </a-card>

      <a-card v-if="receipts.length" :title="labels.history" size="small" class="receipt-history">
        <a-collapse>
          <a-collapse-panel v-for="receipt in receipts" :key="receipt.id"
            :header="`${receipt.action === 'reject' ? labels.rejectRemaining : labels.receiptEvent} · ${receipt.received_by || '—'} · ${dateTime(receipt.created_at)}`">
            <p v-if="receipt.notes">{{ receipt.notes }}</p>
            <a-table :columns="historyColumns" :data-source="receipt.lines" row-key="detail_id"
              :pagination="false" :scroll="{ x: 'max-content' }" size="small">
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'product'">{{ detailNames[record.detail_id] }}</template>
              </template>
            </a-table>
          </a-collapse-panel>
        </a-collapse>
      </a-card>
    </template>
  </div>
</template>

<script setup>
/**
 * GET transfers/{id} → {transfer, details}. PDF transfer_pdf/{id}.
 */
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import { ArrowLeftOutlined, EditOutlined, FilePdfOutlined } from '@ant-design/icons-vue';
import PageHeader from '../../components/PageHeader.vue';
import { useFormat } from '../../composables/useFormat';
import { useAuthStore } from '../../stores/auth';
import http from '../../lib/http';
import { receiptStatusColor, receiptStatusLabel } from './receiptStatus';

const { t, locale } = useI18n();
const { money, dateTime } = useFormat();
const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const transfer = ref({});
const details = ref([]);
const receipts = ref([]);
const counts = ref({});
const receiptNotes = ref('');
const submitting = ref(false);

const labels = computed(() => locale.value === 'ar' ? {
  checkReceipt: 'مطابقة الاستلام', dispatchedBy: 'أرسل بواسطة', matchAll: 'تأكيد مطابقة كل الكميات',
  instructions: 'عدّ القطع اللي وصلت فعليًا وسجّلها لكل صنف. الكمية المقبولة فقط هي اللي تدخل مخزون الفرع.',
  counted: 'العدد الفعلي', expected: 'المتوقع', variance: 'الفرق', received: 'تم استلامه', remaining: 'المتبقي',
  excess: 'العدد الفعلي أكبر من المتوقع في صنف واحد أو أكثر. الزيادة هتتسجل في الفرق، والمخزون هيتحدّث بالكمية المتوقعة فقط.',
  notes: 'الملاحظات (مطلوبة عند الرفض أو عدم استلام أي كمية)', saveReceipt: 'حفظ الاستلام',
  rejectRemaining: 'رفض الكمية المتبقية', rejectConfirm: 'تأكيد رفض الكمية المتبقية؟', receiptEvent: 'تسجيل استلام', history: 'سجل الاستلام',
} : {
  checkReceipt: 'Check receipt', dispatchedBy: 'Dispatched by', matchAll: 'Confirm all counts match',
  instructions: 'Count the items that arrived and enter the actual quantity for each line. Only accepted quantities enter destination stock.',
  counted: 'Physical count', expected: 'Expected', variance: 'Variance', received: 'Received', remaining: 'Outstanding',
  excess: 'The physical count exceeds the expected quantity. The excess is recorded as a variance and only the expected quantity enters stock.',
  notes: 'Notes (required when rejecting or receiving no stock)', saveReceipt: 'Save receipt',
  rejectRemaining: 'Reject outstanding quantity', rejectConfirm: 'Reject the outstanding quantity?', receiptEvent: 'Receipt recorded', history: 'Receipt history',
});

function remaining(line) {
  return Number((line.quantity - line.received_quantity).toFixed(3));
}

function variance(line) {
  const counted = counts.value[line.id];
  return counted == null ? null : Number((counted - remaining(line)).toFixed(3));
}

const outstandingLines = computed(() => details.value.filter(line => remaining(line) > 0));
const allCounted = computed(() => outstandingLines.value.every(line => counts.value[line.id] != null));
const hasExcess = computed(() => outstandingLines.value.some(line => variance(line) > 0));
const detailNames = computed(() => Object.fromEntries(details.value.map(line => [line.id, line.name])));

const itemColumns = computed(() => [
  { title: t('ProductName'), key: 'product' },
  { title: t('Quantity'), key: 'quantity', align: 'right' },
  ...(transfer.value.receipt_status ? [
    { title: labels.value.received, key: 'received', align: 'right' },
    { title: labels.value.remaining, key: 'remaining', align: 'right' },
  ] : []),
  { title: t('Total'), key: 'total', align: 'right' },
]);

const receiptColumns = computed(() => [
  { title: t('ProductName'), key: 'product' },
  { title: labels.value.expected, key: 'expected' },
  { title: labels.value.counted, key: 'counted' },
  { title: labels.value.variance, key: 'variance' },
]);

const historyColumns = computed(() => [
  { title: t('ProductName'), key: 'product' },
  { title: labels.value.expected, dataIndex: 'expected_quantity' },
  { title: labels.value.counted, dataIndex: 'counted_quantity' },
  { title: labels.value.received, dataIndex: 'accepted_quantity' },
  { title: labels.value.variance, dataIndex: 'variance' },
]);

function matchAll() {
  counts.value = Object.fromEntries(outstandingLines.value.map(line => [line.id, remaining(line)]));
}

async function submitReceipt(action) {
  if (submitting.value) return;
  submitting.value = true;
  try {
    await http.post(`transfers/${transfer.value.id}/receive`, {
      request_id: transfer.value.receipt_request_id,
      version: transfer.value.receipt_version,
      action,
      notes: receiptNotes.value,
      lines: outstandingLines.value.filter(line => counts.value[line.id] != null).map(line => ({
        detail_id: line.id,
        counted_quantity: counts.value[line.id],
      })),
    });
    await loadTransfer();
    message.success(t('Success'));
  } catch (error) {
    message.error(error?.data?.message || t('InvalidData'));
  } finally {
    submitting.value = false;
  }
}

const downloadingPdf = ref(false);
async function downloadPdf() {
  downloadingPdf.value = true;
  try {
    await http.download(`transfer_pdf/${transfer.value.id || route.params.id}`, `Transfer_${transfer.value.Ref || route.params.id}.pdf`);
  } catch (e) {
    message.error(t('InvalidData'));
  } finally {
    downloadingPdf.value = false;
  }
}

async function loadTransfer() {
  const data = await http.get(`transfers/${route.params.id}`);
  transfer.value = data.transfer;
  details.value = data.details;
  receipts.value = data.receipts;
  counts.value = {};
  receiptNotes.value = '';
}

onMounted(async () => {
  try {
    await loadTransfer();
  } catch (e) {
    message.error(t('InvalidData'));
    router.push('/transfers');
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.receipt-card { margin-bottom: 16px; }
.receipt-table { margin: 12px 0; }
.receipt-history { margin-top: 16px; }
.muted {
  color: rgba(0, 0, 0, 0.45);
  font-size: 12px;
}
</style>
