<template>
  <a-menu
    v-model:selectedKeys="selectedKeys"
    v-model:openKeys="openKeys"
    class="sidebar-menu"
    mode="inline"
    :inline-indent="16"
    :inline-collapsed="collapsed"
    :theme="theme"
    :items="filteredItems"
    :style="{ '--sb-primary': ui.primaryColor }"
    @click="onClick"
  />
</template>

<script setup>
import { computed, ref, watch, onMounted, h } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { resolveTarget } from '../config/menu';
import { MENU_ICONS } from '../config/menuIcons';
import { MENU_ITEM_ICONS } from '../config/menuItemIcons';
import { useAuthStore } from '../stores/auth';
import { useUiStore } from '../stores/ui';
import { useMenuStore } from '../stores/menu';
import { isEntryAllowed } from '../lib/saasAccess';

const props = defineProps({
  theme: { type: String, default: 'light' },
  collapsed: { type: Boolean, default: false },
});

const { t } = useI18n();
const auth = useAuthStore();
const ui = useUiStore();
const menuStore = useMenuStore();
const route = useRoute();
const router = useRouter();

const selectedKeys = ref([]);
const openKeys = ref([]);

/* ==========================================================
   SAAS PLAN FEATURE CHECK
========================================================== */
const tenantFeatures = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('tenant_features') || '[]');
  } catch (e) {
    return [];
  }
});

const hasAllAccess = computed(() => {
  const feats = tenantFeatures.value;
  return feats.length === 0 || feats.includes('*');
});

