<script setup>
import { DropdownMenuPortal as RekaDropdownMenuPortal, useForwardPropsEmits } from "reka-ui";
import { reactiveOmit } from "@vueuse/core";

const props = defineProps({
  forceMount: { type: Boolean, required: false },
  container: { type: null, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
});

const emits = defineEmits(["openAutoFocus", "select"]);

const delegatedProps = reactiveOmit(props);
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
  <RekaDropdownMenuPortal v-bind="forwarded">
    <slot />
  </RekaDropdownMenuPortal>
</template>
