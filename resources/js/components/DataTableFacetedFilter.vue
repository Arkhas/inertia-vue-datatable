<script setup lang="ts">
import type { Component } from 'vue'
import { computed, ref, watch } from 'vue'
import { Check, PlusCircle } from 'lucide-vue-next'

import { cn } from '../lib/utils'
import { Button } from './ui/button'
import Badge from './ui/badge/Badge.vue'

import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator } from './ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from './ui/popover'
import { Separator } from './ui/separator'

interface DataTableFacetedFilter {
  title?: string
  options: {
    label: string
    value: string
    icon?: Component
    count?: number
  }[]
  selected?: string[]
}

const props = defineProps<DataTableFacetedFilter>()
const emit = defineEmits<{
  'update:selected': [selected: string[]]
}>()

const selectedValuesSet = ref(new Set<string>())

// Initialize selectedValuesSet from props.selected
watch(() => props.selected, (newSelected) => {
  if (newSelected) {
    selectedValuesSet.value = new Set(newSelected)
  } else {
    selectedValuesSet.value = new Set()
  }
}, { immediate: true })

// Computed property to get the selected values as an array
const selectedValues = computed(() => selectedValuesSet.value)

// Update the selected values and emit the change
const updateSelected = (value: string, isSelected: boolean) => {
  const newSet = new Set(selectedValuesSet.value)

  if (isSelected) {
    newSet.add(value)
  } else {
    newSet.delete(value)
  }

  selectedValuesSet.value = newSet
  emit('update:selected', Array.from(newSet))
}

// Clear all selected values
const clearSelected = () => {
  selectedValuesSet.value = new Set()
  emit('update:selected', [])
}
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" size="sm" class="h-8 border-dashed">
        <PlusCircle class="mr-2 h-4 w-4" />
        {{ title }}
        <template v-if="selectedValues.size > 0">
          <Separator orientation="vertical" class="mx-2 h-4" />
          <Badge
            variant="secondary"
            class="rounded-sm px-1 font-normal lg:hidden"
          >
            {{ selectedValues.size }}
          </Badge>
          <div class="hidden space-x-1 lg:flex">
            <Badge
              v-if="selectedValues.size > 2"
              variant="secondary"
              class="rounded-sm px-1 font-normal"
            >
              {{ selectedValues.size }} selected
            </Badge>

            <template v-else>
              <Badge
                v-for="option in options
                  .filter((option) => selectedValues.has(option.value))"
                :key="option.value"
                variant="secondary"
                class="rounded-sm px-1 font-normal"
              >
                {{ option.label }}
              </Badge>
            </template>
          </div>
        </template>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[200px] p-0" align="start">
      <Command>
        <CommandInput :placeholder="title" />
        <CommandList>
          <CommandEmpty>No results found.</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="option in options"
              :key="option.value"
              :value="option"
              @select="() => {
                const isSelected = selectedValues.has(option.value)
                updateSelected(option.value, !isSelected)
              }"
            >
              <div
                :class="cn(
                  'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                  selectedValues.has(option.value)
                    ? 'bg-primary text-primary-foreground'
                    : 'opacity-50 [&_svg]:invisible',
                )"
              >
                <Check :class="cn('h-4 w-4')" />
              </div>
              <component :is="option.icon" v-if="option.icon" class="mr-2 h-4 w-4 text-muted-foreground" />
              <span>{{ option.label }}</span>
              <span v-if="option.count" class="ml-auto flex h-4 w-4 items-center justify-center font-mono text-xs">
                {{ option.count }}
              </span>
            </CommandItem>
          </CommandGroup>

          <template v-if="selectedValues.size > 0">
            <CommandSeparator />
            <CommandGroup>
              <CommandItem
                :value="{ label: 'Clear filters' }"
                class="justify-center text-center"
                @select="clearSelected"
              >
                Clear filters
              </CommandItem>
            </CommandGroup>
          </template>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