/* ==========================================================
   DEPENDENCY MAP (Module -> Children it unlocks)
========================================================== */
const DEPENDENCY_MAP = {
  'store': ['store_settings', 'store_payment_gateway', 'store_orders', 'store_collections', 'store_banners', 'store_subscribers', 'store_messages', 'store_invite_codes', 'store_pending_customers', 'store_shipping_methods', 'store_tax_rates', 'store_coupons', 'store_flash_sales', 'store_reviews', 'store_popups', 'store_quote_requests', 'store_returns', 'store_pages', 'store_menus', 'store_realestate', 'store_realestate_properties', 'store_realestate_categories', 'store_realestate_inquiries', 'products', 'sales'],
  'store_realestate': ['store_realestate_properties', 'store_realestate_categories', 'store_realestate_inquiries'],
  'people': ['people_customers', 'people_customer_add', 'people_customer_import', 'people_client_portal', 'people_suppliers', 'people_supplier_add', 'people_supplier_import'],
  'user_management': ['user_management_users', 'user_management_permissions'],
  'products': ['products_add', 'products_list', 'products_import', 'products_import_update', 'products_opening_stock', 'products_barcode', 'products_count_stock', 'products_categories', 'products_subcategories', 'products_brands', 'products_size_guides', 'products_units', 'products_vehicle_fitment', 'products_batches', 'products_serial_numbers'],
  'sales': ['sales_list', 'sales_add', 'sales_import', 'sales_pos', 'sales_customer_screen', 'sales_shipments', 'sales_realtime_counter', 'sales_kitchen', 'sales_return', 'products', 'people'],
  'sales_pos': ['sales', 'sales_list', 'sales_add', 'sales_import', 'sales_customer_screen', 'sales_shipments', 'sales_realtime_counter', 'sales_kitchen', 'sales_return', 'products', 'people', 'purchases'],
  'purchases': ['purchases_add', 'purchases_list', 'purchases_import', 'purchases_return', 'products', 'people'],
  'quotations': ['quotations_add', 'quotations_list'],
  'stock_adjustment': ['stock_adjustment_add', 'stock_adjustment_list'],
  'stock_transfers': ['stock_transfers_add', 'stock_transfers_list'],
  'damages': ['damages_create', 'damages_list'],
  'hrm': ['hrm_company', 'hrm_departments', 'hrm_designations', 'hrm_office_shift', 'hrm_employees', 'hrm_attendance', 'hrm_leave_request', 'hrm_leave_type', 'hrm_holidays', 'hrm_payroll', 'hrm_contracts', 'hrm_knowledge_base', 'hrm_article_groups', 'people'],
  'recruit': ['recruit_dashboard', 'recruit_jobs', 'recruit_candidates', 'recruit_applications', 'recruit_interviews', 'recruit_job_categories', 'recruit_reports', 'people'],
  'meeting': ['meeting_dashboard', 'meeting_meetings', 'meeting_calendar', 'meeting_reports'],
  'marketing': ['marketing_dashboard', 'marketing_campaigns', 'marketing_customer_segments', 'marketing_sms_templates', 'marketing_whatsapp_templates', 'marketing_email_templates', 'marketing_reports', 'marketing_settings'],
  'accounting': ['accounting_dashboard', 'accounting_chart_of_accounts', 'accounting_journal_entries', 'accounting_trial_balance', 'accounting_profit_loss', 'accounting_balance_sheet', 'accounting_tax_summary', 'accounting_accounts', 'accounting_transfers', 'accounting_expenses_create', 'accounting_expenses_list', 'accounting_deposits_create', 'accounting_deposits_list', 'accounting_expense_category', 'accounting_deposit_category', 'sales', 'purchases'],
  'e_wallet': ['e_wallet_dashboard', 'e_wallet_items', 'e_wallet_settings', 'people'],
  'commissions': ['commissions_programs', 'commissions_agents', 'commissions_rules', 'commissions_receipts', 'commissions_reports', 'people'],
  'promotions': ['products'],
  'shopify': ['shopify_dashboard', 'shopify_stores', 'shopify_sync', 'shopify_mappings', 'shopify_logs', 'products', 'sales'],
  'manufacturing': ['manufacturing_dashboard', 'manufacturing_production', 'manufacturing_shop_floor', 'manufacturing_boms', 'manufacturing_work_centres', 'manufacturing_quality', 'manufacturing_planning', 'manufacturing_reports', 'products'],
  'assets': ['assets_dashboard', 'assets_add', 'assets_list', 'assets_assignments', 'assets_maintenance', 'assets_transfers', 'assets_depreciation', 'assets_due', 'assets_category', 'assets_reports', 'people'],
  'projects': ['projects_dashboard', 'projects_list', 'projects_tasks', 'projects_task_board', 'projects_milestones', 'projects_timesheets', 'projects_reports', 'people'],
  'bookings': ['bookings_list', 'bookings_calendar', 'bookings_trays', 'people'],
  'service': ['service_jobs', 'service_awaiting_approval', 'service_ready_for_pickup', 'service_awaiting_payment', 'service_technicians', 'service_checklist_categories', 'service_checklist_items', 'service_maintenance_history', 'people'],
  'fleet': ['fleet_dashboard', 'fleet_vehicles', 'fleet_maintenance', 'fleet_fuel_logs', 'fleet_assignments', 'fleet_reports', 'people'],
  'hospital': ['hospital_dashboard', 'hospital_patients', 'hospital_appointments', 'hospital_consultations', 'hospital_admissions', 'hospital_wards', 'hospital_lab_orders', 'hospital_lab_tests', 'hospital_billing', 'hospital_doctors', 'hospital_departments', 'hospital_reports', 'people'],
  'school': ['school_dashboard', 'school_students', 'school_enrollment', 'school_attendance', 'school_timetable', 'school_exams', 'school_fees', 'school_teachers', 'school_academics', 'school_reports', 'people'],
  'reports': ['reports_ai', 'reports_sales_3d', 'reports_payments', 'reports_profit', 'reports_profit_loss', 'reports_transactions', 'reports_cash_flow', 'reports_seller', 'reports_attendance_summary', 'reports_analytics', 'reports_stock_valuation', 'reports_cash_register', 'reports_warranty', 'reports_inventory_valuation', 'reports_expenses', 'reports_deposits', 'reports_quantity_alerts', 'reports_warehouse', 'reports_internal_location', 'reports_stock', 'reports_serial_available', 'reports_serial_sold', 'reports_serial_movement', 'reports_serial_inventory', 'reports_negative_stock', 'reports_product', 'reports_zero_sales', 'reports_dead_stock', 'reports_expiry', 'reports_batch_register', 'reports_stock_aging', 'reports_stock_transfer', 'reports_stock_adjustment', 'reports_discount_summary', 'reports_loyalty_points', 'reports_tax_summary', 'reports_draft_invoices', 'reports_return_ratio', 'reports_sales', 'reports_product_sales', 'reports_sales_by_category', 'reports_sales_by_brand', 'reports_purchases', 'reports_product_purchases', 'reports_customers', 'reports_inactive_customers', 'reports_top_suppliers', 'reports_suppliers', 'reports_top_selling', 'reports_top_customers', 'reports_users', 'reports_login_activity', 'reports_error_logs', 'reports_service_jobs', 'reports_checklist_completion', 'reports_maintenance_history', 'sales', 'purchases', 'accounting'],
  'settings': ['settings_system', 'settings_warehouses', 'settings_modules', 'settings_quickbooks', 'settings_appearance', 'settings_languages', 'settings_payment_methods', 'settings_sms', 'settings_sms_templates', 'settings_mail', 'settings_email_templates', 'settings_pos', 'settings_pos_receipt', 'settings_update', 'settings_payment_gateway', 'settings_currencies', 'settings_backup', 'settings_system_health', 'settings_webhooks', 'settings_login_devices', 'settings_loyalty'],
};

