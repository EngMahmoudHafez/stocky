<template>
  <div class="overview" :style="themeColors">
    <header class="overview-header">
      <div>
        <h1>{{ t('title') }}</h1>
        <p>{{ t('description') }}</p>
      </div>
      <div class="overview-filters">
        <label>
          <span>{{ t('location') }}</span>
          <select v-model="warehouseId" :disabled="loading" @change="load">
            <option value="">{{ t('allLocations') }}</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
          </select>
        </label>
        <label>
          <span>{{ t('period') }}</span>
          <select v-model="period" :disabled="loading" @change="load">
            <option value="week">{{ t('week') }}</option>
            <option value="month">{{ t('month') }}</option>
          </select>
        </label>
        <a-button :loading="loading" @click="load">
          <template #icon><ReloadOutlined aria-hidden="true" /></template>
          {{ t('refresh') }}
        </a-button>
      </div>
    </header>

    <nav v-if="quickActions.length" class="quick-actions" :aria-label="t('quickActions')">
      <router-link v-for="action in quickActions" :key="action.key" :to="action.to" class="quick-action">
        <component :is="action.icon" aria-hidden="true" />
        <span>{{ t(action.key) }}</span>
        <ArrowRightOutlined class="action-arrow" :class="{ 'action-arrow-rtl': locale === 'ar' }" aria-hidden="true" />
      </router-link>
    </nav>

    <a-alert v-if="loadFailed" type="error" show-icon :message="t('loadFailed')" />
    <a-spin v-else :spinning="loading">
      <div v-if="snapshot" class="overview-sections">
        <section aria-labelledby="overview-period">
          <div class="section-heading">
            <h2 id="overview-period">{{ t('periodSummary') }}</h2>
            <span>{{ periodLabel }}</span>
          </div>
          <div class="summary-grid period-grid">
            <a-card v-for="metric in periodMetrics" :key="metric.key" size="small" class="summary-card period-card">
              <component :is="metric.icon" class="summary-icon" aria-hidden="true" />
              <div>{{ t(metric.key) }}</div>
              <strong dir="auto">{{ metric.key === 'invoices' ? number(snapshot.period_summary[metric.key]) : money(snapshot.period_summary[metric.key]) }}</strong>
              <small>{{ t(metric.hint) }}</small>
            </a-card>
          </div>
        </section>

        <section aria-labelledby="overview-today">
          <div class="section-heading">
            <h2 id="overview-today">{{ t('today') }}</h2>
            <span>{{ t('updated', { time: updatedTime }) }}</span>
          </div>
          <div class="summary-grid">
            <a-card v-for="metric in dailyMetrics" :key="metric.key" size="small" class="summary-card">
              <component :is="metric.icon" class="summary-icon" aria-hidden="true" />
              <div>{{ t(metric.key) }}</div>
              <strong dir="auto">{{ money(snapshot.today[metric.key]) }}</strong>
            </a-card>
          </div>
        </section>

        <a-card :title="t('branches')">
          <template #extra><span class="section-date">{{ periodLabel }}</span></template>
          <template v-if="snapshot.branches.length">
            <div class="branch-row branch-header" aria-hidden="true">
              <span>{{ t('location') }}</span>
              <span>{{ t('sales') }}</span>
              <span>{{ t('purchases') }}</span>
              <span>{{ t('currentStock') }}</span>
            </div>
            <ul class="overview-list">
              <li v-for="branch in snapshot.branches" :key="branch.id" class="branch-row">
                <strong class="branch-name">{{ branch.name }}</strong>
                <span><small class="compact-label">{{ t('sales') }}</small>{{ money(branch.sales) }}</span>
                <span><small class="compact-label">{{ t('purchases') }}</small>{{ money(branch.purchases) }}</span>
                <span><small class="compact-label">{{ t('currentStock') }}</small>{{ t('units', { count: number(branch.stock_units) }) }}</span>
              </li>
            </ul>
          </template>
          <a-empty v-else :description="t('empty')" />
        </a-card>

        <div class="overview-grid trend-grid">
          <a-card :title="t('trend')">
            <template #extra><span class="section-date">{{ periodLabel }}</span></template>
            <div class="view-filters" role="group" :aria-label="t('trend')">
              <a-button
                v-for="key in trendKeys"
                :key="key"
                size="small"
                :type="trendBy === key ? 'primary' : 'default'"
                :aria-pressed="trendBy === key"
                @click="trendBy = key"
              >{{ t(key) }}</a-button>
            </div>
            <apexchart
              v-if="hasTradingActivity"
              :key="`${period}-${warehouseId}-${trendBy}`"
              type="area"
              height="265"
              :options="chartOptions"
              :series="chartSeries"
            />
            <a-empty v-else :description="t('empty')" />
          </a-card>

          <a-card :title="t('payments')">
            <p class="card-description">{{ t('paymentScope') }}</p>
            <dl class="payment-list">
              <div>
                <dt>{{ t('received') }}</dt>
                <dd>{{ money(snapshot.payments.received) }}</dd>
              </div>
              <div>
                <dt>{{ t('sent') }}</dt>
                <dd>{{ money(snapshot.payments.sent) }}</dd>
              </div>
            </dl>
          </a-card>
        </div>

        <div class="overview-grid">
          <a-card :title="t('rankings')">
            <div class="view-filters" role="group" :aria-label="t('rankings')">
              <a-button
                v-for="key in rankingKeys"
                :key="key"
                size="small"
                :type="rankBy === key ? 'primary' : 'default'"
                :aria-pressed="rankBy === key"
                @click="rankBy = key"
              >{{ t(key) }}</a-button>
            </div>
            <p v-if="rankBy === 'customers'" class="card-description">{{ t('customerRevenue') }}</p>
            <ol v-if="ranking.length" class="overview-list ranking-list">
              <li v-for="(entry, index) in ranking" :key="index">
                <span>{{ entry.name }}</span>
                <strong>{{ rankBy === 'customers' ? money(entry.value) : t('units', { count: number(entry.value) }) }}</strong>
              </li>
            </ol>
            <a-empty v-else :description="t('empty')" />
          </a-card>

          <a-card :title="t('recent')">
            <ul v-if="snapshot.recent_sales.length" class="overview-list recent-list">
              <li v-for="sale in snapshot.recent_sales" :key="sale.id">
                <div>
                  <router-link v-if="auth.can('Sales_view')" :to="{ name: 'sale-details', params: { id: sale.id } }">{{ sale.reference }}</router-link>
                  <strong v-else>{{ sale.reference }}</strong>
                  <small>{{ [sale.customer, sale.warehouse, date(sale.date)].filter(Boolean).join(' · ') }}</small>
                </div>
                <strong class="recent-amount">{{ money(sale.total) }}</strong>
              </li>
            </ul>
            <a-empty v-else :description="t('empty')" />
          </a-card>
        </div>

        <a-card v-if="snapshot.low_stock !== null" :title="t('lowStock')">
          <template #extra>
            <router-link v-if="stockReportAllowed" to="/reports/quantity-alerts">{{ t('viewStockReport') }}</router-link>
          </template>
          <p class="card-description">{{ t('stockScope') }}</p>
          <ul v-if="snapshot.low_stock.length" class="overview-list stock-list">
            <li v-for="item in snapshot.low_stock" :key="item.id">
              <span class="stock-warning"><WarningOutlined aria-hidden="true" /></span>
              <div class="stock-description">
                <strong>{{ item.name }}</strong>
                <small>{{ [item.variant, item.warehouse].filter(Boolean).join(' · ') }}</small>
              </div>
              <div class="stock-quantity">
                <strong>{{ t('units', { count: number(item.quantity) }) }}</strong>
                <small>{{ t('stockThreshold', { count: number(item.threshold) }) }}</small>
              </div>
            </li>
          </ul>
          <a-empty v-else :description="t('stockHealthy')" />
        </a-card>
      </div>
      <div v-else class="initial-loading" aria-hidden="true" />
    </a-spin>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { theme } from 'ant-design-vue';
