<template>
  <main>
    <section class="banner">
      <img src="/public/images/banner-women.png" alt="Women Banner" class="banner-img" />
    </section>

    <section class="collection">
      <div class="collection-header">
        <h2>WEDDING GOWNS</h2>
        <router-link to="/women/wedding" class="view-more">view more</router-link>
      </div>

      <div class="products">
        <router-link
          v-for="product in wedding"
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
        <router-link to="/women/prom" class="view-more">view more</router-link>
      </div>

      <div class="products">
        <router-link
          v-for="product in prom"
          :key="product.id"
          :to="`/products/${product.id}`"
          class="product"
        >
          <img :src="productImage(product.image)" :alt="product.item_name" />
          <div class="price">₱{{ formatPrice(product.rental_fee) }}</div>
        </router-link>
      </div>
    </section>

    <button class="logout-btn" @click="logout">Log Out</button>
  </main>
</template>

<script>
export default {
  name: 'WomenDashboard',
  data() {
    return {
      wedding: [],
      prom: []
    }
  },
  async mounted() {
    try {
      const res = await fetch('/api/women')
      const data = await res.json()

      this.wedding = data.wedding || []
      this.prom = data.prom || []
    } catch (error) {
      console.error('Failed to load women dashboard:', error)
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
    },
    async logout() {
      try {
        await fetch('/logout', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute('content') || '',
            'Accept': 'application/json'
          }
        })

        window.location.href = '/login'
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }
  }
}
</script>