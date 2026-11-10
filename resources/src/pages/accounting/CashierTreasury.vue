<template>
  <div class="page cashier-workspace">
    <PageHeader :title="labels.title" :breadcrumb="[labels.title]">
      <template #actions>
        <a-space wrap>
          <router-link to="/pos"><a-button>{{ labels.pos }}</a-button></router-link>
          <a-button :loading="loading" @click="load()">{{ labels.refresh }}</a-button>
          <a-button v-if="workspace?.can_open && !ownOpenShift" type="primary" @click="openModal = true">{{ labels.open }}</a-button>
        </a-space>
      </template>
    </PageHeader>
    <a-alert v-if="error" type="error" show-icon :message="error" class="workspace-notice" />
    <a-alert v-if="workspace && !workspace.cashier_accounts.length && !ownOpenShift" type="info" show-icon class="workspace-notice" :message="labels.setup">
      <template v-if="workspace.can_configure_accounts" #action><router-link to="/accounts">{{ labels.accounts }}</router-link></template>
    </a-alert>
    <a-spin :spinning="loading">
      <template v-if="workspace">
        <a-card class="workspace-section">
          <label class="shift-label">{{ labels.shift }}
            <a-select :value="register?.id" :options="registerOptions" class="shift-select" :placeholder="labels.noShift" @change="changeShift" />
          </label>
          <p v-if="register" class="shift-meta">{{ register.user?.username }} · {{ register.warehouse?.name }} · {{ register.account?.account_name || labels.unlinked }} · {{ dateTime(register.opened_at) }}</p>
        </a-card>

        <template v-if="register && workspace.summary">
          <div class="cash-metrics">
            <a-card v-for="metric in metrics" :key="metric.key" size="small">
              <span>{{ labels[metric.key] }}</span>
              <strong>{{ money(metric.value) }}</strong>
            </a-card>
          </div>
          <a-alert v-if="!register.account_id" type="warning" show-icon :message="labels.legacy" class="workspace-notice" />
          <a-alert v-if="register.status === 'closed'" type="info" show-icon :message="labels.closed" class="workspace-notice" />
          <div v-if="register.status === 'open'" class="cash-operations">
            <a-card :title="labels.handover">
              <p>{{ labels.handoverHint }}</p>
              <a-form :model="handover" layout="vertical" @finish="submitHandover">
                <a-form-item :label="labels.destination" required>
                  <a-select v-model:value="handover.to_account_id" :options="treasuryOptions" :disabled="!register.account_id || busy" />
                </a-form-item>
                <a-form-item :label="labels.receiver" required>
                  <a-select v-model:value="handover.receiver_id" :options="receiverOptions" show-search option-filter-prop="label" :disabled="busy" />
                </a-form-item>
                <a-form-item :label="labels.amount" required>
                  <a-input-number v-model:value="handover.amount" :min="0.01" :precision="2" :max="availableCash" class="cash-input" :disabled="busy" />
                </a-form-item>
                <a-form-item :label="labels.notes"><a-textarea v-model:value="handover.notes" :maxlength="1000" :disabled="busy" /></a-form-item>
                <a-button html-type="submit" type="primary" :loading="busy" :disabled="!register.account_id || availableCash <= 0 || !handover.receiver_id || !handover.to_account_id || !handover.amount">{{ labels.submit }}</a-button>
              </a-form>
            </a-card>
            <a-card :title="labels.close">
              <p>{{ labels.closeHint }}</p>
              <dl class="closing-summary">
                <div><dt>{{ labels.expected }}</dt><dd>{{ money(workspace.summary.expected_cash) }}</dd></div>
                <div><dt>{{ labels.accountBalance }}</dt><dd>{{ workspace.summary.account_balance === null ? labels.unlinked : money(workspace.summary.account_balance) }}</dd></div>
              </dl>
              <a-form :model="closing" layout="vertical" @finish="submitClose">
                <a-form-item :label="labels.counted" required><a-input-number v-model:value="closing.counted_cash" :precision="2" :min="0" class="cash-input" :disabled="busy" /></a-form-item>
                <p v-if="closing.counted_cash !== null">{{ labels.difference }}: <strong>{{ money(closing.counted_cash - workspace.summary.expected_cash) }}</strong></p>
                <a-form-item :label="labels.notes"><a-textarea v-model:value="closing.notes" :maxlength="1000" :disabled="busy" /></a-form-item>
                <a-button html-type="submit" danger :loading="busy" :disabled="closing.counted_cash === null">{{ labels.close }}</a-button>
              </a-form>
            </a-card>
          </div>
          <a-card :title="labels.receipts" class="workspace-section">
            <a-table :columns="receiptColumns" :data-source="workspace.handovers" row-key="id" :pagination="{ pageSize: 10 }" :scroll="{ x: 720 }">
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'reference'">HT-{{ record.id }}</template>
                <template v-else-if="column.key === 'created_at'">{{ dateTime(record.created_at) }}</template>
                <template v-else-if="['amount', 'expected_cash_after'].includes(column.key)">{{ money(record[column.key]) }}</template>
              </template>
            </a-table>
          </a-card>
        </template>
        <a-empty v-else :description="labels.noShift" />
      </template>
    </a-spin>

    <a-modal v-model:open="openModal" :title="labels.open" :confirm-loading="busy" :ok-button-props="{ disabled: !opening.account_id || !opening.warehouse_id }" @ok="submitOpen">
      <a-form layout="vertical">
        <a-form-item :label="labels.warehouse" required><a-select v-model:value="opening.warehouse_id" :options="warehouseOptions" /></a-form-item>
        <a-form-item :label="labels.drawer" required><a-select v-model:value="opening.account_id" :options="drawerOptions" @change="selectDrawer" /></a-form-item>
        <a-form-item :label="labels.opening"><a-input-number :value="opening.opening_balance" disabled class="cash-input" :precision="2" /></a-form-item>
        <p>{{ labels.openingHint }}</p>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { message } from 'ant-design-vue';