import { ReloadOutlined, ShoppingCartOutlined, ShoppingOutlined, RollbackOutlined, UndoOutlined, ArrowRightOutlined, FileTextOutlined, CalculatorOutlined, WalletOutlined, SwapOutlined, WarningOutlined, ShopOutlined } from '@ant-design/icons-vue';
import { useI18n } from 'vue-i18n';
import http from '../lib/http';
import { useAuthStore } from '../stores/auth';
import { useFormat } from '../composables/useFormat';
import { isEntryAllowed } from '../lib/saasAccess';
import { moduleKeyForPath } from '../config/modules';

const { t, locale } = useI18n({
  useScope: 'local',
  fallbackLocale: 'en',
  messages: {
    ar: {
      title: 'نظرة عامة على التشغيل',
      description: 'تابع مبيعات اليوم وقارن أداء الفروع والمخازن.',
      location: 'الفرع أو المخزن',
      allLocations: 'كل الفروع والمخازن',
      period: 'فترة المقارنة',
      week: 'آخر ٧ أيام',
      month: 'آخر ٣٠ يومًا',
      refresh: 'تحديث',
      loadFailed: 'تعذر تحميل لوحة التحكم. اضغط تحديث للمحاولة مرة أخرى.',
      today: 'ملخص اليوم',
      updated: 'آخر تحديث {time}',
      sales: 'المبيعات',
      purchases: 'المشتريات',
      sale_returns: 'مرتجعات المبيعات',
      purchase_returns: 'مرتجعات المشتريات',
      branches: 'مقارنة الفروع والمخازن',
      currentStock: 'المخزون الحالي',
      units: '{count} وحدة',
      trend: 'اتجاه المبيعات والمشتريات',
      payments: 'حركة المدفوعات',
      paymentScope: 'مدفوعات البيع والشراء والمرتجعات خلال الفترة.',
      received: 'المدفوعات الداخلة',
      sent: 'المدفوعات الخارجة',
      rankings: 'الأكثر مبيعًا وأفضل العملاء',
      products: 'المنتجات',
      categories: 'التصنيفات',
      sizes: 'المقاسات',
      colors: 'الألوان',
      materials: 'الخامات',
      seasons: 'المواسم',
      customers: 'العملاء',
      customerRevenue: 'ترتيب العملاء حسب إجمالي قيمة المبيعات.',
      recent: 'أحدث عمليات البيع',
      empty: 'لا توجد بيانات لهذا الاختيار.',
      cashierTreasury: 'يومية الكاشير والتوريد',
      quickActions: 'اختصارات العمل',
      newSale: 'فاتورة بيع جديدة',
      pointOfSale: 'نقطة البيع',
      onlineOrders: 'طلبات المتجر',
      stockTransfers: 'تحويلات المخزون',
      finance: 'الحسابات والتقارير',
      viewStockReport: 'تقرير نواقص المخزون',
      periodSummary: 'أداء الفترة المختارة',
      invoices: 'فواتير البيع',
      average_sale: 'متوسط الفاتورة',
      unpaid: 'متبقي للتحصيل',
      completedSalesHint: 'قيمة المبيعات المكتملة',
      invoicesHint: 'عدد الفواتير المكتملة',
      averageHint: 'المبيعات ÷ عدد الفواتير',
      unpaidHint: 'المتبقي من فواتير الفترة',
      lowStock: 'أصناف تحتاج متابعة',
      stockScope: 'أقل خمسة أرصدة وصلت لحد التنبيه في الفروع المختارة. الأرصدة الحالية لا ترتبط بفترة المبيعات.',
      stockThreshold: 'حد التنبيه: {count}',
      stockHealthy: 'لا توجد أصناف عند حد التنبيه في الفروع المختارة.',
    },
    en: {
      title: 'Business overview',
      description: 'Track today’s sales and compare branches and warehouses.',
      location: 'Branch or warehouse',
      allLocations: 'All branches and warehouses',
      period: 'Comparison period',
      week: 'Last 7 days',
      month: 'Last 30 days',
      refresh: 'Refresh',
      loadFailed: 'Could not load the dashboard. Select Refresh to try again.',
      today: 'Today at a glance',
      updated: 'Last updated {time}',
      sales: 'Sales',
      purchases: 'Purchases',
      sale_returns: 'Sales returns',
      purchase_returns: 'Purchase returns',
      branches: 'Branch and warehouse comparison',
      currentStock: 'Current stock',
      units: '{count} units',
      trend: 'Sales and purchase trend',
      payments: 'Payment activity',
      paymentScope: 'Payments for sales, purchases and returns in this period.',
      received: 'Payments received',
      sent: 'Payments sent',
      rankings: 'Sales rankings',
      products: 'Products',
      categories: 'Categories',
      sizes: 'Sizes',
      colors: 'Colors',
      materials: 'Materials',
      seasons: 'Seasons',
      customers: 'Customers',
      customerRevenue: 'Customers ranked by total sales value.',
      recent: 'Latest sales',
      empty: 'No data for this selection.',
      cashierTreasury: 'Cashier shifts and treasury',
      quickActions: 'Quick actions',
      newSale: 'New sales invoice',
      pointOfSale: 'Point of sale',
      onlineOrders: 'Online orders',
      stockTransfers: 'Stock transfers',
      finance: 'Accounts and reports',
      viewStockReport: 'Low stock report',
      periodSummary: 'Selected period at a glance',
      invoices: 'Sales invoices',
      average_sale: 'Average invoice',
      unpaid: 'Awaiting collection',
      completedSalesHint: 'Completed sales value',
      invoicesHint: 'Completed invoices',
      averageHint: 'Sales divided by invoice count',
      unpaidHint: 'Unpaid balance on period invoices',
      lowStock: 'Stock to follow up',
      stockScope: 'Five lowest balances at or below their alert threshold in selected locations. Current stock is independent of the sales period.',
      stockThreshold: 'Alert threshold: {count}',
      stockHealthy: 'No items at the alert threshold in selected locations.',
    },
  },
});

