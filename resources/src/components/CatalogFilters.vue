<template>
  <a-space wrap>
    <a-select
      v-for="field in visibleFields" :key="field"
      :value="modelValue[field]" :options="options[field].map(value => ({ value, label: value }))"
      :placeholder="catalogLabel(field, locale)" :aria-label="catalogLabel(field, locale)"
      style="width: 150px" allow-clear show-search option-filter-prop="label"
      @update:value="$emit('update:modelValue', { ...modelValue, [field]: $event })"
    />
  </a-space>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { catalogFields, catalogLabel } from '../lib/catalogLabels';

const props = defineProps({
  modelValue: { type: Object, required: true },
  options: { type: Object, default: () => ({}) },
});
defineEmits(['update:modelValue']);
const { locale } = useI18n();
const visibleFields = computed(() => catalogFields.filter(field => props.options[field]?.length));
</script>
