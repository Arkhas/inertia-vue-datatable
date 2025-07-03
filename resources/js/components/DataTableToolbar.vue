<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'
import { computed, reactive, watch, onMounted, inject, provide } from 'vue'
import { useTranslation } from '../i18n/useTranslation'

import { X, ChevronDown } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';
import { Button } from './ui/button'
import { Input } from './ui/input'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from './ui/dropdown-menu'

import { priorities, statuses } from '../data/data'
import DataTableFacetedFilter from './DataTableFacetedFilter.vue'
import DataTableViewOptions from './DataTableViewOptions.vue'
import {PageProps} from "@/components/type";
import {router} from "@inertiajs/vue3";

const form = reactive({
  search: null,
  filters: {} as Record<string, string[]>
})

const props = defineProps<{
  table: PageProps,
  configName: string,
  selectedRows?: string[] | number[]
}>()

const emit = defineEmits(['action'])

// Check if any rows are selected
const hasSelectedRows = computed(() => {
  return props.selectedRows && props.selectedRows.length > 0
})

// Watch for changes in the search input and reload the table
watch(() => form.search, (newValue) => {
  // Debounce the search to avoid too many requests
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleSearch(newValue);
  }, 300); // Wait 300ms after typing stops
});

// Watch for changes in filters
watch(() => form.filters, (newValue) => {
  handleFilters(newValue);
}, { deep: true });

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// Handle search
const handleSearch = (searchValue: string | null) => {
  // Create a params object with the search parameter
  const params: Record<string, any> = {};

  // Add the search parameter to the specific datatable config
  params[props.configName] = {
    search: searchValue
  };

  // Send the request to the server
  router.post(window.location.pathname, params, {
    preserveState: true,
    preserveScroll: true,
    only: [props.configName]
  });
}

// Handle filters
const handleFilters = (filters: Record<string, string[]>) => {
  // Create a params object with the filters parameter
  const params: Record<string, any> = {};

  // Add the filters parameter to the specific datatable config
  params[props.configName] = {
    filters: filters
  };

  // Send the request to the server
  router.post(window.location.pathname, params, {
    preserveState: true,
    preserveScroll: true,
    only: [props.configName]
  });
}

// Initialize filters from currentFilters
const initializeFilters = () => {
  if (props.table && props.table.currentFilters) {
    for (const [key, value] of Object.entries(props.table.currentFilters)) {
      if (Array.isArray(value)) {
        form.filters[key] = value;
      } else if (typeof value === 'string') {
        form.filters[key] = [value];
      }
    }
  }
}

// Call initializeFilters when component is mounted
onMounted(() => {
  initializeFilters();
});

const isFiltered = computed(() => {
  return (form.search !== null && form.search !== '') || 
         Object.values(form.filters).some(filter => filter && filter.length > 0)
})

// Function to get the icon component by name
const getIconComponent = (iconName) => {
  if (!iconName) return null;
  return LucideIcons[iconName] || null;
};

// Get the translation function from the useTranslation hook
const { t } = useTranslation();

// Also provide it to child components in case they don't have access to the injected value
provide('t', t);
</script>

<template>
  <div class="flex items-center justify-between">
    <div class="flex flex-1 items-center space-x-2">
      <!-- Always show filters -->
      <Input
        :placeholder="t('filter_placeholder')"
        v-model="form.search"
        class="h-8 w-[150px] lg:w-[250px]"
      />
      <template v-for="filter in table.filters" :key="filter.name">
        <DataTableFacetedFilter
          :title="filter.label"
          :options="filter.filterOptions || Object.entries(filter.options).map(([value, label]) => ({ value, label }))"
          @update:selected="(selected) => {
            form.filters[filter.name] = selected;
          }"
          :selected="form.filters[filter.name] || []"
        />
      </template>

      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="() => {
          form.search = null;
          form.filters = {};
          handleSearch(null);
          handleFilters({});
        }"
      >
        {{ t('reset') }}
        <X class="ml-2 h-4 w-4" />
      </Button>
    </div>
    <div class="flex items-center space-x-2">
      <!-- Actions on the right side -->
      <div class="flex items-center space-x-2">
        <span v-if="hasSelectedRows" class="text-sm font-medium">{{ selectedRows.length }} {{ t('selected') }}</span>
        <template v-for="action in table.actions" :key="action.name">
          <!-- Handle action groups -->
          <template v-if="action.type === 'group'">
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm" class="h-8" :disabled="!hasSelectedRows" v-bind="action.props || {}">
                  <component 
                    :is="getIconComponent(action.icon)" 
                    class="mr-2 h-4 w-4" 
                    v-if="action.icon && action.iconPosition !== 'right'"
                  />
                  {{ action.label }}
                  <component 
                    :is="getIconComponent(action.icon)" 
                    class="ml-2 h-4 w-4" 
                    v-if="action.icon && action.iconPosition === 'right'"
                  />
                  <ChevronDown class="ml-2 h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <DropdownMenuItem 
                  v-for="groupAction in action.actions" 
                  :key="groupAction.name"
                  @click="emit('action', groupAction.hasConfirmCallback ? groupAction.name + '_confirm' : groupAction.name)"
                  :disabled="!hasSelectedRows"
                  v-bind="groupAction.props || {}"
                >
                  <component 
                    :is="getIconComponent(groupAction.icon)" 
                    class="mr-2 h-4 w-4" 
                    v-if="groupAction.icon && groupAction.iconPosition !== 'right'"
                  />
                  {{ groupAction.label }}
                  <component 
                    :is="getIconComponent(groupAction.icon)" 
                    class="ml-2 h-4 w-4" 
                    v-if="groupAction.icon && groupAction.iconPosition === 'right'"
                  />
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </template>
          <!-- Handle single actions -->
          <template v-else>
            <Button 
              variant="outline" 
              size="sm" 
              class="h-8"
              @click="emit('action', action.hasConfirmCallback ? action.name + '_confirm' : action.name)"
              :disabled="!hasSelectedRows"
              v-bind="action.props || {}"
            >
              <component 
                :is="getIconComponent(action.icon)" 
                class="mr-2 h-4 w-4" 
                v-if="action.icon && action.iconPosition !== 'right'"
              />
              {{ action.label }}
              <component 
                :is="getIconComponent(action.icon)" 
                class="ml-2 h-4 w-4" 
                v-if="action.icon && action.iconPosition === 'right'"
              />
            </Button>
          </template>
        </template>
      </div>
      <DataTableViewOptions :table="table" :config-name="configName" />
    </div>
  </div>
</template>
