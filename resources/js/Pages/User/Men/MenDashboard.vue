<template>
  <main>
    <section class="banner">
      <img src="/public/images/banner-men.png" alt="Men Banner" class="banner-img" />
    </section>

    <section class="collection">
      <div class="collection-header">
        <h2>TUXEDO &amp; SUITS</h2>
        <router-link to="/men/tuxedo" class="view-more">view more</router-link>
      </div>

      <div class="products">
        <router-link
          v-for="product in tuxLimited"
          :key="product.id"
          :to="`/products/${product.id}`"
          class="product"
        >
          <img :src="productImage(product.image)" :alt="product.item_name" />
          <div class="price">₱{{ formatPrice(product.rental_fee) }}</div>
        </router-link>
      </div>
    </section>

    <section class="collection">
      <div class="collection-header">
        <h2>PROM &amp; STYLES</h2>
        <router-link to="/men/prom" class="view-more">view more</router-link>
      </div>

      <div class="products">
        <router-link
          v-for="product in promLimited"
          :key="product.id"
          :to="`/products/${product.id}`"
          class="product"
        >
          <img :src="productImage(product.image)" :alt="product.item_name" />
          <div class="price">₱{{ formatPrice(product.rental_fee) }}</div>
        </router-link>
      </div>
    </section>
  </main>
</template>

<script>
export default {
  name: 'MenDashboard',
  data() {
    return {
      tux: [],
      prom: []
    }
  },

  computed: {
    tuxLimited() {
      return this.tux.slice(0, 5)
    },
    promLimited() {
      return this.prom.slice(0, 5)
    }
  },

  async mounted() {
    try {
      const res = await fetch('/api/men', {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      const data = await res.json()
      this.tux = data.tux || []
      this.prom = data.prom || []
    } catch (error) {
      console.error('Failed to load men dashboard:', error)
    }
  },

  methods: {
    productImage(image) {
      return image ? `/storage/${image}` : '/images/hfhmn.jpg'
    },
    formatPrice(price) {
      return Number(price || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    }
  }
}
</script>