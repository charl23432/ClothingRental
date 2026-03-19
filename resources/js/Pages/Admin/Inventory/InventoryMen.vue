<template>

<div class="inventory-content">

<header class="inventory-header">
<h1>Inventory</h1>
</header>
<div class="inventory-wrapper">
<div class="inventory-actions">
<input v-model="search" placeholder="Search">

<router-link :to="{ path: '/admin/inventory/add/men_tuxedo' }" class="add-btn">
  Add New Item
</router-link>
</div>



<div class="inventory-tabs">

<div class="tab-group top-tabs">
<router-link to="/admin/inventory-men" class="active">Men</router-link>
<router-link to="/admin/inventory-women">Women</router-link>
</div>

<div class="tab-group sub-tabs">
<router-link to="/admin/inventory-men" class="active">
Tuxedo & Suits
</router-link>

<router-link to="/admin/inventory-men-ps">
Prom & Styles
</router-link>
</div>

</div>


<InventoryTable :items="filteredItems" />

</div>
</div>
</template>


<script>
import InventoryTable from '../../../Components/InventoryTable.vue'

export default {
  components: {
    InventoryTable
  },

  data() {
    return {
      items: [],
      search: ''
    }
  },

  computed: {
    filteredItems() {
      const keyword = this.search.toLowerCase()

      return this.items.filter(item =>
        item.item_name.toLowerCase().includes(keyword)
      )
    }
  },

  mounted() {
    this.fetchItems()
  },

  methods: {
    async fetchItems() {
      try {
        const res = await fetch('/api/inventory-men')
        this.items = await res.json()
      } catch (error) {
        console.error('Failed to fetch items:', error)
      }
    }
  }
}
</script>