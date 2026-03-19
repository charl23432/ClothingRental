<template>
  <section class="product-details" v-if="product">
    <div class="product-image">
      <img
        id="productImage"
        :src="productImage(product.image)"
        :alt="product.item_name"
      />
    </div>

    <div class="product-info">
      <div
        v-if="Number(product.quantity) <= 0"
        style="background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin-bottom:15px;"
      >
        <strong>Out of Stock</strong><br />
        This item is currently unavailable for reservation.
      </div>

      <h2 id="productName">{{ product.item_name }}</h2>
      <p id="productPrice" class="rent-price">
        Rent for ₱{{ formatPrice(product.rental_fee) }}
      </p>

      <template v-if="sizes.length">
        <h4>AVAILABLE SIZE (inches)</h4>

        <table class="size-table">
          <thead>
            <tr>
              <th>Size</th>
              <th>Chest</th>
              <th>Waist</th>
              <th>Hip</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(size, index) in sizes" :key="index">
              <td>{{ size.size }}</td>
              <td>{{ size.chest }}</td>
              <td>{{ size.waist }}</td>
              <td>{{ size.hip }}</td>
            </tr>
          </tbody>
        </table>
      </template>

      <router-link :to="`/checkout/${product.id}`">
        <button
          id="rentNowBtn"
          class="rent-btn"
          :disabled="Number(product.quantity) <= 0"
        >
          RENT NOW
        </button>
      </router-link>
    </div>
  </section>

  <div v-else style="padding: 20px;">Loading product...</div>
</template>

<script>
export default {
  name: 'ProductDetails',
  data() {
    return {
      product: null
    }
  },
  computed: {
    sizes() {
      if (!this.product?.sizes) return []

      try {
        const parsed = typeof this.product.sizes === 'string'
          ? JSON.parse(this.product.sizes)
          : this.product.sizes

        return Array.isArray(parsed)
          ? parsed.filter(size =>
              size &&
              size.size !== undefined &&
              size.chest !== undefined &&
              size.waist !== undefined &&
              size.hip !== undefined
            )
          : []
      } catch {
        return []
      }
    }
  },
  async mounted() {
    try {
      const res = await fetch(`/api/products/${this.$route.params.id}`)
      this.product = await res.json()
    } catch (error) {
      console.error('Failed to load product details:', error)
    }
  },
  methods: {
    productImage(image) {
      return image ? `/storage/${image}` : '/images/hfhmn.jpg'
    },
    formatPrice(price) {
      return Number(price).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    }
  }
}
</script>