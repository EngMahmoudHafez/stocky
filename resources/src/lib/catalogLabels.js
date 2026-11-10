const attributes = {
  color: ['اللون', 'Color'],
  size: ['المقاس', 'Size'],
  material: ['الخامة', 'Material'],
  season: ['الموسم', 'Season'],
};

export const catalogFields = Object.keys(attributes);

export function catalogLabel(field, locale) {
  return attributes[field][locale === 'ar' ? 0 : 1];
}
