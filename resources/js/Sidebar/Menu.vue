<template>
  <ul>
    <li v-for="menu in mainMenus" :key="menu.id">
      {{ menu.label }}

      <ul v-if="getChildren(menu.id).length">
        <SubMenu
          v-for="child in getChildren(menu.id)"
          :key="child.id"
          :menu="child"
        />
      </ul>
    </li>
  </ul>
</template>

<script setup>
import { computed } from 'vue'
import SubMenu from './SubMenu.vue'

const props = defineProps({ menus: Array })

const mainMenus = computed(() =>
  props.menus.filter(menu => menu.parent === null)
)

function getChildren(parentId) {
  return props.menus.filter(menu => menu.parent === parentId)
}
</script>