/**
 * Single source of truth for SaaS plan access in the Next SPA.
 * Plan features are owned by the central SaaS database and delivered by
 * /api/get_user_auth. They are deliberately not stored in localStorage.
 */

export const FEATURE_DEPENDENCIES = {
    store: ['store_settings', 'store_payment_gateway', 'store_orders', 'store_collections', 'store_banners', 'store_subscribers', 'store_messages', 'store_invite_codes', 'store_pending_customers', 'store_shipping_methods', 'store_tax_rates', 'store_coupons', 'store_flash_sales', 'store_reviews', 'store_popups', 'store_quote_requests', 'store_returns', 'store_pages', 'store_menus', 'store_realestate', 'store_realestate_properties', 'store_realestate_categories', 'store_realestate_inquiries', 'products', 'sales'],
    people: ['people_customers', 'people_customer_add', 'people_customer_import', 'people_client_portal', 'people_suppliers', 'people_supplier_add', 'people_supplier_import'],
    user_management: ['user_management_users', 'user_management_permissions'],
    products: ['products_add', 'products_list', 'products_import', 'products_import_update', 'products_opening_stock', 'products_barcode', 'products_count_stock', 'products_categories', 'products_subcategories', 'products_brands', 'products_size_guides', 'products_units', 'products_vehicle_fitment', 'products_batches', 'products_serial_numbers'],
    sales: ['sales_list', 'sales_add', 'sales_import', 'sales_pos', 'sales_customer_screen', 'sales_shipments', 'sales_realtime_counter', 'sales_kitchen', 'sales_return'],
    purchases: ['purchases_add', 'purchases_list', 'purchases_import', 'purchases_return'],
    quotations: ['quotations_add', 'quotations_list'],
    adjustment: ['stock_adjustment_add', 'stock_adjustment_list'],
    transfer: ['stock_transfers_add', 'stock_transfers_list'],
    damages: ['damages_create', 'damages_list'],
    hrm: ['hrm_company', 'hrm_departments', 'hrm_designations', 'hrm_office_shift', 'hrm_employees', 'hrm_attendance', 'hrm_leave_request', 'hrm_leave_type', 'hrm_holidays', 'hrm_payroll', 'hrm_contracts', 'hrm_knowledge_base', 'hrm_article_groups'],
    recruit: ['recruit_dashboard', 'recruit_jobs', 'recruit_candidates', 'recruit_applications', 'recruit_interviews', 'recruit_job_categories', 'recruit_reports'],
    meetings: ['meeting_dashboard', 'meeting_meetings', 'meeting_calendar', 'meeting_reports'],
    marketing: ['marketing_dashboard', 'marketing_campaigns', 'marketing_customer_segments', 'marketing_sms_templates', 'marketing_whatsapp_templates', 'marketing_email_templates', 'marketing_reports', 'marketing_settings'],
    accounting: ['accounting_dashboard', 'accounting_chart_of_accounts', 'accounting_journal_entries', 'accounting_trial_balance', 'accounting_profit_loss', 'accounting_balance_sheet', 'accounting_tax_summary', 'accounting_accounts', 'accounting_transfers', 'accounting_expenses_create', 'accounting_expenses_list', 'accounting_deposits_create', 'accounting_deposits_list', 'accounting_expense_category', 'accounting_deposit_category'],
    e_wallet: ['e_wallet_dashboard', 'e_wallet_items', 'e_wallet_settings'],
    commissions: ['commissions_programs', 'commissions_agents', 'commissions_rules', 'commissions_receipts', 'commissions_reports'],
    promotions: ['promotions'],
    shopify: ['shopify_dashboard', 'shopify_stores', 'shopify_sync', 'shopify_mappings', 'shopify_logs'],
    manufacturing: ['manufacturing_dashboard', 'manufacturing_production', 'manufacturing_shop_floor', 'manufacturing_boms', 'manufacturing_work_centres', 'manufacturing_quality', 'manufacturing_planning', 'manufacturing_reports'],
    assets: ['assets_dashboard', 'assets_add', 'assets_list', 'assets_assignments', 'assets_maintenance', 'assets_transfers', 'assets_depreciation', 'assets_due', 'assets_category', 'assets_reports'],
    projects: ['projects_dashboard', 'projects_list', 'projects_tasks', 'projects_task_board', 'projects_milestones', 'projects_timesheets', 'projects_reports'],
    bookings: ['bookings_list', 'bookings_calendar', 'bookings_trays'],
    service_maintenance: ['service_jobs', 'service_awaiting_approval', 'service_ready_for_pickup', 'service_awaiting_payment', 'service_technicians', 'service_checklist_categories', 'service_checklist_items', 'service_maintenance_history'],
    fleet: ['fleet_dashboard', 'fleet_vehicles', 'fleet_maintenance', 'fleet_fuel_logs', 'fleet_assignments', 'fleet_reports'],
    hospital: ['hospital_dashboard', 'hospital_patients', 'hospital_appointments', 'hospital_consultations', 'hospital_admissions', 'hospital_wards', 'hospital_lab_orders', 'hospital_lab_tests', 'hospital_billing', 'hospital_doctors', 'hospital_departments', 'hospital_reports'],
    school: ['school_dashboard', 'school_students', 'school_enrollment', 'school_attendance', 'school_timetable', 'school_exams', 'school_fees', 'school_teachers', 'school_academics', 'school_reports'],
    reports: ['reports_ai', 'reports_sales_3d', 'reports_payments', 'reports_profit', 'reports_profit_loss', 'reports_transactions', 'reports_cash_flow', 'reports_seller', 'reports_attendance_summary', 'reports_analytics', 'reports_stock_valuation', 'reports_cash_register', 'reports_warranty', 'reports_inventory_valuation', 'reports_expenses', 'reports_deposits', 'reports_quantity_alerts', 'reports_warehouse', 'reports_internal_location', 'reports_stock', 'reports_serial_available', 'reports_serial_sold', 'reports_serial_movement', 'reports_serial_inventory', 'reports_negative_stock', 'reports_product', 'reports_zero_sales', 'reports_dead_stock', 'reports_expiry', 'reports_batch_register', 'reports_stock_aging', 'reports_stock_transfer', 'reports_stock_adjustment', 'reports_discount_summary', 'reports_loyalty_points', 'reports_tax_summary', 'reports_draft_invoices', 'reports_return_ratio', 'reports_sales', 'reports_product_sales', 'reports_sales_by_category', 'reports_sales_by_brand', 'reports_purchases', 'reports_product_purchases', 'reports_customers', 'reports_inactive_customers', 'reports_top_suppliers', 'reports_suppliers', 'reports_top_selling', 'reports_top_customers', 'reports_users', 'reports_login_activity', 'reports_error_logs', 'reports_service_jobs', 'reports_checklist_completion', 'reports_maintenance_history'],
    settings: ['settings_system', 'settings_warehouses', 'settings_modules', 'settings_quickbooks', 'settings_appearance', 'settings_languages', 'settings_payment_methods', 'settings_sms', 'settings_sms_templates', 'settings_mail', 'settings_email_templates', 'settings_pos', 'settings_pos_receipt', 'settings_update', 'settings_payment_gateway', 'settings_currencies', 'settings_backup', 'settings_system_health', 'settings_webhooks', 'settings_login_devices', 'settings_loyalty'],
};