/* ==========================================================
   LABEL TO KEY MAP (Every label from menu.js)
========================================================== */
const LABEL_TO_KEY_MAP = {
  'dashboard': 'dashboard',

  // STORE
  'Store': 'store',
  'Visit_Online_Store': 'store',
  'Settings': 'store_settings',
  'Payment_Gateway': 'store_payment_gateway',
  'Online_Orders': 'store_orders',
  'Collections': 'store_collections',
  'Banners': 'store_banners',
  'Subscribers': 'store_subscribers',
  'Messages': 'store_messages',
  'Invite_Codes': 'store_invite_codes',
  'Pending_Customers': 'store_pending_customers',
  'Customers_without_Login': 'people_customers',
  'Customers_with_Login': 'people_customers',
  'Shipping_Methods': 'store_shipping_methods',
  'Tax_Rates': 'store_tax_rates',
  'Coupons': 'store_coupons',
  'Flash_Sales': 'store_flash_sales',
  'Product_Reviews': 'store_reviews',
  'Popup_Messages': 'store_popups',
  'Quote_Requests': 'store_quote_requests',
  'Returns_Requests': 'store_returns',
  'Pages': 'store_pages',
  'Menus': 'store_menus',
  'Real_Estate': 'store_realestate',
  'Properties': 'store_realestate_properties',
  'Property_Categories': 'store_realestate_categories',
  'Property_Inquiries': 'store_realestate_inquiries',

  // PEOPLE
  'People': 'people',
  'Customers': 'people_customers',
  'Add': 'people_customer_add',
  'Import_Customers': 'people_customer_import',
  'Client_Portal': 'people_client_portal',
  'Suppliers': 'people_suppliers',
  'Import_Suppliers': 'people_supplier_import',

  // USER MANAGEMENT
  'User_Management': 'user_management',
  'Users': 'user_management_users',
  'GroupPermissions': 'user_management_permissions',

  // PRODUCTS
  'Products': 'products',
  'AddProduct': 'products_add',
  'productsList': 'products_list',
  'import_products': 'products_import',
  'Import (Update Only)': 'products_import_update',
  'Opening_Stock': 'products_opening_stock',
  'Printbarcode': 'products_barcode',
  'CountStock': 'products_count_stock',
  'Categories': 'products_categories',
  'SubCategory': 'products_subcategories',
  'Brand': 'products_brands',
  'Size_Guides': 'products_size_guides',
  'Units': 'products_units',
  'Vehicle Fitment': 'products_vehicle_fitment',
  'Batches': 'products_batches',
  'Serial_Numbers': 'products_serial_numbers',

  // SALES
  'Sales': 'sales',
  'ListSales': 'sales_list',
  'AddSale': 'sales_add',
  'Import_Sales': 'sales_import',
  'POS': 'sales_pos',
  'Customer_Screen': 'sales_customer_screen',
  'Shipments': 'sales_shipments',
  'Real_time_Sales_Counter': 'sales_realtime_counter',
  'KitchenDisplay': 'sales_kitchen',
  'SalesReturn': 'sales_return',

  // PURCHASES
  'Purchases': 'purchases',
  'AddPurchase': 'purchases_add',
  'ListPurchases': 'purchases_list',
  'Import_Purchases': 'purchases_import',
  'PurchasesReturn': 'purchases_return',

  // QUOTATIONS
  'Quotations': 'quotations',
  'AddQuote': 'quotations_add',
  'ListQuotations': 'quotations_list',

  // STOCK
  'Stock Adjustment': 'stock_adjustment',
  'CreateAdjustment': 'stock_adjustment_add',
  'ListAdjustments': 'stock_adjustment_list',
  'Stock Transfers': 'stock_transfers',
  'CreateTransfer': 'stock_transfers_add',
  'ListTransfers': 'stock_transfers_list',

  // DAMAGES
  'Damages': 'damages',
  'Create_Damage': 'damages_create',

  // HRM
  'hrm': 'hrm',
  'Company': 'hrm_company',
  'Departments': 'hrm_departments',
  'Designations': 'hrm_designations',
  'Office_Shift': 'hrm_office_shift',
  'Employees': 'hrm_employees',
  'Attendance': 'hrm_attendance',
  'Leave_request': 'hrm_leave_request',
  'Leave_type': 'hrm_leave_type',
  'Holidays': 'hrm_holidays',
  'Payroll': 'hrm_payroll',
  'Contracts': 'hrm_contracts',
  'Knowledge_Base': 'hrm_knowledge_base',
  'Article Groups': 'hrm_article_groups',

  // RECRUITMENT
  'Recruits and Jobs': 'recruit',
  'Dashboard': 'recruit_dashboard',
  'Jobs': 'recruit_jobs',
  'Candidates': 'recruit_candidates',
  'Applications': 'recruit_applications',
  'Interviews': 'recruit_interviews',
  'Job_Categories': 'recruit_job_categories',

  // MEETINGS
  'Meetings': 'meeting',
  'Calendar': 'meeting_calendar',

  // MARKETING
  'Marketing': 'marketing',
  'Campaigns': 'marketing_campaigns',
  'Customer_Segments': 'marketing_customer_segments',
  'SMS_Templates': 'marketing_sms_templates',
  'WhatsApp_Templates': 'marketing_whatsapp_templates',
  'Email_Templates': 'marketing_email_templates',
  'Marketing_Settings': 'marketing_settings',

  // ACCOUNTING
  'Accounting': 'accounting',
  'Chart_of_Accounts_Title': 'accounting_chart_of_accounts',
  'Journal_Entries_Title': 'accounting_journal_entries',
  'Trial_Balance_Title': 'accounting_trial_balance',
  'Profit_Loss_Title': 'accounting_profit_loss',
  'Balance_Sheet_Title': 'accounting_balance_sheet',
  'Tax_Summary_Report': 'accounting_tax_summary',
  'List_accounts': 'accounting_accounts',
  'Transfers_Money': 'accounting_transfers',
  'Create_Expense': 'accounting_expenses_create',
  'ListExpenses': 'accounting_expenses_list',
  'Create_deposit': 'accounting_deposits_create',
  'List_Deposit': 'accounting_deposits_list',
  'Expense_Category': 'accounting_expense_category',
  'Deposit_Category': 'accounting_deposit_category',

  // E-WALLET
  'E_Wallet': 'e_wallet',
  'E_Wallet_Dashboard': 'e_wallet_dashboard',
  'Wallet_Items': 'e_wallet_items',
  'E_Wallet_Settings': 'e_wallet_settings',

  // COMMISSIONS
  'Commissions': 'commissions',
  'Commission_Programs': 'commissions_programs',
  'Sales_Agents': 'commissions_agents',
  'Commission_Rules': 'commissions_rules',
  'Commission_Receipts': 'commissions_receipts',
  'Commission_Report': 'commissions_reports',

  // PROMOTIONS
  'Promotions': 'promotions',

  // INTEGRATIONS
  'WooCommerce': 'woocommerce',
  'Shopify': 'shopify',
  'Stores': 'shopify_stores',
  'Sync Centre': 'shopify_sync',
  'Mappings': 'shopify_mappings',
  'Logs': 'shopify_logs',

  // DOCUMENTS
  'Document Archive': 'documents',

  // SUBSCRIPTION
  'Subscription_Product': 'subscription_product',

  // MANUFACTURING
  'Manufacturing (MRP)': 'manufacturing',
  'Production Orders': 'manufacturing_production',
  'Shop Floor': 'manufacturing_shop_floor',
  'Bills of Materials': 'manufacturing_boms',
  'Work Centres': 'manufacturing_work_centres',
  'Quality Control': 'manufacturing_quality',
  'Planning': 'manufacturing_planning',

  // ASSETS
  'Asset Management': 'assets',
  'Add_Asset': 'assets_add',
  'Assets_List': 'assets_list',
  'Assignments': 'assets_assignments',
  'Maintenance': 'assets_maintenance',
  'Transfers': 'assets_transfers',
  'Depreciation': 'assets_depreciation',
  'Due_Assets': 'assets_due',
  'Asset_Category': 'assets_category',
  'Asset Reports': 'assets_reports',

  // PROJECTS
  'Projects Management': 'projects',
  'Projects': 'projects_list',
  'Tasks': 'projects_tasks',
  'Task Board': 'projects_task_board',
  'Milestones': 'projects_milestones',
  'Timesheets': 'projects_timesheets',
  'Project Reports': 'projects_reports',

  // BOOKINGS
  'Booking Management': 'bookings',
  'Booking_List': 'bookings_list',
  'Calendar_View': 'bookings_calendar',
  'Trays': 'bookings_trays',

  // SERVICE
  'Service_Maintenance': 'service',
  'Service_Jobs': 'service_jobs',
  'Awaiting_Approval': 'service_awaiting_approval',
  'Ready_For_Pickup': 'service_ready_for_pickup',
  'Awaiting_Payment': 'service_awaiting_payment',
  'Service_Technicians': 'service_technicians',
  'Checklist_Categories': 'service_checklist_categories',
  'Checklist_Items': 'service_checklist_items',
  'Maintenance_History': 'service_maintenance_history',

  // FLEET
  'Fleet Management': 'fleet',
  'Vehicles': 'fleet_vehicles',
  'Fuel Logs': 'fleet_fuel_logs',
  'Fleet Reports': 'fleet_reports',

  // HOSPITAL
  'Hospital Management': 'hospital',
  'Patients': 'hospital_patients',
  'Appointments': 'hospital_appointments',
  'Consultations': 'hospital_consultations',
  'Admissions': 'hospital_admissions',
  'Wards & Beds': 'hospital_wards',
  'Laboratory': 'hospital_lab_orders',
  'Lab Orders': 'hospital_lab_orders',
  'Test Catalogue': 'hospital_lab_tests',
  'Billing': 'hospital_billing',
  'Doctors': 'hospital_doctors',
  'Hospital Reports': 'hospital_reports',

  // SCHOOL
  'School Management': 'school',
  'Students': 'school_students',
  'Enrolment': 'school_enrollment',
  'Timetable': 'school_timetable',
  'Exams & Results': 'school_exams',
  'Fees': 'school_fees',
  'Teachers': 'school_teachers',
  'Academic Setup': 'school_academics',
  'School Reports': 'school_reports',

  // SETTINGS
  'SystemSettings': 'settings_system',
  'Warehouses': 'settings_warehouses',
  'Modules': 'settings_modules',
  'Quickbooks_Sync': 'settings_quickbooks',
  'Dynamic_Appearance': 'settings_appearance',
  'Languages': 'settings_languages',
  'Payment_Methods': 'settings_payment_methods',
  'sms_settings': 'settings_sms',
  'sms_templates': 'settings_sms_templates',
  'mail_settings': 'settings_mail',
  'email_templates': 'settings_email_templates',
  'Pos_Settings': 'settings_pos',
  'POS_Receipt': 'settings_pos_receipt',
  'update_settings': 'settings_update',
  'Currencies': 'settings_currencies',
  'Backup': 'settings_backup',
  'System_Health': 'settings_system_health',
  'Webhooks': 'settings_webhooks',
  'Login_Device_Management': 'settings_login_devices',
  'Loyalty_Rewards': 'settings_loyalty',

  // REPORTS
  'Reports': 'reports',
  'AI_Reports': 'reports_ai',
  'sales_3d_dashboard': 'reports_sales_3d',
  'Payments': 'reports_payments',
  'Profit Reports': 'reports_profit',
  'ProfitandLoss': 'reports_profit_loss',
  'Report_Transactions': 'reports_transactions',
  'Cash_Flow_Report': 'reports_cash_flow',
  'Seller_report': 'reports_seller',
  'attendance_summary': 'reports_attendance_summary',
  'Analytics_Report': 'reports_analytics',
  'Stock_Inventory_Valuation': 'reports_stock_valuation',
  'Cash_Register_Report': 'reports_cash_register',
  'Warranty_Guarantee_Report': 'reports_warranty',
  'Inventory_Valuation': 'reports_inventory_valuation',
  'Expense_Report': 'reports_expenses',
  'Deposits_Report': 'reports_deposits',
  'ProductQuantityAlerts': 'reports_quantity_alerts',
  'Warehouse_report': 'reports_warehouse',
  'Internal_Location_Report': 'reports_internal_location',
  'stock_report': 'reports_stock',
  'Available_Serial_Numbers': 'reports_serial_available',
  'Sold_Serial_Numbers': 'reports_serial_sold',
  'Serial_Movement_Log': 'reports_serial_movement',
  'Product_Serial_Inventory': 'reports_serial_inventory',
  'Negative_Stock_Report': 'reports_negative_stock',
  'product_report': 'reports_product',
  'Zero_Sales_Products_Report': 'reports_zero_sales',
  'Dead_Stock_Report': 'reports_dead_stock',
  'Expiry_Report': 'reports_expiry',
  'Batch_Register_Report': 'reports_batch_register',
  'Stock_Aging_Report': 'reports_stock_aging',
  'Stock_Transfer_Report': 'reports_stock_transfer',
  'Stock_Adjustment_Report': 'reports_stock_adjustment',
  'Discount_Summary_Report': 'reports_discount_summary',
  'Customer_Loyalty_Points_Report': 'reports_loyalty_points',
  'Draft_Invoices_Report': 'reports_draft_invoices',
  'Return_Ratio_Report': 'reports_return_ratio',
  'SalesReport': 'reports_sales',
  'product_sales_report': 'reports_product_sales',
  'Sales_by_Category': 'reports_sales_by_category',
  'Sales_by_Brand': 'reports_sales_by_brand',
  'PurchasesReport': 'reports_purchases',
  'Product_purchases_report': 'reports_product_purchases',
  'CustomersReport': 'reports_customers',
  'Inactive_Customers_Report': 'reports_inactive_customers',
  'Top_Suppliers_Report': 'reports_top_suppliers',
  'SuppliersReport': 'reports_suppliers',
  'Top_Selling_Products': 'reports_top_selling',
  'Top_customers': 'reports_top_customers',
  'Users_Report': 'reports_users',
  'Login_Activity_Report': 'reports_login_activity',
  'Error_Logs': 'reports_error_logs',
  'Service_Jobs_Report': 'reports_service_jobs',
  'Checklist_Completion_Report': 'reports_checklist_completion',
  'Customer_Maintenance_History_Report': 'reports_maintenance_history',
};

