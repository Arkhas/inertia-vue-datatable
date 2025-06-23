<script setup lang="ts">
import {ArrowDown, ArrowUp, ChevronsUpDown, EyeOff} from 'lucide-vue-next'
import {ref, watch, onMounted} from 'vue'

import {cn} from '../lib/utils'
import {Button} from './ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from './ui/dropdown-menu'
import {Column} from "@/components/type";

const props = defineProps<{
  column: Column,
  currentSort?: string,
  currentDirection?: 'asc' | 'desc' | null
}>()

// Define local state for sorting
const sortDirection = ref<'asc' | 'desc' | null>(null)

// Initialize sortDirection based on props
onMounted(() => {
  updateSortDirection()
})

// Update sortDirection when props change
watch([() => props.currentSort, () => props.currentDirection], () => {
  updateSortDirection()
})

// Helper function to update sortDirection based on props
const updateSortDirection = () => {
  if (props.currentSort === props.column.key && props.currentDirection) {
    sortDirection.value = props.currentDirection
  } else {
    sortDirection.value = null
  }
}

// Methods to handle sorting
const toggleSorting = (descending: boolean) => {
  sortDirection.value = descending ? 'desc' : 'asc'
  // Emit an event to notify parent component about sorting change
  emit('sort', {column: props.column, direction: sortDirection.value})
}

// Method to handle visibility
const toggleVisibility = (visible: boolean) => {
  // Emit an event to notify parent component about visibility change
  emit('visibility', {column: props.column, visible})
}

// Define emits
const emit = defineEmits(['sort', 'visibility'])
</script>

<script lang="ts">
export default {
  inheritAttrs: false,
}

</script>

<template>
  <div :class="cn('flex items-center space-x-2', $attrs.class ?? '')">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
            variant="ghost"
            size="sm"
            class="-ml-3 h-8 data-[state=open]:bg-accent"
        >
          <span>{{ column.label }}</span>
          <div v-if="column.sortable">
            <ArrowDown v-if="sortDirection === 'desc'" class="ml-2 h-4 w-4"/>
            <ArrowUp v-else-if="sortDirection === 'asc'" class="ml-2 h-4 w-4"/>
            <ChevronsUpDown v-else class="ml-2 h-4 w-4"/>
          </div>
        </Button>
      </DropdownMenuTrigger>

      <DropdownMenuContent align="start">
        <div v-if="column.sortable">
          <DropdownMenuItem @click="toggleSorting(false)">
            <ArrowUp class="mr-2 h-3.5 w-3.5 text-muted-foreground/70"/>
            Asc
          </DropdownMenuItem>
          <DropdownMenuItem @click="toggleSorting(true)">
            <ArrowDown class="mr-2 h-3.5 w-3.5 text-muted-foreground/70"/>
            Desc
          </DropdownMenuItem>
          <DropdownMenuSeparator/>
        </div>
        <DropdownMenuItem @click="toggleVisibility(false)">
          <EyeOff class="mr-2 h-3.5 w-3.5 text-muted-foreground/70"/>
          Hide
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
