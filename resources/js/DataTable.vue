<script setup lang="ts">
import {columns} from './components/columns'
import DataTable from './components/DataTable.vue'
import tasks from './data/tasks.json'
</script>
<template>
  <DataTable :data="tasks" :columns="columns"/>
</template>