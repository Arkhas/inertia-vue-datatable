<script setup lang="ts">
import {ChevronLeft} from 'lucide-vue-next';
import {ChevronRight} from 'lucide-vue-next';
import {ChevronsLeft} from 'lucide-vue-next';
import {ChevronsRight} from 'lucide-vue-next';
import {usePage, router} from "@inertiajs/vue3";
import {inject, provide} from 'vue';
import {useTranslation} from '../i18n/useTranslation';
import {Button} from './ui/button'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './ui/select'
import {PageProps} from "@/components/type";

const props = defineProps<{
  table: PageProps,
  configName: string
}>()

const goToPage = (page: number) => {
  router.reload({data: {page: page}});
}

const setPageSize = (pageSize: number) => {
  const data: Record<string, any> = {};
  data[props.configName] = {pageSize: pageSize};

  router.reload({data: data});
}

// Get the translation function from the useTranslation hook
const { t } = useTranslation();

// Also provide it to child components in case they don't have access to the injected value
provide('t', t);
</script>

<template>
  <div class="flex items-center justify-between px-2">
    <div class="flex-1 text-sm text-muted-foreground">
      {{ t('showing_results', { from: table.data.from, to: table.data.to, total: table.data.total }) }}
    </div>
    <div class="flex items-center space-x-6 lg:space-x-8">
      <div class="flex items-center space-x-2">
        <p class="text-sm font-medium">
        {{ t('rows_per_page') }}
        </p>
        <Select
            :model-value="`${table.data.per_page}`"
            @update:model-value="setPageSize as any"
        >
          <SelectTrigger class="h-8 w-[70px]">
            <SelectValue :placeholder="`${table.data.per_page}`"/>
          </SelectTrigger>
          <SelectContent side="top">
            <SelectItem v-for="pageSize in table.availablePageSizes" :key="pageSize" :value="`${pageSize}`">
              {{ pageSize }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div class="flex w-[100px] items-center justify-center text-sm font-medium">
        {{ t('page_of', { current: table.data.current_page, last: table.data.last_page }) }}
      </div>
      <div class="flex items-center space-x-2">
        <Button
            variant="outline"
            class="hidden h-8 w-8 p-0 lg:flex"
            :disabled="table.data.current_page  == 1"
            @click="goToPage(1)"
        >
          <span class="sr-only">{{ t('go_to_first_page') }}</span>
          <ChevronsLeft class="h-4 w-4"/>
        </Button>
        <Button
            variant="outline"
            class="h-8 w-8 p-0"
            :disabled="table.data.current_page == 1"
            @click="goToPage(table.data.current_page - 1)"
        >
          <span class="sr-only">{{ t('go_to_previous_page') }}</span>
          <ChevronLeft class="h-4 w-4"/>
        </Button>
        <Button
            variant="outline"
            class="h-8 w-8 p-0"
            :disabled="table.data.current_page  >= table.data.last_page"
            @click="goToPage(table.data.current_page + 1)"
        >
          <span class="sr-only">{{ t('go_to_next_page') }}</span>
          <ChevronRight class="h-4 w-4"/>
        </Button>
        <Button
            variant="outline"
            class="hidden h-8 w-8 p-0 lg:flex"
            :disabled="table.data.current_page  >= table.data.last_page"
            @click="goToPage(table.data.last_page)"
        >
          <span class="sr-only">{{ t('go_to_last_page') }}</span>
          <ChevronsRight class="h-4 w-4"/>
        </Button>
      </div>
    </div>
  </div>
</template>