import PageHeader from '../../components/PageHeader.vue';
import { useAuthStore } from '../../stores/auth';
import { useFormat } from '../../composables/useFormat';
import http from '../../lib/http';

const { locale } = useI18n();
const { money, dateTime } = useFormat();
const auth = useAuthStore();
const labels = computed(() => locale.value === 'ar' ? {
  title: 'يومية الكاشير والتوريد', pos: 'نقطة البيع', refresh: 'تحديث', open: 'فتح وردية', close: 'قفل اليومية',
  setup: 'جهّز حسابًا من نوع «درج كاشير» وحدد المستخدم المسؤول، وحسابًا من نوع «خزنة» من شاشة الحسابات.',
  accounts: 'إعداد الحسابات', shift: 'الوردية', noShift: 'لا توجد وردية. افتح وردية بعد تجهيز حساب الدرج.',
  unlinked: 'غير مرتبط بحساب', legacy: 'هذه وردية قديمة غير مربوطة بحساب. اقفلها ثم افتح وردية جديدة قبل تحصيل النقدي أو التوريد.',
  closed: 'الوردية مقفولة. سندات التوريد محفوظة للقراءة.', handover: 'توريد نقدية للخزنة',
  handoverHint: 'سجّل المبلغ الذي تم تسليمه فعليًا واسم المستلم. يتحدث الحسابان وعهدة الوردية معًا عند التأكيد.',
  destination: 'الخزنة المستلمة', receiver: 'اسم المستلم', amount: 'مبلغ التوريد', notes: 'ملاحظات', submit: 'تأكيد التوريد وتسجيل السند',
  closeHint: 'عدّ النقدية المتبقية في الدرج بعد التوريدات. الفيزا والآجل خارج هذا الحساب. القفل لا ينشئ توريدًا إضافيًا.',
  opening: 'عهدة الافتتاح', received: 'تحصيل نقدي', refunded: 'مرتجعات نقدية', handed: 'نقدية خارجة / توريدات',
  expected: 'المتوقع في الدرج', accountBalance: 'رصيد حساب الدرج', counted: 'النقدية المعدودة فعليًا', difference: 'الفرق',
  receipts: 'سندات التوريد', reference: 'رقم السند', date: 'وقت التوريد', after: 'المتبقي بالوردية',
  warehouse: 'الفرع', drawer: 'حساب درج الكاشير', openingHint: 'عهدة الافتتاح مأخوذة من رصيد الحساب. موّل الدرج بتحويل من الخزنة قبل فتح الوردية.',
  saved: 'تم التسجيل بنجاح', failed: 'تعذر تنفيذ العملية. راجع البيانات وحاول مجددًا.',
} : {
  title: 'Cashier shifts and treasury', pos: 'Point of sale', refresh: 'Refresh', open: 'Open shift', close: 'Close shift',
  setup: 'Configure a Cashier drawer account with its assigned user, and a Treasury account in Accounts.', accounts: 'Configure accounts',
  shift: 'Shift', noShift: 'No shift. Open one after configuring your drawer account.', unlinked: 'No linked account',
  legacy: 'This older shift has no account. Close it, then open a linked shift before receiving cash or handing it over.',
  closed: 'Shift closed. Handover receipts remain available to read.', handover: 'Hand cash over to treasury',
  handoverHint: 'Record cash physically delivered and its recipient. Both accounts and shift custody update together on confirmation.',
  destination: 'Receiving treasury', receiver: 'Recipient', amount: 'Handover amount', notes: 'Notes', submit: 'Confirm handover and record receipt',
  closeHint: 'Count cash remaining after handovers. Card and unpaid amounts are excluded. Closing does not create another transfer.',
  opening: 'Opening cash', received: 'Cash received', refunded: 'Cash refunds', handed: 'Cash out / handovers', expected: 'Expected drawer cash',
  accountBalance: 'Drawer account balance', counted: 'Actual counted cash', difference: 'Difference', receipts: 'Handover receipts',
  reference: 'Receipt', date: 'Delivered at', after: 'Shift cash remaining', warehouse: 'Branch', drawer: 'Cashier drawer account',
  openingHint: 'Opening cash comes from the account balance. Fund the drawer from treasury before opening.', saved: 'Recorded successfully', failed: 'Operation failed. Review the details and retry.',
});