/* ==========================================================
   GET UNLOCKED FEATURES
========================================================== */
function getUnlockedFeatures() {
  if (hasAllAccess.value) {
    return Object.keys(LABEL_TO_KEY_MAP).map(k => LABEL_TO_KEY_MAP[k]);
  }

  const unlocked = new Set(tenantFeatures.value);

  tenantFeatures.value.forEach(feature => {
    if (DEPENDENCY_MAP[feature]) {
      DEPENDENCY_MAP[feature].forEach(dep => unlocked.add(dep));
    }
  });

  unlocked.add('dashboard');
  return Array.from(unlocked);
}

/* ==========================================================
   ALL FEATURES MAP (Route path -> Feature key)
========================================================== */
const ALL_FEATURES_MAP = {
  '/dashboard': 'dashboard',
  '/sales': 'sales',
  '/pos': 'sales_pos',
  '/purchases': 'purchases',
  '/quotations': 'quotations',
  '/customers': 'people_customers',
  '/suppliers': 'people_suppliers',
  '/products': 'products',
  '/users': 'user_management_users',
  '/accounting': 'accounting',
  '/reports': 'reports',
  '/settings': 'settings',
};

/* ==========================================================
   SECURITY: Route Guard
========================================================== */
onMounted(() => {
  checkRouteAccess();
});

