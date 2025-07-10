<script setup lang="ts">
import DataTable from './components/DataTable.vue'
import { usePage } from '@inertiajs/vue3'
import { h, provide } from 'vue'
import { Checkbox } from './components/ui/checkbox'
import DataTableColumnHeader from './components/DataTableColumnHeader.vue'
import DataTableRowActions from './components/DataTableRowActions.vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { useTranslation } from './i18n/useTranslation'

// Define props for this component
const config = defineProps({
  name: {
    type: String,
    required: true
  }
})

// Get the translation function and provide it to child components
const { t } = useTranslation();
provide('t', t);

</script>
<template>
  <DataTable :name="config.name"/>
</template>
