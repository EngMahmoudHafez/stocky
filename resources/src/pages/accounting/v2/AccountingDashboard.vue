<template>
  <div class="page finance-home">
    <PageHeader :title="labels.title" :breadcrumb="[$t('Accounting'), labels.title]" />

    <a-row :gutter="[16, 16]">
      <a-col v-if="actions.length" :xs="24" :lg="12">
        <a-card :title="labels.daily">
          <p>{{ labels.dailyHint }}</p>
          <div class="finance-links">
            <router-link
              v-for="link in actions" :key="link.path" :to="link.path"
              custom v-slot="{ href, navigate }"
            >
              <a-button :href="href" :type="link.primary ? 'primary' : 'default'" @click="navigate">{{ labels[link.key] }}</a-button>
            </router-link>
          </div>
        </a-card>
      </a-col>
      <a-col v-if="reports.length" :xs="24" :lg="12">
        <a-card :title="labels.reports">
          <p>{{ labels.reportsHint }}</p>
          <div class="finance-links">
            <router-link
              v-for="link in reports" :key="link.path" :to="link.path" custom v-slot="{ href, navigate }"
            >
              <a-button :href="href" @click="navigate">{{ labels[link.key] }}</a-button>
            </router-link>
          </div>
        </a-card>
      </a-col>
    </a-row>

    <a-collapse v-if="ledgerLinks.length" class="finance-ledger">
      <a-collapse-panel key="ledger" :header="labels.ledger">
        <p>{{ labels.ledgerHint }}</p>
        <a-alert v-if="error" type="error" :message="labels.loadError" show-icon>
          <template #action><a-button size="small" @click="load">{{ $t('Refresh') }}</a-button></template>
        </a-alert>
        <a-spin v-else :spinning="loading">
          <a-empty v-if="summary && !summary.journals_30d" :description="labels.emptyLedger" />
          <dl v-else-if="summary" class="finance-kpis">
            <div><dt>{{ labels.income }}</dt><dd>{{ money(summary.income_30d) }}</dd></div>
            <div><dt>{{ labels.expense }}</dt><dd>{{ money(summary.expense_30d) }}</dd></div>
            <div><dt>{{ labels.net }}</dt><dd>{{ money(summary.income_30d - summary.expense_30d) }}</dd></div>
          </dl>
        </a-spin>
        <div class="finance-links">
          <router-link
            v-for="link in ledgerLinks" :key="link.path" :to="link.path" custom v-slot="{ href, navigate }"
          >
            <a-button :href="href" @click="navigate">{{ labels[link.key] }}</a-button>
          </router-link>
        </div>
      </a-collapse-panel>
    </a-collapse>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PageHeader from '../../../components/PageHeader.vue';
import { useFormat } from '../../../composables/useFormat';
import { useAuthStore } from '../../../stores/auth';
import http from '../../../lib/http';

const { locale } = useI18n();
const { money } = useFormat();
const auth = useAuthStore();
const loading = ref(false);
const error = ref(false);
const summary = ref(null);

