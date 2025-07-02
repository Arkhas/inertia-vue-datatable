<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'
import { computed, reactive, watch, onMounted } from 'vue'

import { X } from 'lucide-vue-next';
import { Button } from './ui/button'
import { Input } from './ui/input'

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
  configName: string
}>()

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
  router.get(window.location.pathname, params, {
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
  router.get(window.location.pathname, params, {
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
</script>

<template>
  <div class="flex items-center justify-between">
    <div class="flex flex-1 items-center space-x-2">
      <Input
        placeholder="Filter..."
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
        Reset
        <X class="ml-2 h-4 w-4" />
      </Button>
    </div>
     <DataTableViewOptions :table="table" :config-name="configName" />
  </div>
</template>
