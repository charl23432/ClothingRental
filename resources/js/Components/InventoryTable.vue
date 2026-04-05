<template>
  <section class="inventory-table">
    <div class="table-header">
      <span>Item</span>
      <span>Item Name</span>
      <span>Available</span>
      <span>Rented</span>
      <span>Rental Fee</span>
      <span>Action</span>
    </div>

    <div v-if="items.length">
      <div
        v-for="item in items"
        :key="item.id"
        class="table-row"
      >
        <span>
          <img
            v-if="item.image"
            :src="'/storage/' + item.image"
          />
          <span v-else>No Image</span>
        </span>

        <span class="item-name">
          {{ item.item_name }}
        </span>

        <span>
          {{ item.quantity }}
          <small v-if="item.quantity == 0" style="color:red;">
            Out of stock
          </small>
        </span>

        <span>
          {{ item.rented ?? 0 }}
        </span>

        <span>
          ₱{{ item.rental_fee }}
        </span>

        <span class="action-btns">
          <router-link
            :to="{ name: 'EditItemPage', params: { id: item.id } }"
            class="edit-btn"
          >
            Edit
          </router-link>

          <button
            class="delete-btn"
            @click="$emit('delete-item', item.id)"
          >
            Delete
          </button>
        </span>
      </div>
    </div>

    <div v-else style="padding:20px;text-align:center;">
      No items found
    </div>
  </section>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    default: () => []
  }
})

defineEmits(['delete-item'])
</script>