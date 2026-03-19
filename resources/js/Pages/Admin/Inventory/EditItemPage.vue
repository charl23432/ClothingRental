<template>
  <ItemForm :item-data="item" :redirect-url="'/inventory-men'" />
</template>

<script>
import ItemForm from '../../../Components/ItemForm.vue'

export default {
  components: { ItemForm },
  data() {
    return {
      item: {}
    }
  },
  mounted() {
    fetch(`/api/inventory/${this.$route.params.id}`)
      .then(res => res.json())
      .then(data => this.item = data)
  }
}
</script>