const ALIASES = {
    Store: 'store', People: 'people', User_Management: 'user_management',
    sale_return: 'sales_return', purchase_return: 'purchases_return',
    adjustments: 'adjustment', transfers: 'transfer',
    stock_adjustment: 'adjustment', stock_transfers: 'transfer',
    meeting: 'meetings', service: 'service_maintenance',
    EWallet: 'e_wallet', woocommerce_settings: 'woocommerce',
    store_ecommerce: 'store', recruits_jobs: 'recruit',
    manufacturing_mrp: 'manufacturing', asset_management: 'assets',
    projects_tasks: 'projects', booking_management: 'bookings',
    fleet_management: 'fleet', hospital_management: 'hospital', school_management: 'school',
    all_reports: 'reports', document_archive: 'documents',
    subscription_products: 'subscription_product',
};

export function canonicalFeatureKey(key) {
    if (!key) return null;
    return ALIASES[key] || String(key).toLowerCase();
}

export function expandPlanFeatures(features) {
    const raw = Array.isArray(features) ? features : [];
    if (raw.includes('*')) return new Set(['*']);
    const out = new Set(raw.map(canonicalFeatureKey));
    // Dashboard is the core landing page and is always available to a tenant.
    out.add('dashboard');
    // Accept legacy names used by older plans.
    if (out.has('meeting')) out.add('meetings');
    if (out.has('service')) out.add('service_maintenance');
    if (out.has('woocommerce_settings')) out.add('woocommerce');
    if (out.has('e_wallet')) out.add('e_wallet');
    for (const feature of [...out]) {
        const children = FEATURE_DEPENDENCIES[feature];
        if (children) children.forEach(child => out.add(child));
    }
    // Legacy plans may contain a granular page without its parent module.
    for (const [parent, children] of Object.entries(FEATURE_DEPENDENCIES)) {
        if (children.some(child => out.has(child))) out.add(parent);
    }
    return out;
}