watch(() => route.path, () => {
  checkRouteAccess();
});

function checkRouteAccess() {
  // ==========================================================
  // BLOCK SAAS TENANTS FROM /settings/business-modules
  // (Central admin has hasAllAccess=true, so they can still access it)
  // ==========================================================
  if (!hasAllAccess.value && route.path.includes('/settings/business-modules')) {
    router.push('/dashboard');
    return;
  }
  // ==========================================================

  if (hasAllAccess.value) return;

  const currentPath = route.path;
  const unlocked = getUnlockedFeatures();
  const requiredFeature = ALL_FEATURES_MAP[currentPath];

  if (requiredFeature && !unlocked.includes(requiredFeature)) {
    router.push('/dashboard');
  }
}

/* ==========================================================
   MENU FILTERING
========================================================== */
 function allowed(entry) {
  if (!auth.user?.is_saas_tenant) return isEntryAllowed(entry, auth);
  if (hasAllAccess.value) return true;
  if (!entry.label) return true;

  // ==========================================================
  // SETTINGS
  // Keep the Settings section visible for tenants.
  // Only expose Warehouses here for now.
  // Do not change any other tenant menu.
  // ==========================================================
  if (
    entry.key === 'settings' ||
    entry.label === 'Settings'
  ) {
    return true;
  }

  // Show Warehouses under Settings
  if (
    entry.label === 'Warehouses' ||
    entry.label === 'settings_warehouses'
  ) {
    return true;
  }

  // Block Modules from SaaS tenants
  if (
    entry.label === 'Modules' ||
    entry.label === 'settings_modules'
  ) {
    return false;
  }

  let labelKey = entry.label;

  if (entry.labelKeys && entry.labelKeys.length) {
    labelKey = entry.labelKeys.join('_');
  }

  const featureKey =
    LABEL_TO_KEY_MAP[labelKey]
    || LABEL_TO_KEY_MAP[entry.label]
    || entry.label.toLowerCase().replace(/\s+/g, '_');

  return getUnlockedFeatures().includes(featureKey);
}

