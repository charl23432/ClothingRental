<template>
  <div>
    <p v-if="loading">Loading item...</p>
    <p v-else-if="error">{{ error }}</p>
    <ItemForm v-else :item-data="item" />
  </div>
</template>

<script>
import ItemForm from '../../../Components/ItemForm.vue'

export default {
  components: { ItemForm },
  data() {
    return {
      item: null,
      loading: true,
      error: null
    }
  },
  async mounted() {
    try {
      const res = await fetch(`/api/inventory/${this.$route.params.id}`, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      })

      const text = await res.text()
      console.log('EDIT FETCH RESPONSE:', text)

      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      this.item = JSON.parse(text)
    } catch (err) {
      console.error(err)
      this.error = 'Failed to load item.'
    } finally {
      this.loading = false
    }
  }
}
</script>