export function hasPlanFeature(features, feature) {
    const key = canonicalFeatureKey(feature);
    if (!key) return false;
    const unlocked = expandPlanFeatures(features);
    if (unlocked.has('*') || unlocked.has(key)) return true;
    // Legacy plans sometimes stored a child feature directly. In that case the
    // owning module must still be visible so the purchased page is reachable.
    return Object.entries(FEATURE_DEPENDENCIES).some(([parent, children]) =>
        children.includes(key) && unlocked.has(parent)
    );
}

export function canonicalModuleFeature(key) {
    return canonicalFeatureKey(key);
}

function routeLikeForEntry(entry) {
    if (!entry) return '';
    if (entry.to) return entry.to;
    if (entry.href) return entry.href;
    return '';
}

export function featureKeyForPath(path) {
    const p = String(path || '').split('?')[0].replace(/^\/+/, '').toLowerCase();
    if (!p) return 'dashboard';
    const exact = {
        dashboard: 'dashboard', pos: 'sales_pos', 'cashier-treasury': 'sales_pos',
        products: 'products', sales: 'sales', purchases: 'purchases', quotations: 'quotations',
        'sale-returns': 'sales_return', 'purchase-returns': 'purchases_return',
        adjustments: 'adjustment', transfers: 'transfer', damages: 'damages',
        customers: 'people', suppliers: 'people', users: 'user_management', permissions: 'user_management',
        reports: 'reports', settings: 'settings', store: 'store', realestate: 'store',
        mrp: 'manufacturing', assets: 'assets', projects: 'projects', tasks: 'projects',
        bookings: 'bookings', service: 'service_maintenance', fleet: 'fleet',
        hospital: 'hospital', school: 'school', marketing: 'marketing',
        recruit: 'recruit', meetings: 'meetings', meeting: 'meetings',
        commissions: 'commissions', ewallet: 'e_wallet', promotions: 'promotions',
        shopify: 'shopify', woocommerce: 'woocommerce', documents: 'documents',
        subscriptions: 'subscription_product', 'accounting-v2': 'accounting',
    };
    return exact[p.split('/')[0]] || null;
}

/** Feature for a MENU node. Path wins over label, fixing duplicate labels such as "Dashboard". */
export function featureKeyForEntry(entry, groupKey = null) {
    if (!entry) return null;
    if (entry.labelKeys?.includes('Customer')) return 'people_customer_add';
    if (entry.labelKeys?.includes('Supplier')) return 'people_supplier_add';

    const raw = routeLikeForEntry(entry);
    if (raw) {
        // Legacy menu paths are still useful for feature ownership.
        const nextPath = raw.startsWith('/app/') ? raw.slice(5) : raw;
        const key = featureKeyForPath(nextPath);
        if (key) {
            // Child pages that need a dedicated plan key are identified here.
            const p = nextPath.split('?')[0];
            const specific = {
                '/accounting-v2/dashboard': 'accounting_dashboard',
                '/accounting-v2/chart-of-accounts': 'accounting_chart_of_accounts',
                '/accounting-v2/journal-entries': 'accounting_journal_entries',
            };
            if (specific[p]) return specific[p];
            if (p.includes('/reports/')) return key === 'reports' ? 'reports' : key;
            return key;
        }
    }

    if (groupKey) return canonicalFeatureKey(groupKey);
    return canonicalFeatureKey(entry.label);
}

/** Shared sidebar gate: plan first, then the user's normal permission. */
export function isEntryAllowed(entry, auth, groupKey = null) {
    if (!auth?.user) return false;
    if (!auth.user.is_saas_tenant) {
        return !entry?.permissions?.length || entry.permissions.some(p => auth.can(p));
    }
    const feature = featureKeyForEntry(entry, groupKey);
    if (!hasPlanFeature(auth.user.saas_features, feature)) return false;
    if (!entry?.permissions?.length) return true;
    return entry.permissions.some(p => auth.can(p));
}
