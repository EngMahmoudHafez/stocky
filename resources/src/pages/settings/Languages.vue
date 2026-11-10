<template>
  <div class="page">
    <PageHeader :title="$t('Languages')" :breadcrumb="[$t('Settings'), $t('Languages')]" />
    <a-card size="small" :body-style="{ padding: 0 }">
      <a-table
        :columns="columns" :data-source="languages" :loading="loading"
        :pagination="false" row-key="id" size="middle"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            {{ supportedNames[record.locale] }}
          </template>
          <template v-else-if="column.key === 'is_default'">
            <a-tag v-if="record.is_default" color="success">{{ $t('Default') }}</a-tag>
            <a-button v-else size="small" :disabled="saving" @click="setDefault(record)">
              {{ locale === 'ar' ? 'تعيين كافتراضية' : 'Set as default' }}
            </a-button>
          </template>
          <template v-else-if="column.key === 'actions'">
            <router-link :to="`/settings/translations/${record.locale}`">{{ $t('Translations') }}</router-link>
          </template>
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { message } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import PageHeader from '../../components/PageHeader.vue';
import { SUPPORTED_LOCALES } from '../../i18n';
import http from '../../lib/http';
import { useAuthStore } from '../../stores/auth';

const { t, locale } = useI18n();
const auth = useAuthStore();
const languages = ref([]);
const loading = ref(false);
const saving = ref(false);
const supportedNames = Object.fromEntries(SUPPORTED_LOCALES.map(({ value, label }) => [value, label]));
const columns = computed(() => [
  { title: t('Language'), key: 'name' },
  { title: t('DefaultLanguage'), key: 'is_default' },
  { title: t('Actions'), key: 'actions' },
]);

async function load() {
  loading.value = true;
  try {
    languages.value = await http.get('languages_setting');
  } catch {
    message.error(t('Error'));
  } finally {
    loading.value = false;
  }
}

async function setDefault(language) {
  saving.value = true;
  try {
    await http.post(`languages_setting/${language.id}/set-default`);
    await load();
    await auth.reload();
    message.success(locale.value === 'ar' ? 'تم تغيير اللغة الافتراضية' : 'Default language updated');
  } catch {
    message.error(t('Error'));
  } finally {
    saving.value = false;
  }
}

onMounted(load);
</script>
