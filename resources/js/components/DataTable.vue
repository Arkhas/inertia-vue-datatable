<script setup lang="ts">
import {ref, watch} from 'vue'
import {valueUpdater} from '../lib/utils'
import {computed} from 'vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from './ui/table'
import DataTablePagination from './DataTablePagination.vue'
import DataTableToolbar from './DataTableToolbar.vue'
import {usePage, router} from "@inertiajs/vue3";
import {h} from 'vue'
import {Checkbox} from './ui/checkbox'
import DataTableColumnHeader from './DataTableColumnHeader.vue'
import DataTableRowActions from './DataTableRowActions.vue'
import {FlexRender} from "@tanstack/vue-table";
import {
  Column,
  TableAction,
  TableActionGroup,
  FilterOption,
  FilterDefinition,
  ConfirmDialogContent,
  PageProps
} from './type'

// Define props for this component
const config = defineProps({
  name: {
    type: String,
    required: true
  },
})

// Make datatable reactive using computed to ensure it updates when props change
const datatable = computed(() => {
  // Ensure props[config.name] exists, otherwise return a default PageProps object
  return usePage().props[config.name] ? usePage().props[config.name] as PageProps : {
    columns: [],
    filters: [],
    actions: [],
    currentFilters: {},
    data: {
      data: [],
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 10
    },
    pageSize: 10,
    availablePageSizes: [10, 25, 50, 100]
  } as PageProps;
});

// Handle sort event from DataTableColumnHeader
const handleSort = ({column, direction}: { column: Column, direction: 'asc' | 'desc' | null }) => {
  // Create a params object with the sort parameters
  const params: Record<string, any> = {};

  // Add the sort parameters to the specific datatable config
  params[config.name] = {
    sort: column.key,
    column: column.name,
    direction: direction
  };

  router.get(window.location.pathname, params, {
    preserveState: true,
    preserveScroll: true,
    only: [config.name]
  });
}

</script>

<template>
  <div class="space-y-4">
    <DataTableToolbar v-if="datatable" :table="datatable" :config-name="config.name"/>
    <div v-if="datatable" class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead v-for="column in datatable.columns" :key="column.name">
              <DataTableColumnHeader
                  :column="column"
                  :currentSort="datatable.sort"
                  :currentDirection="datatable.direction"
                  @sort="handleSort"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="datatable.data.total">
            <TableRow
                v-for="row in datatable.data.data"
            >
              <TableCell v-html="row[column.name]" v-for="column in datatable.columns">
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else>
            <TableCell
                :colspan="datatable.columns.length"
                class="h-24 text-center"
            >
              No results.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <DataTablePagination :table="datatable" :config-name="config.name"/>
  </div>
</template>