const messages = {
  ar: {
    title: 'نظرة مالية', daily: 'التعاملات اليومية', dailyHint: 'سجّل المصروفات والإيداعات وتابع أرصدة الحسابات.',
    expenseCreate: 'تسجيل مصروف', depositCreate: 'تسجيل إيداع', accounts: 'أرصدة الحسابات',
    expenses: 'المصروفات', deposits: 'الإيداعات', transfer: 'تحويل بين الحسابات',
    reports: 'التقارير المالية', reportsHint: 'راجع حركة النقد والربحية من عمليات البيع والشراء.',
    cashFlow: 'حركة النقد', operationalProfit: 'ربحية التشغيل', expenseReport: 'تقرير المصروفات', depositReport: 'تقرير الإيداعات',
    ledger: 'القيود والتقارير المحاسبية', ledgerHint: 'ملخص القيود المحاسبية لآخر ٣٠ يومًا.',
    chart: 'دليل الحسابات', journals: 'القيود اليومية', trial: 'ميزان المراجعة',
    ledgerProfit: 'الأرباح والخسائر المحاسبية', balance: 'الميزانية العمومية', tax: 'الملخص الضريبي',
    income: 'الإيرادات المرحّلة', expense: 'المصروفات المرحّلة', net: 'صافي القيود المرحّلة',
    emptyLedger: 'لا توجد قيود محاسبية في آخر ٣٠ يومًا.', loadError: 'تعذر تحميل ملخص القيود.',
  },
  en: {
    title: 'Financial overview', daily: 'Daily transactions', dailyHint: 'Record expenses and deposits, and check account balances.',
    expenseCreate: 'Record expense', depositCreate: 'Record deposit', accounts: 'Account balances',
    expenses: 'Expenses', deposits: 'Deposits', transfer: 'Transfer between accounts',
    reports: 'Financial reports', reportsHint: 'Review cash activity and profit from sales and purchases.',
    cashFlow: 'Cash flow', operationalProfit: 'Operating profit', expenseReport: 'Expense report', depositReport: 'Deposit report',
    ledger: 'Journal and accounting reports', ledgerHint: 'Accounting entries for the last 30 days.',
    chart: 'Chart of accounts', journals: 'Journal entries', trial: 'Trial balance',
    ledgerProfit: 'Ledger profit and loss', balance: 'Balance sheet', tax: 'Tax summary',
    income: 'Posted income', expense: 'Posted expenses', net: 'Net posted amount',
    emptyLedger: 'There are no journal entries in the last 30 days.', loadError: 'Could not load the ledger summary.',
  },
};
const labels = computed(() => messages[locale.value === 'ar' ? 'ar' : 'en']);

function permitted(links) {
  return links.filter(link => auth.can(link.permission) && auth.adminPathEnabled(link.path));
}

const actions = computed(() => permitted([
  { key: 'expenseCreate', path: '/expenses/create', permission: 'expense_add', primary: true },
  { key: 'depositCreate', path: '/deposits/create', permission: 'deposit_add', primary: true },
  { key: 'accounts', path: '/accounts', permission: 'account' },
  { key: 'expenses', path: '/expenses', permission: 'expense_view' },
  { key: 'deposits', path: '/deposits', permission: 'deposit_view' },
  { key: 'transfer', path: '/transfer-money', permission: 'transfer_money' },
]));
const reports = computed(() => permitted([
  { key: 'cashFlow', path: '/reports/cash-flow', permission: 'cash_flow_report' },
  { key: 'operationalProfit', path: '/reports/profit-and-loss', permission: 'Reports_profit' },
  { key: 'expenseReport', path: '/reports/expenses', permission: 'expenses_report' },
  { key: 'depositReport', path: '/reports/deposits', permission: 'deposits_report' },
]));
const ledgerLinks = computed(() => permitted([
  { key: 'chart', path: '/accounting-v2/chart-of-accounts', permission: 'chart_of_accounts' },
  { key: 'journals', path: '/accounting-v2/journal-entries', permission: 'journal_entries' },
  { key: 'trial', path: '/accounting-v2/reports/trial-balance', permission: 'trial_balance' },
  { key: 'ledgerProfit', path: '/accounting-v2/reports/profit-and-loss', permission: 'accounting_profit_loss' },
  { key: 'balance', path: '/accounting-v2/reports/balance-sheet', permission: 'balance_sheet' },
  { key: 'tax', path: '/accounting-v2/reports/tax-report', permission: 'accounting_tax_report' },
]));

async function load() {
  loading.value = true;
  error.value = false;
  try {
    summary.value = (await http.get('accounting/v2/dashboard')).kpi;
  } catch {
    error.value = true;
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.finance-links { display: flex; flex-wrap: wrap; gap: 8px; }
.finance-links .ant-btn { display: inline-flex; align-items: center; min-height: 36px; }
.finance-ledger { margin-top: 16px; }
.finance-ledger .finance-links { margin-top: 12px; }
.finance-kpis { display: flex; flex-wrap: wrap; gap: 16px 28px; margin: 0; }
.finance-kpis dd { font-weight: 700; font-size: 18px; margin: 2px 0 0; }
</style>
