<script setup lang="ts">
import {ref, watch, onMounted, inject, provide} from 'vue'
import {valueUpdater} from '../lib/utils'
import {computed} from 'vue'
import {useTranslation} from '../i18n/useTranslation'
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
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from './ui/dialog';
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

// State variables
const selectedRows = ref(new Set())
const showConfirmDialog = ref(false)
const pendingAction = ref(null)
const pendingRowId = ref(null)

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
    rows.forEach((row) => {
      if (row && row._id && !row.checks_disabled) {
        selectedRows.value.add(row._id)
      }
    });
  } else {
    selectedRows.value.clear();
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
      const visibleSetting = newVisibleColumns[column.name]
      if (visibleSetting !== undefined && column.hidden !== !visibleSetting) {
        // Update the column's hidden property to match the visibility setting
        column.hidden = !visibleSetting
      }
    })
  }
}, { deep: true })

// Helper function to send requests to the server
const sendRequest = (data, onlyActionResult = false) => {
  const params = {}
  params[config.name] = data

  const currentPath = typeof window !== 'undefined' ? window.location.pathname : '/'
  router.post(currentPath, params, {
    preserveState: true,
    preserveScroll: true,
    only: [config.name]
  })
}

// Handle sort event from DataTableColumnHeader
const handleSort = ({column, direction}: { column: Column, direction: 'asc' | 'desc' | null }) => {
  sendRequest({
    sort: column.name,
    direction: direction
  })
}

// Handle visibility event from DataTableColumnHeader
const handleVisibility = ({column, visible}: { column: Column, visible: boolean }) => {
  // Update the column properties locally for immediate reactivity
  column.hidden = !visible

  sendRequest({
    visibleColumns: {
      [column.name]: visible
    }
  })
}

// Initialize column visibility from session data when component is mounted
onMounted(() => {
  if (datatable.value?.visibleColumns && datatable.value?.columns) {
    // Apply visibility settings from session to columns
    datatable.value.columns.forEach(column => {
      const visibleSetting = datatable.value.visibleColumns[column.name]
      if (visibleSetting !== undefined) {
        // Update the column's hidden property to match the visibility setting
        column.hidden = !visibleSetting
      }
    })
  }
})

const columns = computed(() => {
  if (!datatable.value || !datatable.value.columns) return []
  return datatable.value.columns.filter(column => !column.hidden)
})

// Function to get the icon component by name
const getIconComponent = (iconName) => {
  if (!iconName) return null
  return LucideIcons[iconName] || null
}

// Extract the base action name from an action with suffix
const getActionName = (actionName) => {
  if (!actionName) return ''

  // Remove '_confirm' suffix if present
  let name = actionName.endsWith('_confirm') 
    ? actionName.substring(0, actionName.length - '_confirm'.length) 
    : actionName

  // Extract base name (e.g., "delete" from "delete_1_task(s)")
  const baseMatch = name.match(/^([^_]+)(?:_\d+.*)?$/)
  return baseMatch && baseMatch[1] ? baseMatch[1] : name
}

// Alias for backward compatibility
const getBaseActionName = getActionName

// Helper function to send action requests
const sendActionRequest = (actionName, ids, clearSelection = false) => {
  sendRequest({
    action: actionName,
    ids: ids
  }, true)

  // Clear selection if needed (for non-confirmation actions)
  if (clearSelection) {
    selectedRows.clear()
  }
}

// Handle toolbar action
const handleToolbarAction = (actionName) => {
  // Reset pendingRowId to ensure row actions don't interfere with toolbar actions
  pendingRowId.value = null

  // Check if this is a confirmation action
  if (actionName.endsWith('_confirm')) {
    pendingAction.value = actionName
    sendActionRequest(actionName, Array.from(selectedRows))
  } else {
    // Regular action without confirmation
    sendActionRequest(actionName, Array.from(selectedRows), true)
  }
}

