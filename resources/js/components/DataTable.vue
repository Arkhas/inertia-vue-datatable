<script setup lang="ts">
import {ref, watch, onMounted} from 'vue'
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
import {Button} from './ui/button'
import DataTableColumnHeader from './DataTableColumnHeader.vue'
import DataTableRowActions from './DataTableRowActions.vue'
import {FlexRender} from "@tanstack/vue-table";
import * as LucideIcons from 'lucide-vue-next';
import { MoreHorizontal } from 'lucide-vue-next';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from './ui/dropdown-menu';
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

// State for selected rows
const selectedRows = ref(new Set())

  // Toggle selection for a single row
const toggleRowSelection = (rowId) => {
  if (!rowId) return;

  if (selectedRows.value.has(rowId)) {
    selectedRows.value.delete(rowId)
  } else {
    selectedRows.value.add(rowId)
  }
}

// Toggle selection for all rows
const toggleAllRows = (rows, selected) => {
  if (!rows) return;

  if (selected) {
    rows.forEach(row => {
      if (row && row._id && !row.checks_disabled) {
        selectedRows.value.add(row._id)
      }
    })
  } else {
    selectedRows.value.clear()
  }
}

// Check if all rows are selected
const areAllRowsSelected = (rows) => {
  if (!rows || rows.length === 0) return false
  return rows.every(row => row && (row.checks_disabled || (row._id && selectedRows.value.has(row._id))))
}

// Check if some rows are selected
const areSomeRowsSelected = (rows) => {
  if (!rows) return false
  return selectedRows.value.size > 0 && !areAllRowsSelected(rows)
}

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

  const currentPath = typeof window !== 'undefined' && window.location ? window.location.pathname : '/';
  router.post(currentPath, params, {
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

  const currentPath = typeof window !== 'undefined' && window.location ? window.location.pathname : '/';
  router.post(currentPath, params, {
    preserveState: true,
    preserveScroll: true,
    only: [config.name]
  });
}

// Initialize column visibility from session data when component is mounted
onMounted(() => {
  if (datatable.value?.visibleColumns && datatable.value?.columns) {
    // Apply visibility settings from session to columns
    datatable.value.columns.forEach(column => {
      const visibleSetting = datatable.value.visibleColumns[column.name];
      if (visibleSetting !== undefined) {
        // Update the column's hidden property to match the visibility setting
        column.hidden = !visibleSetting;
      }
    });
  }
});

const columns = computed(() => {
  if (!datatable.value || !datatable.value.columns) return [];
  return datatable.value.columns.filter(column => !column.hidden);
});

// Function to get the icon component by name
const getIconComponent = (iconName) => {
  if (!iconName) return null;
  return LucideIcons[iconName] || null;
};

</script>

<template>
  <div class="space-y-4">
    <DataTableToolbar 
      v-if="datatable" 
      :table="datatable" 
      :config-name="config.name"
      :selected-rows="Array.from(selectedRows)"
      @action="(actionName) => {
        // Create a params object with the action parameters
        const params = {};
        params[config.name] = {
          action: actionName,
          ids: Array.from(selectedRows)
        };

        // Send the request to the server
        const currentPath = typeof window !== 'undefined' && window.location ? window.location.pathname : '/';
        router.post(currentPath, params, {
          preserveState: true,
          preserveScroll: true,
          only: [config.name]
        });

        // Clear selection after action
        selectedRows.clear();
      }"
    />
    <div v-if="datatable" class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead v-for="column in columns" :key="column.name">
              <template v-if="column.type === 'checkbox'">
                <div class="flex items-center justify-center">
                  <Checkbox
                    :model-value="areAllRowsSelected(datatable.data.data)"
                    :indeterminate="areSomeRowsSelected(datatable.data.data)"
                    @update:model-value="toggleAllRows(datatable.data.data, $event)"
                    :disabled="datatable.data.data.length === 0"
                  />
                </div>
              </template>
              <template v-else>
                <DataTableColumnHeader
                    :column="column"
                    :currentSort="datatable.sort"
                    :currentDirection="datatable.direction"
                    @sort="handleSort"
                    @visibility="handleVisibility"
                />
              </template>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="datatable.data.total">
            <TableRow
                v-for="row in datatable.data.data"
            >
              <TableCell v-for="column in columns" :key="column.name">
                <template v-if="column.type === 'checkbox'">
                  <div class="flex items-center justify-center">
                    <Checkbox
                      :model-value="row && row._id ? selectedRows.has(row._id) : false"
                      @update:model-value="row && row._id ? toggleRowSelection(row._id) : null"
                      :disabled="row && row[column.name + '_disabled']"
                    />
                  </div>
                </template>
                <template v-else-if="column && column.type === 'action' && row && row[column.name + '_action']">
                  <div class="flex items-center justify-center">
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="h-8 w-8 p-0">
                          <span class="sr-only">Open menu</span>
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <template v-if="row[column.name + '_action'] && row[column.name + '_action'].actions">
                          <template v-for="(action, index) in row[column.name + '_action'].actions" :key="action && action.name ? action.name : index">
                            <DropdownMenuItem 
                              @click="() => {
                                try {
                                  const params = {};
                                  params[config.name] = {
                                    action: action && action.hasConfirmCallback ? action.name + '_confirm' : (action ? action.name : ''),
                                    ids: row && row._id ? [row._id] : []
                                  };

                                  const currentPath = typeof window !== 'undefined' && window.location ? window.location.pathname : '/';
                                  router.post(currentPath, params, {
                                    preserveState: true,
                                    preserveScroll: true,
                                    only: [config.name]
                                  });
                                } catch (error) {
                                  console.error('Error in action click handler:', error);
                                }
                              }"
                            >
                              {{ action.label }}
                            </DropdownMenuItem>
                          </template>
                        </template>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                </template>
                <template v-else-if="column && column.hasIcon && row && row[column.name + '_icon']">
                  <div class="flex items-center">
                    <component 
                      :is="getIconComponent(row && column ? row[column.name + '_icon'] : null)" 
                      class="mr-2 h-4 w-4" 
                      v-if="column && column.iconPosition !== 'right'"
                    />
                    <span v-html="row && column ? row[column.name] : ''"></span>
                    <component 
                      :is="getIconComponent(row && column ? row[column.name + '_icon'] : null)" 
                      class="ml-2 h-4 w-4" 
                      v-if="column && column.iconPosition === 'right'"
                    />
                  </div>
                </template>
                <span v-else v-html="row && column ? row[column.name] : ''"></span>
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
