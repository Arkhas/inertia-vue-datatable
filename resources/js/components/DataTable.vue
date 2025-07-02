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

const datatable = computed(() => {
  return usePage().props[config.name] as PageProps;
});

// Watch for changes in datatable.value.visibleColumns to update column hidden properties
watch(() => datatable.value?.visibleColumns, (newVisibleColumns) => {
  if (newVisibleColumns && datatable.value?.columns) {
    // Update column hidden properties based on visibleColumns
    datatable.value.columns.forEach(column => {
      const visibleSetting = newVisibleColumns[column.name];
      if (visibleSetting !== undefined && column.hidden !== !visibleSetting) {
        // Update the column's hidden property to match the visibility setting
        column.hidden = !visibleSetting;
      }
    });
  }
}, { deep: true });

// Handle sort event from DataTableColumnHeader
const handleSort = ({column, direction}: { column: Column, direction: 'asc' | 'desc' | null }) => {
  // Create a params object with the sort parameters
  const params: Record<string, any> = {};

  // Add the sort parameters to the specific datatable config
  params[config.name] = {
    sort: column.name,
    direction: direction
  };

  router.post(window.location.pathname, params, {
    preserveState: true,
    preserveScroll: true,
    only: [config.name]
  });
}

// Handle visibility event from DataTableColumnHeader
const handleVisibility = ({column, visible}: { column: Column, visible: boolean }) => {
  // Create a params object with the visibility parameters
  const params: Record<string, any> = {};

  // Add the visibility parameters to the specific datatable config
  params[config.name] = {
    visibleColumns: {
      [column.name]: visible
    }
  };

  // Update the column properties locally for immediate reactivity
  column.hidden = !visible;

  router.post(window.location.pathname, params, {
    preserveState: true,
    preserveScroll: true,
    only: [config.name]
  });
}

const columns = computed(() => {
  if (!datatable.value || !datatable.value.columns) return [];
  return datatable.value.columns.filter(column => !column.hidden);
});

</script>

<template>
  <div class="space-y-4">
    <DataTableToolbar v-if="datatable" :table="datatable" :config-name="config.name"/>
    <div v-if="datatable" class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead v-for="column in columns" :key="column.name">
              <DataTableColumnHeader
                  :column="column"
                  :currentSort="datatable.sort"
                  :currentDirection="datatable.direction"
                  @sort="handleSort"
                  @visibility="handleVisibility"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="datatable.data.total">
            <TableRow
                v-for="row in datatable.data.data"
            >
              <TableCell v-html="row[column.name]" v-for="column in columns">
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else>
            <TableCell
                :colspan="columns.length"
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