// Handle row action click
const handleRowAction = (action, row) => {
  if (!action) return

  const actionName = action.hasConfirmCallback ? action.name + '_confirm' : action.name
  const rowId = row && row._id ? row._id : null

  // If confirmation action, store the pending action and row ID
  if (action.hasConfirmCallback) {
    pendingAction.value = actionName
    pendingRowId.value = rowId
  }

  // Send the request
  sendActionRequest(actionName, rowId ? [rowId] : [])
}

// Watch handlers for confirmation dialog and data changes

// Watch for changes in datatable.value.actionResult to show confirmation dialog
watch(() => datatable.value?.actionResult?.confirmData, (newConfirmData) => {
  if (newConfirmData) {
    showConfirmDialog.value = true

    // Store the action name and row ID for redundancy
    if (datatable.value?.actionResult) {
      if (pendingAction.value) {
        datatable.value.actionResult.pendingActionName = pendingAction.value
      }
      if (pendingRowId.value) {
        datatable.value.actionResult.pendingRowId = pendingRowId.value
      }
    }
  } else {
    showConfirmDialog.value = false
  }
})

// Watch for changes in datatable.value.data to update selectedRows
watch(() => datatable.value?.data?.data, (newData, oldData) => {
  if (newData && selectedRows.value.size > 0) {
    // Get the IDs of the new data
    const newIds = new Set(newData.map(row => row._id).filter(Boolean))

    // Remove any selected rows that no longer exist in the data
    for (const id of selectedRows.value) {
      if (!newIds.has(id)) {
        selectedRows.value.delete(id)
      }
    }
  }
}, { deep: true })

// Handle confirm action
const handleConfirm = () => {
  if (!datatable.value?.actionResult?.confirmData) return

  // Get action name with fallbacks
  let actionName = pendingAction.value || 
                  datatable.value?.actionResult?.pendingActionName || ''

  if (!actionName) return

  // Get the base action name
  const baseActionName = getActionName(actionName)

  // Get row ID with fallbacks
  let rowId = pendingRowId.value || 
             datatable.value?.actionResult?.pendingRowId || null

  // Determine which IDs to send
  // If we have a row ID, use that (for row actions)
  // Otherwise, use the selected rows (for toolbar actions)
  const ids = rowId ? [rowId] : Array.from(selectedRows.value)

  // Send request to server
  sendActionRequest(baseActionName, ids)

  // Reset state
  showConfirmDialog.value = false
  pendingAction.value = null
  pendingRowId.value = null
}

// Handle cancel action
const handleCancel = () => {
  showConfirmDialog.value = false
  pendingAction.value = null
  pendingRowId.value = null
}

// Get the translation function from the useTranslation hook
const { t } = useTranslation();

// Also provide it to child components in case they don't have access to the injected value
provide('t', t);
</script>

<template>
  <div class="space-y-4">
    <!-- Confirmation Dialog -->
    <Dialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{{ datatable?.actionResult?.confirmData?.title || t('confirmation') }}</DialogTitle>
          <DialogDescription>
            {{ datatable?.actionResult?.confirmData?.message || t('confirm_action_message') }}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="flex items-center justify-end space-x-2">
          <Button
            variant="outline"
            @click="handleCancel"
          >
            {{ datatable?.actionResult?.confirmData?.cancel || t('cancel') }}
          </Button>
          <Button
            variant="default"
            @click="handleConfirm"
            :disabled="datatable?.actionResult?.confirmData?.disabled"
          >
            {{ datatable?.actionResult?.confirmData?.confirm || t('confirm') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <DataTableToolbar
      v-if="datatable" 
      :table="datatable" 
      :config-name="config.name"
      :selected-rows="Array.from(selectedRows)"
      @action="handleToolbarAction"
    />
    <div v-if="datatable" class="rounded-md border overflow-x-auto w-full">
      <Table class="min-w-max w-full">
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
                              @click="() => handleRowAction(action, row)"
                            >
                              {{ getBaseActionName(action.label) }}
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
              {{ t('no_results', 'No results.') }}
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <DataTablePagination :table="datatable" :config-name="config.name"/>
  </div>
</template>
