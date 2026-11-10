const statuses = {
  awaiting_dispatch: { ar: 'بانتظار الإرسال', en: 'Awaiting dispatch', color: 'default' },
  in_transit: { ar: 'قيد النقل', en: 'In transit', color: 'processing' },
  partially_received: { ar: 'استلام جزئي', en: 'Partially received', color: 'warning' },
  received: { ar: 'تم الاستلام', en: 'Received', color: 'success' },
  receipt_rejected: { ar: 'رُفض الاستلام', en: 'Receipt rejected', color: 'error' },
};

export function receiptStatusLabel(status, locale) {
  return statuses[status]?.[locale === 'ar' ? 'ar' : 'en'];
}

export function receiptStatusColor(status) {
  return statuses[status]?.color;
}

export function receiptStatusOptions(locale) {
  return Object.keys(statuses).map(value => ({ value, label: receiptStatusLabel(value, locale) }));
}