function label(entry) {
  if (entry.raw) return entry.label;
  if (entry.labelKeys) return entry.labelKeys.map(k => t(k)).join(' ');
  return t(entry.label);
}

function icon(name) {
  const Cmp = MENU_ICONS[name];
  return Cmp ? () => h(Cmp, { size: 16 }) : undefined;
}

function labelNode(entry) {
  return label(entry);
}

function itemIcon(entry, groupIconName) {
  const Cmp = MENU_ITEM_ICONS[entry.to] || MENU_ICONS[groupIconName];
  return Cmp ? () => h(Cmp, { size: 15 }) : undefined;
}

function leafItem(groupKey, entry, groupIconName) {
  const target = entry.href
    ? { type: 'external', path: entry.href }
    : resolveTarget(entry.to);
  return {
    key: `${groupKey}|${target.type}|${target.path}`,
    label: labelNode(entry),
    icon: itemIcon(entry, groupIconName),
  };
}

function hasVisibleChildren(group) {
  return (group.children || []).some(c =>
    c.children ? c.children.some(allowed) : allowed(c)
  );
}

function buildGroupChildren(group) {
  return group.children
    .filter(allowed)
    .map(child => {
      if (child.children) {
        if (!child.children.some(allowed)) return null;
        const subKey = `${group.key}|group|${child.label}`;
        const subItems = child.children.filter(allowed)
          .map(x => ({ ...leafItem(group.key, x, group.icon), icon: undefined }));
        return { key: subKey, icon: icon(child.icon || group.icon), label: label(child), children: subItems };
      }
      return leafItem(group.key, child, group.icon);
    })
    .filter(Boolean);
}

