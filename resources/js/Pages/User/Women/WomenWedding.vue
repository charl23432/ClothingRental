<template>
  <section class="collection">
    <div class="collection-header">
      <h2>WEDDING GOWNS</h2>

      <div class="filters">
        <select v-model="priceFilter">
          <option value="">by price</option>
          <option value="low">₱0-₱350</option>
          <option value="mid">₱351-₱450</option>
          <option value="high">₱451+</option>
        </select>

        <div class="search-box">
          <input v-model="search" type="text" placeholder="Search" />
          <button>🔍</button>
        </div>
      </div>
    </div>

    <div class="products" id="productGrid">
      <router-link
        v-for="product in filteredProducts"
        :key="product.id"
        :to="`/products/${product.id}`"
        class="product"
      >
        <img :src="productImage(product.image)" :alt="product.item_name" />
        <div class="price">₱{{ formatPrice(product.rental_fee) }}</div>
      </router-link>
    </div>
  </section>
</template>

<script>
export default {
  name: 'WomenWedding',
  data() {
    return {
      wedding: [],
      search: '',
      priceFilter: ''
    }
  },
  computed: {
    filteredProducts() {
      return this.wedding.filter(product => {
        const matchesSearch = product.item_name
          .toLowerCase()
          .includes(this.search.toLowerCase())

        const price = Number(product.rental_fee)

        let matchesPrice = true
        if (this.priceFilter === 'low') matchesPrice = price >= 0 && price <= 350
        if (this.priceFilter === 'mid') matchesPrice = price >= 351 && price <= 450
        if (this.priceFilter === 'high') matchesPrice = price >= 451

        return matchesSearch && matchesPrice
      })
    }
  },
  async mounted() {
    try {
      const res = await fetch('/api/women/wedding')
      this.wedding = await res.json()
    } catch (error) {
      console.error('Failed to load wedding products:', error)
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