const auth = useAuthStore();
const { date, money } = useFormat();
const { token } = theme.useToken();
const themeColors = computed(() => ({
  '--overview-muted': token.value.colorTextSecondary,
  '--overview-line': token.value.colorBorderSecondary,
  '--overview-accent': token.value.colorPrimary,
  '--overview-control-bg': token.value.colorBgContainer,
  '--overview-control-border': token.value.colorBorder,
  '--overview-text': token.value.colorText,
}));

const dailyMetrics = [
  { key: 'sales', icon: ShoppingCartOutlined },
  { key: 'purchases', icon: ShoppingOutlined },
  { key: 'sale_returns', icon: RollbackOutlined },
  { key: 'purchase_returns', icon: UndoOutlined },
];
const periodMetrics = [
  { key: 'sales', icon: ShoppingCartOutlined, hint: 'completedSalesHint' },
  { key: 'invoices', icon: FileTextOutlined, hint: 'invoicesHint' },
  { key: 'average_sale', icon: CalculatorOutlined, hint: 'averageHint' },
  { key: 'unpaid', icon: WalletOutlined, hint: 'unpaidHint' },
];
const actionEntries = [
  { key: 'cashierTreasury', to: '/cashier-treasury', permissions: ['Pos_view', 'account', 'transfer_money', 'accounting_dashboard'], icon: WalletOutlined },
  { key: 'newSale', to: '/sales/create', permissions: ['Sales_add'], icon: FileTextOutlined },
  { key: 'pointOfSale', to: '/pos', permissions: ['Pos_view'], icon: ShoppingCartOutlined },
  { key: 'onlineOrders', to: '/store/orders', permissions: ['Orders_view'], icon: ShopOutlined },
  { key: 'stockTransfers', to: '/transfers', permissions: ['transfer_view'], icon: SwapOutlined },
  { key: 'finance', to: '/accounting-v2/dashboard', permissions: ['accounting_dashboard'], icon: WalletOutlined },
  { key: 'viewStockReport', to: '/reports/quantity-alerts', permissions: ['Reports_quantity_alerts'], icon: WarningOutlined },
];
function actionAllowed(action) {
  return isEntryAllowed(action, auth)
    && auth.moduleEnabled(moduleKeyForPath(action.to))
    && auth.adminPathEnabled(action.to);
}
const quickActions = computed(() => actionEntries.filter(actionAllowed));
const stockReportAllowed = computed(() => actionAllowed(actionEntries.find(action => action.key === 'viewStockReport')));
const rankingKeys = ['products', 'categories', 'sizes', 'colors', 'materials', 'seasons', 'customers'];
const trendKeys = ['sales', 'purchases'];
const period = ref('month');
const warehouseId = ref('');
const warehouses = ref([]);
const rankBy = ref('products');
const trendBy = ref('sales');
const snapshot = ref(null);
const loading = ref(false);
const loadFailed = ref(false);
let refreshTimer;