const filteredItems = computed(() => {
  const out = [];
  for (const group of menuStore.menu) {
    if (!allowed(group)) continue;
    if (group.children && hasVisibleChildren(group)) {
      out.push({ key: group.key, icon: icon(group.icon), label: label(group), children: buildGroupChildren(group) });
    } else if (group.to) {
      const tgt = resolveTarget(group.to);
      out.push({ key: `${group.key}|${tgt?.type}|${tgt?.path}`, icon: icon(group.icon), label: labelNode(group) });
    }
  }
  return out;
});

const routeIndex = computed(() => {
  const idx = {};
  const addLeaf = (groupKey, entry, opens) => {
    if (entry.href) return;
    const tgt = resolveTarget(entry.to);
    if (tgt?.type === 'next') idx[tgt.path] = { key: `${groupKey}|next|${tgt.path}`, opens };
  };
  for (const group of menuStore.menu) {
    if (!group.children) { addLeaf(group.key, group, []); continue; }
    for (const child of group.children) {
      if (child.children) {
        const subKey = `${group.key}|group|${child.label}`;
        child.children.forEach(leaf => addLeaf(group.key, leaf, [group.key, subKey]));
      } else {
        addLeaf(group.key, child, [group.key]);
      }
    }
  }
  return idx;
});

