<script setup lang="ts">
import {computed} from 'vue'
import {SlidersHorizontal} from 'lucide-vue-next'

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

const props = defineProps<{
  table: PageProps,
  configName: string
}>()

const columns = computed(() => props.table.columns
    .filter(
        column => column.toggable && column.label,
    ))

const toggleVisibility = (column: Column, value: string | null) => {
  column.hidden = value;
  console.log(column)
  console.log(value)
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
          variant="outline"
          size="sm"
          class="ml-auto hidden h-8 lg:flex"
      >
        <SlidersHorizontal class="mr-2 h-4 w-4"/>
        View
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent @select.prevent align="end" class="w-[150px]">
      <DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
      <DropdownMenuSeparator/>

      <DropdownMenuCheckboxItem
          v-for="column in columns"
          :key="column.name"
          class="capitalize"
          :model-value="!column.hidden"
          @update:model-value="(value) => toggleVisibility(column, !!value)"
      >
        {{ column.label }}
      </DropdownMenuCheckboxItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