const periodLabel = computed(() => t(period.value));
const ranking = computed(() => snapshot.value?.rankings[rankBy.value] ?? []);
const updatedTime = computed(() => new Intl.DateTimeFormat(locale.value, {
  hour: 'numeric', minute: '2-digit', timeZone: auth.user?.timezone || undefined,
}).format(new Date(snapshot.value.updated_at)));
const quantityFormat = computed(() => new Intl.NumberFormat(locale.value, { maximumFractionDigits: 3 }));

function number(value) {
  return quantityFormat.value.format(Number(value));
}

const trendValues = computed(() => snapshot.value?.trend.map(day => day[trendBy.value]) ?? []);
const hasTradingActivity = computed(() => trendValues.value.some(value => value !== 0));
const chartSeries = computed(() => [{ name: t(trendBy.value), data: trendValues.value }]);
const chartOptions = computed(() => ({
  chart: {
    foreColor: token.value.colorTextSecondary,
    fontFamily: 'inherit',
    toolbar: { show: false },
    zoom: { enabled: false },
    animations: { enabled: false },
  },
  colors: [trendBy.value === 'sales' ? token.value.colorPrimary : '#23866d'],
  dataLabels: { enabled: false },
  stroke: { curve: 'straight', width: 2 },
  fill: { opacity: 0.15, type: 'solid' },
  legend: { show: false },
  grid: { borderColor: token.value.colorBorderSecondary },
  xaxis: { categories: snapshot.value.trend.map(day => date(day.date, 'DD/MM')), tickAmount: 6 },
  yaxis: { labels: { formatter: number } },
  tooltip: { y: { formatter: money } },
}));

