<template>
  <section class="about">
    <div class="about-logo">
      <img
        id="storeLogoPreview"
        :src="logoSrc"
        alt="Store Logo"
        class="global-logo"
      />
    </div>

    <div class="about-text">
      <p>
        Welcome to {{ setting.store_name || 'Your Store Name' }}, a premier clothing rental service specializing in tuxedos, suits, wedding gowns, and formal dresses.
        We provide elegant, high-quality attire for weddings, corporate events, and special occasions.
      </p>

      <p>
        Our goal is to offer clients a convenient and affordable way to look their best without compromising on style or sophistication.
        Each garment is carefully maintained and tailored to ensure a perfect fit and a polished appearance.
      </p>

      <p>
        At Soleil Laluna Boutique, we take pride in delivering exceptional service, attention to detail, and timeless fashion for every event.
      </p>
    </div>

    <div class="contact">
      <h2>CONTACT US</h2>
      <ul>
        <li>📞 {{ setting.contact_number || 'N/A' }}</li>
        <li>✉️ {{ setting.email || 'N/A' }}</li>
        <li>📍 {{ setting.address || 'N/A' }}</li>
      </ul>
    </div>
  </section>
</template>

<script>
export default {
  name: 'About',
  data() {
    return {
      setting: {
        store_name: '',
        logo: '',
        contact_number: '',
        email: '',
        address: ''
      },
      defaultLogo: '/images/hfhmn.jpg'
    }
  },
  computed: {
    logoSrc() {
      return this.setting.logo ? `/${this.setting.logo}` : this.defaultLogo
    }
  },
  async mounted() {
    try {
      const res = await fetch('/api/settings')
      const data = await res.json()
      this.setting = data
    } catch (error) {
      console.error('Failed to load settings:', error)
    }
  }
}
</script>