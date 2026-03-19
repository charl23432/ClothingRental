<template>
  <li class="submenu">
    → {{ menu.label }}
  </li>
</template>

<script setup>
defineProps({ menu: Object })
</script>