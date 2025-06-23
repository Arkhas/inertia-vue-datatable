<script setup lang="ts">
import DataTable from './components/DataTable.vue'
import { usePage } from '@inertiajs/vue3'
import { h } from 'vue'
import { Checkbox } from './components/ui/checkbox'
import DataTableColumnHeader from './components/DataTableColumnHeader.vue'
import DataTableRowActions from './components/DataTableRowActions.vue'
import type { ColumnDef } from '@tanstack/vue-table'

// Define props for this component
const config = defineProps({
  name: {
    type: String,
    required: true
  }
})

</script>
<template>
  <DataTable :name="config.name"/>
</template>
