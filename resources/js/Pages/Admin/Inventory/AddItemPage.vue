<template>
  <ItemForm :category="category" />
</template>

<script>
import ItemForm from '../../../Components/ItemForm.vue'

export default {
  components: { ItemForm },
  props: {
    category: {
      type: String,
      default: 'men_tuxedo'
    }
  }
}
</script>