async function load() {
  if (loading.value) return;
  loading.value = true;
  loadFailed.value = false;
  try {
    snapshot.value = await http.get('dashboard/overview', {
      period: period.value,
      warehouse_id: warehouseId.value || undefined,
    });
    warehouses.value = snapshot.value.warehouses;
  } catch (error) {
    console.error('Could not load the dashboard overview', error);
    snapshot.value = null;
    loadFailed.value = true;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  load();
  refreshTimer = window.setInterval(() => {
    if (!document.hidden && !loadFailed.value) load();
  }, 60000);
});
onUnmounted(() => window.clearInterval(refreshTimer));
</script>

<style scoped>
.overview {
  max-width: 1440px;
  margin-inline: auto;
}
.overview-header, .section-heading {
  display: flex;
  justify-content: space-between;
  align-items: end;
  gap: 12px;
}
.overview-header { margin-bottom: 22px; flex-wrap: wrap; }
.quick-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
.quick-action { display: flex; align-items: center; gap: 9px; padding: 11px 14px; border: 1px solid var(--overview-line); border-radius: 10px; background: var(--overview-control-bg); color: var(--overview-text); transition: border-color 150ms, color 150ms; }
.quick-action:hover { border-color: var(--overview-accent); color: var(--overview-accent); }
.quick-action > .anticon:first-child { color: var(--overview-accent); }
.action-arrow { font-size: 11px; opacity: .5; margin-inline-start: 10px; }
.action-arrow-rtl { transform: rotate(180deg); }
.overview-header h1 { font-size: clamp(21px, 2.4vw, 27px); margin: 0 0 3px; }
.overview-header p, .section-heading span, .section-date, .card-description {
  color: var(--overview-muted);
}
.overview-header p { margin: 0; }
.overview-filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: end;
}
.overview-filters label { display: grid; gap: 4px; color: var(--overview-muted); font-size: 12px; }
.overview-filters select {
  min-height: 32px;
  padding-inline: 9px;
  border: 1px solid var(--overview-control-border);
  border-radius: 6px;
  background: var(--overview-control-bg);
  color: var(--overview-text);
  font: inherit;
  font-size: 14px;
}
.overview-filters select:focus-visible { outline: 2px solid var(--overview-accent); outline-offset: 2px; }
.overview-sections { display: grid; gap: 16px; }
.initial-loading { min-height: 140px; }
.section-heading { margin-bottom: 8px; }
.section-heading h2 { font-size: 17px; margin: 0; }
.section-heading span, .section-date { font-size: 12px; white-space: nowrap; }
.summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.summary-card { min-width: 0; }
.summary-card :deep(.ant-card-body) { display: grid; gap: 5px; }
.summary-card strong { font-size: clamp(16px, 1.9vw, 22px); overflow-wrap: anywhere; }
.period-card { border-top: 3px solid var(--overview-accent); }
.period-card small { color: var(--overview-muted); }
.summary-icon { color: var(--overview-accent); font-size: 17px; }
.overview-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.trend-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
.overview-list { list-style: none; margin: 0; padding: 0; }
.branch-row {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) repeat(3, minmax(0, 1fr));
  align-items: center;
  gap: 10px;
  padding: 11px 0;
}
.branch-header { padding-top: 0; color: var(--overview-muted); }
.branch-row + .branch-row, .branch-header + .overview-list, .recent-list li + li, .ranking-list li + li {
  border-top: 1px solid var(--overview-line);
}
.branch-row span { overflow-wrap: anywhere; }
.compact-label {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip-path: inset(50%);
}
.card-description { margin: 0 0 12px; }
.payment-list { display: grid; gap: 16px; margin: 0; }
.payment-list dt { color: var(--overview-muted); }
.payment-list dd { font-size: 20px; font-weight: 650; margin: 3px 0 0; }
.view-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.ranking-list li, .recent-list li { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 9px 0; }
.recent-list small { display: block; margin-top: 3px; color: var(--overview-muted); }
.recent-amount { white-space: nowrap; }
.stock-list li { display: flex; align-items: center; gap: 12px; padding: 12px 0; }
.stock-list li + li { border-top: 1px solid var(--overview-line); }
.stock-warning { color: #b77616; font-size: 21px; }
.stock-description { flex: 1; min-width: 0; }
.stock-list small { display: block; color: var(--overview-muted); margin-top: 3px; }
.stock-quantity { text-align: end; }
@media (max-width: 950px) {
  .overview-grid, .trend-grid { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 580px) {
  .quick-action { flex: 1 1 42%; min-width: 0; padding: 10px; font-size: 12px; }
  .action-arrow { margin-inline-start: auto; }
  .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .branch-row { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; font-size: 13px; }
  .branch-header { display: none; }
  .branch-header + .overview-list { border-top: 0; }
  .branch-name { grid-column: 1 / -1; }
  .compact-label { position: static; width: auto; height: auto; clip-path: none; display: block; margin-bottom: 3px; color: var(--overview-muted); }
  .overview :deep(.ant-card-body) { padding: 12px; }
  .overview :deep(.ant-card-head) { padding-inline: 12px; }
  .overview :deep(.ant-card-head-title) { white-space: normal; }
}
</style>
