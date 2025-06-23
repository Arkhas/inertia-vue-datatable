<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'
import { computed, reactive, watch } from 'vue'

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

const isFiltered = computed(() => form.search !== null && form.search !== '')
</script>

<template>
  <div class="flex items-center justify-between">
    <div class="flex flex-1 items-center space-x-2">
      <Input
        placeholder="Filter..."
        v-model="form.search"
        class="h-8 w-[150px] lg:w-[250px]"
      />
<!--      <DataTableFacetedFilter-->
<!--        v-if="table.getColumn('status')"-->
<!--        :column="table.getColumn('status')"-->
<!--        title="Status"-->
<!--        :options="statuses"-->
<!--      />-->
<!--      <DataTableFacetedFilter-->
<!--        v-if="table.getColumn('priority')"-->
<!--        :column="table.getColumn('priority')"-->
<!--        title="Priority"-->
<!--        :options="priorities"-->
<!--      />-->

      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="form.search = null; handleSearch(null)"
      >
        Reset
        <X class="ml-2 h-4 w-4" />
      </Button>
    </div>
     <DataTableViewOptions :table="table" :config-name="configName" />
  </div>
</template>
