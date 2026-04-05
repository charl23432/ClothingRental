<template>
  <section class="checkout-page" v-if="product">
    <div class="checkout-image">
      <img
        id="checkoutProductImage"
        :src="productImage(product.image)"
        :alt="product.item_name"
      />
    </div>

    <div class="checkout-form">
      <form id="checkoutForm" @submit.prevent="submitCheckout">
        <input type="hidden" name="product_id" :value="product.id" />
        <input type="hidden" name="price" :value="product.rental_fee" />
        <input type="hidden" name="size" :value="selectedSize" />

        <label>Available Size</label>

        <div class="size-options">
          <button
            v-for="sizeData in availableSizes"
            :key="sizeData.size"
            type="button"
            class="size-btn"
            :class="{ active: selectedSize === sizeData.size }"
            :disabled="Number(sizeData.available || 0) <= 0"
            @click="selectedSize = sizeData.size"
          >
            {{ capitalize(sizeData.size) }}
            <span v-if="sizeData.available !== undefined">
              ({{ sizeData.available }} left)
            </span>
          </button>
        </div>

        <div v-if="!availableSizes.length" style="color:red; margin-bottom: 15px;">
          No available sizes found.
        </div>

        <div class="delivery-method">
          <label class="delivery-option">
            <input type="radio" v-model="delivery" value="delivery" />
            <span class="delivery-text">For Delivery (fee ₱50)</span>
          </label>

          <label class="delivery-option">
            <input type="radio" v-model="delivery" value="pickup" />
            <span class="delivery-text">For Pick Up</span>
          </label>
        </div>

        <div class="rental-info">
          <label>Rent Time</label>
          <input
            type="datetime-local"
            v-model="rent_time"
            id="rentTime"
            required
          />
        </div>

        <div class="payment-details">
          <h4>Payment Details</h4>
          <table>
            <tr>
              <td>Item</td>
              <td id="itemPrice">₱{{ formatPrice(product.rental_fee) }}</td>
            </tr>
            <tr>
              <td>Delivery Fee</td>
              <td id="deliveryFee">₱{{ formatPrice(deliveryFee) }}</td>
            </tr>
            <tr class="total-row">
              <td><strong>Total</strong></td>
              <td id="totalPrice">
                <strong>₱{{ formatPrice(totalPrice) }}</strong>
              </td>
            </tr>
          </table>
        </div>

        <div class="gcash-info">
          <p>
            Pay via GCash:
            <span class="gcash-number" id="gcashNumber">
              {{ gcashNumber }}
            </span>
            <button
              class="copy-btn"
              type="button"
              id="copyGcash"
              @click="copyGcash"
            >
              Copy
            </button>
          </p>

          <p class="note">
            <strong>Note:</strong>
            Payments are non-refundable but can be rescheduled.
          </p>
        </div>

        <input
          v-model="gcash_reference"
          type="text"
          name="gcash_reference"
          class="reference-input"
          placeholder="Enter GCash Reference Number"
          required
        />

        <button
          type="submit"
          class="confirm-btn"
          id="confirmCheckout"
          :disabled="Number(product.quantity) <= 0 || !selectedSize"
        >
          CONFIRM &amp; CHECKOUT
        </button>
      </form>
    </div>
  </section>

  <div v-else-if="loading" style="padding: 20px;">Loading checkout...</div>

  <div v-else style="padding: 20px; color: red;">
    {{ errorMessage || 'Failed to load checkout.' }}
  </div>
</template>

<script>
export default {
  name: 'Checkout',
  data() {
    return {
      product: null,
      setting: null,
      availableSizes: [],
      selectedSize: '',
      delivery: 'delivery',
      rent_time: '',
      gcash_reference: '',
      loading: true,
      errorMessage: ''
    }
  },
  computed: {
    deliveryFee() {
      return this.delivery === 'delivery' ? 50 : 0
    },
    totalPrice() {
      return Number(this.product?.rental_fee || 0) + this.deliveryFee
    },
    gcashNumber() {
      return this.setting?.gcash_number || '09876543210'
    }
  },
  async mounted() {
    await this.loadCheckout()
  },
  methods: {
    async loadCheckout() {
      this.loading = true
      this.errorMessage = ''

      try {
        const productRes = await fetch(`/api/checkout/${this.$route.params.id}`, {
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        if (!productRes.ok) {
          const text = await productRes.text()
          throw new Error(text || 'Failed to load checkout item')
        }

        const checkoutData = await productRes.json()
        this.product = checkoutData.product
        this.availableSizes = checkoutData.availableSizes || []

        const firstAvailable = this.availableSizes.find(
          size => Number(size.available || 0) > 0
        )

        if (firstAvailable) {
          this.selectedSize = firstAvailable.size
        } else if (this.availableSizes.length > 0) {
          this.selectedSize = this.availableSizes[0].size
        }

        try {
          const settingsRes = await fetch('/api/settings', {
            credentials: 'same-origin',
            headers: {
              Accept: 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          })

          if (settingsRes.ok) {
            this.setting = await settingsRes.json()
          } else {
            this.setting = null
          }
        } catch (settingsError) {
          console.error('Settings load failed:', settingsError)
          this.setting = null
        }
      } catch (error) {
        console.error('Failed to load checkout data:', error)
        this.errorMessage = error.message || 'Failed to load checkout data.'
      } finally {
        this.loading = false
      }
    },

    getCsrfToken() {
      const meta = document.querySelector('meta[name="csrf-token"]')
      return meta ? meta.getAttribute('content') : ''
    },

    async submitCheckout() {
      try {
        if (!this.product) {
          alert('Product not loaded.')
          return
        }

        if (!this.selectedSize) {
          alert('Please select a size.')
          return
        }

        if (!this.rent_time) {
          alert('Please select rent time.')
          return
        }

        if (!this.gcash_reference) {
          alert('Please enter GCash reference.')
          return
        }

        const csrfToken = this.getCsrfToken()

        if (!csrfToken) {
          alert('CSRF token missing. Refresh the page.')
          return
        }

        const payload = {
          product_id: this.product.id,
          price: this.product.rental_fee,
          size: this.selectedSize,
          delivery: this.delivery,
          rent_time: this.rent_time,
          gcash_reference: this.gcash_reference
        }

        const res = await fetch('/checkout/store', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(payload)
        })

        const contentType = res.headers.get('content-type') || ''
        const data = contentType.includes('application/json')
          ? await res.json()
          : { message: await res.text() }

        if (!res.ok) {
          throw new Error(data.message || 'Checkout failed')
        }

        alert(data.message || 'Reservation submitted successfully!')
        this.$router.push('/profile')
      } catch (error) {
        console.error('Checkout error:', error)
        alert(error.message || 'Something went wrong during checkout.')
      }
    },

    async copyGcash() {
      try {
        await navigator.clipboard.writeText(this.gcashNumber)
        alert('GCash number copied.')
      } catch (error) {
        console.error('Failed to copy GCash number:', error)
      }
    },

    productImage(image) {
      return image ? `/storage/${image}` : '/images/hfhmn.jpg'
    },

    formatPrice(price) {
      return Number(price).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    capitalize(value) {
      if (!value) return ''
      return value.charAt(0).toUpperCase() + value.slice(1)
    }
  }
}
</script>