const workspace = ref(null);
const loading = ref(false);
const busy = ref(false);
const error = ref('');
const openModal = ref(false);
const opening = ref({ warehouse_id: undefined, account_id: undefined, opening_balance: 0 });
const newHandover = () => ({ to_account_id: undefined, receiver_id: undefined, amount: null, notes: '', request_uuid: crypto.randomUUID() });
const handover = ref(newHandover());
const closing = ref({ counted_cash: null, notes: '' });
const register = computed(() => workspace.value?.current_register);
const ownOpenShift = computed(() => workspace.value?.registers.some(row => row.user_id === auth.user?.id && row.status === 'open'));
const registerOptions = computed(() => workspace.value?.registers.map(row => ({ value: row.id, label: `#${row.id} · ${row.user?.username} · ${row.warehouse?.name} · ${row.status}` })) || []);
const treasuryOptions = computed(() => workspace.value?.treasuries.map(row => ({ value: row.id, label: row.account_name })) || []);
const receiverOptions = computed(() => workspace.value?.receivers.map(row => ({ value: row.id, label: row.username })) || []);
const warehouseOptions = computed(() => workspace.value?.warehouses.map(row => ({ value: row.id, label: row.name })) || []);
const drawerOptions = computed(() => workspace.value?.cashier_accounts.map(row => ({ value: row.id, label: `${row.account_name} · ${money(row.balance)}` })) || []);
const availableCash = computed(() => Math.max(0, Math.min(workspace.value?.summary?.expected_cash || 0, workspace.value?.summary?.account_balance || 0)));
const metrics = computed(() => [{ key: 'opening', value: register.value.opening_balance },
  { key: 'received', value: workspace.value.summary.cash_received }, { key: 'refunded', value: workspace.value.summary.cash_refunded },
  { key: 'handed', value: workspace.value.summary.cash_out }, { key: 'expected', value: workspace.value.summary.expected_cash },
  ...(register.value.status === 'closed' ? [{ key: 'counted', value: register.value.closing_balance }, { key: 'difference', value: register.value.difference }] : [])]);
