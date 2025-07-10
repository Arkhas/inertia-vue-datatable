<script setup lang="ts">
import {computed, watch, onMounted, inject, provide, ref, nextTick} from 'vue'
import {useTranslation} from '../i18n/useTranslation'
import {SlidersHorizontal} from 'lucide-vue-next'
import {router} from "@inertiajs/vue3"

import {Button} from './ui/button'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from './ui/dropdown-menu'
import {Column, PageProps} from "@/components/type";
import {Input} from './ui/input'

const props = defineProps<{
  table: PageProps,
  configName: string
}>()

const searchQuery = ref('')
const searchInputRef = ref(null)
const isDropdownOpen = ref(false)

const allToggableColumns = computed(() => props.table.columns
    .filter(
        column => column.toggable && column.label,
    ))

const columns = computed(() => {
  if (!searchQuery.value) {
    return allToggableColumns.value
  }

  const query = searchQuery.value.toLowerCase()
  return allToggableColumns.value.filter(
    column => column.label.toLowerCase().includes(query)
  )
})

// Watch for changes in table.visibleColumns to update column hidden properties
watch(() => props.table.visibleColumns, (newVisibleColumns) => {
  if (newVisibleColumns && props.table.columns) {
    // Update column hidden properties based on visibleColumns
    props.table.columns.forEach(column => {
      const visibleSetting = newVisibleColumns[column.name];
      if (visibleSetting !== undefined && column.hidden !== !visibleSetting) {
        // Update the column's hidden property to match the visibility setting
        column.hidden = !visibleSetting;
      }
    });
  }
}, { deep: true });

// Initialize column visibility from session data when component is mounted
onMounted(() => {
  if (props.table.visibleColumns && props.table.columns) {
    // Apply visibility settings from session to columns
    props.table.columns.forEach(column => {
      const visibleSetting = props.table.visibleColumns[column.name];
      if (visibleSetting !== undefined) {
        // Update the column's hidden property to match the visibility setting
        column.hidden = !visibleSetting;
      }
    });
  }
});

const toggleVisibility = (column: Column, visible: boolean) => {
  // Create a params object with the visibility parameters
  const params: Record<string, any> = {};

  // Add the visibility parameters to the specific datatable config
  params[props.configName] = {
    visibleColumns: {
      [column.name]: visible
    }
  };

  // Update the column properties locally for immediate reactivity
  column.hidden = !visible;

  // Send the request to the server
  router.post(window.location.pathname, params, {
    preserveState: true,
    preserveScroll: true,
    only: [props.configName]
  });
}

// Get the translation function from the useTranslation hook
const { t } = useTranslation();

// Also provide it to child components in case they don't have access to the injected value
provide('t', t);

// Function to focus the search input
const focusSearchInput = () => {
  nextTick(() => {
    if (searchInputRef.value) {
      searchInputRef.value.focus();
    }
  });
};

// Watch for dropdown open state changes
watch(isDropdownOpen, (newValue) => {
  if (newValue) {
    focusSearchInput();
  }
});
</script>

<template>
  <DropdownMenu v-model:open="isDropdownOpen">
    <DropdownMenuTrigger as-child>
      <Button
          variant="outline"
          size="sm"
          class="ml-auto hidden h-8 lg:flex"
      >
        <SlidersHorizontal class="mr-2 h-4 w-4"/>
        {{ t('view') }}
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent 
      align="end" 
      class="w-[250px]"
      @openAutoFocus.prevent="focusSearchInput"
    >
      <div class="px-2 py-2">
        <div class="flex items-center">
          <Input 
            ref="searchInputRef"
            v-model="searchQuery"
            :placeholder="t('search_placeholder')"
            class="h-8 w-full"
            @select.prevent
            @click.stop
            @keydown.stop
            @focus.stop
          />
        </div>
      </div>
      <DropdownMenuSeparator/>

      <div class="max-h-[300px] overflow-auto">
        <DropdownMenuCheckboxItem
            v-for="column in columns"
            :key="column.name"
            class="capitalize"
            :model-value="!column.hidden"
            @update:model-value="(value) => toggleVisibility(column, value)"
            @select="(event) => event.preventDefault()"
        >
          {{ column.label }}
        </DropdownMenuCheckboxItem>
        <div v-if="columns.length === 0" class="px-2 py-2 text-sm text-gray-500 text-center">
          {{ t('no_results') }}
        </div>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
