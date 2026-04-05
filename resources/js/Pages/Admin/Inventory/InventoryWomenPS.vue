<template>

<div class="inventory-content">

<header class="inventory-header">
<h1>Inventory</h1>
</header>
<div class="inventory-actions">
<input v-model="search" placeholder="Search">

<router-link :to="{ path: '/admin/inventory/add/women_prom' }" class="add-btn">
  Add New Item
</router-link>
</div>



<div class="inventory-tabs">

<div class="tab-group top-tabs">
<router-link to="/admin/inventory-men">Men</router-link>
<router-link to="/admin/inventory-women" class="active">Women</router-link>
</div>

<div class="tab-group sub-tabs">
<router-link to="/admin/inventory-women">
Wedding & Formal
</router-link>

<router-link to="/inventory-women-ps" class="active">
Prom & Styles
</router-link>
</div>

</div>

<InventoryTable :items="filteredItems" @delete-item="deleteItem"/>

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
        const res = await fetch('/api/inventory-women-ps')
        this.items = await res.json()
      } catch (error) {
        console.error('Failed to fetch items:', error)
      }
    },

    async deleteItem(id) {
      const confirmDelete = confirm('Are you sure you want to delete this item?')
      if (!confirmDelete) return

      try {
        const res = await fetch(`/api/inventory/${id}`, {
          method: 'DELETE',
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        if (!res.ok) {
          throw new Error('Failed to delete item')
        }

        this.items = this.items.filter(item => item.id !== id)

        alert('Item deleted successfully.')
      } catch (error) {
        console.error(error)
        alert('Delete failed.')
      }
    }
  }
}
</script>