const receiptColumns = computed(() => [{ key: 'reference', title: labels.value.reference },
  { key: 'created_at', dataIndex: 'created_at', title: labels.value.date }, { key: 'receiver_name', dataIndex: 'receiver_name', title: labels.value.receiver },
  { key: 'to_account', dataIndex: 'to_account', title: labels.value.destination }, { key: 'amount', dataIndex: 'amount', title: labels.value.amount },
  { key: 'expected_cash_after', dataIndex: 'expected_cash_after', title: labels.value.after }, { key: 'notes', dataIndex: 'notes', title: labels.value.notes }]);

function failure(reason) {
  error.value = Object.values(reason?.data?.errors || {}).flat()[0] || reason?.data?.message || labels.value.failed;
}
async function load(registerId = register.value?.id) {
  loading.value = true;
  error.value = '';
  try {
    workspace.value = await http.get('cash-registers/workspace', { register_id: registerId });
  } catch (reason) { failure(reason); }
  finally { loading.value = false; }
}
async function changeShift(id) {
  handover.value = newHandover();
  closing.value = { counted_cash: null, notes: '' };
  await load(id);
}
function selectDrawer(id) {
  opening.value.opening_balance = workspace.value.cashier_accounts.find(row => row.id === id)?.balance || 0;
}
async function submitOpen() {
  if (busy.value) return;
  error.value = '';
  busy.value = true;
  try {
    const result = await http.post('cash-registers/open', opening.value);
    openModal.value = false;
    await load(result.register.id);
    message.success(labels.value.saved);
  } catch (reason) { failure(reason); }
  finally { busy.value = false; }
}
async function submitHandover() {
  if (busy.value) return;
  error.value = '';
  busy.value = true;
  try {
    await http.post('cash-registers/handover', { ...handover.value, register_id: register.value.id });
    handover.value = newHandover();
    await load();
    message.success(labels.value.saved);
  } catch (reason) { failure(reason); }
  finally { busy.value = false; }
}
async function submitClose() {
  if (busy.value) return;
  error.value = '';
  busy.value = true;
  try {
    await http.post('cash-registers/close-shift', { ...closing.value, register_id: register.value.id });
    closing.value = { counted_cash: null, notes: '' };
    await load();
    message.success(labels.value.saved);
  } catch (reason) { failure(reason); }
  finally { busy.value = false; }
}
onMounted(() => load());
</script>

<style scoped>
.cashier-workspace { max-width: 1400px; margin-inline: auto; }
.workspace-notice, .workspace-section { margin-bottom: 18px; }
.shift-label { display: grid; gap: 6px; max-width: 600px; }
.shift-select, .cash-input { width: 100%; }
.shift-meta { margin: 12px 0 0; opacity: .7; }
.cash-metrics { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
.cash-metrics strong { display: block; font-size: 22px; margin-top: 8px; overflow-wrap: anywhere; }
.cash-operations { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 18px; margin-bottom: 18px; }
.closing-summary div { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.closing-summary dd { font-weight: 600; margin: 0; }
@media (max-width: 1000px) { .cash-metrics { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 650px) { .cash-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); } .cash-operations { grid-template-columns: minmax(0, 1fr); } }
</style>