function onClick({ key }) {
  const [, type, path] = String(key).split('|');
  if (!path) return;
  if (type === 'next') router.push(path);
  else if (type === 'external') window.open(path, '_blank', 'noopener');
  else window.location.href = path;
}

watch(
  [() => route.path, routeIndex],
  ([path]) => {
    const meta = routeIndex.value[path];
    selectedKeys.value = meta ? [meta.key] : [];
    if (!meta || !meta.opens.length) return;
    const missing = meta.opens.filter(k => !openKeys.value.includes(k));
    if (missing.length) openKeys.value = [...openKeys.value, ...missing];
  },
  { immediate: true }
);

watch(openKeys, (nv, ov) => {
  const added = nv.filter(k => !(ov || []).includes(k));
  if (!added.length) return;
  const key = String(added[added.length - 1]);
  const next = key.includes('|group|') ? [key.split('|')[0], key] : [key];
  if (next.length !== nv.length || next.some((k, i) => k !== nv[i])) {
    openKeys.value = next;
  }
});
</script>

<style scoped>
.sidebar-menu { flex: 1; overflow-y: auto; overflow-x: hidden; border-inline-end: none; }
.sidebar-menu.ant-menu-light { border-right: 1px solid #ececee; }
.sidebar-menu :deep(.ant-menu-submenu-arrow) { inset-inline-end: 8px; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline) { position: relative; background: rgba(0, 0, 0, 0.025); }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline), .sidebar-menu :deep(.ant-motion-collapse), .sidebar-menu :deep(.ant-motion-collapse-legacy), .sidebar-menu :deep(.ant-menu-submenu-arrow) { transition: none !important; animation: none !important; }
.sidebar-menu.ant-menu-dark :deep(.ant-menu-sub.ant-menu-inline) { background: rgba(0, 0, 0, 0.22); }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline)::before { content: ''; position: absolute; top: 2px; bottom: 2px; inset-inline-start: 18px; width: 2px; border-radius: 2px; background: rgba(0, 0, 0, 0.1); pointer-events: none; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-item), .sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-submenu-title) { position: relative; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-item:hover)::after { content: ''; position: absolute; top: 7px; bottom: 7px; inset-inline-start: 18px; width: 2px; border-radius: 2px; background: var(--sb-primary, #6d28d9); opacity: 0.45; pointer-events: none; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-item-selected)::after { content: ''; position: absolute; top: 5px; bottom: 5px; inset-inline-start: 18px; width: 3px; border-radius: 2px; background: var(--sb-primary, #6d28d9); opacity: 1; pointer-events: none; }
.sidebar-menu.ant-menu-dark :deep(.ant-menu-sub.ant-menu-inline)::before { background: rgba(255, 255, 255, 0.14); }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-sub.ant-menu-inline) { margin-inline-start: 14px; border-inline-start: 2px solid rgba(0, 0, 0, 0.12); background: transparent; }
.sidebar-menu.ant-menu-dark :deep(.ant-menu-sub.ant-menu-inline .ant-menu-sub.ant-menu-inline) { border-inline-start-color: rgba(255, 255, 255, 0.16); background: transparent; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-sub.ant-menu-inline)::before { display: none; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-sub.ant-menu-inline .ant-menu-item) { padding-inline-start: 14px !important; height: auto; min-height: 36px; line-height: 1.35; padding-top: 7px; padding-bottom: 7px; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-sub.ant-menu-inline .ant-menu-item .ant-menu-title-content) { white-space: normal; overflow: visible; text-overflow: unset; }
.sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-sub.ant-menu-inline .ant-menu-item:hover)::after, .sidebar-menu :deep(.ant-menu-sub.ant-menu-inline .ant-menu-sub.ant-menu-inline .ant-menu-item-selected)::after { inset-inline-start: -2px; }
</style>
