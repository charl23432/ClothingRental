<template>
  <div class="setting-content">
    <header class="setting-h1">
      <h1>Settings</h1>
    </header>

    <div v-if="successMessage" class="alert success">
      {{ successMessage }}
    </div>

    <form @submit.prevent="saveSettings">
      <div class="settings-card">
        <h2>Store Information</h2>

        <div class="store-info">
          <!-- LOGO -->
          <div class="logo-section">
            <img
              :src="logoPreview || defaultLogo"
              alt="Store Logo"
              class="global-logo"
              id="storeLogoPreview"
            />

            <input
              ref="logoInput"
              type="file"
              accept="image/*"
              style="display: none"
              @change="handleLogoChange"
            />

            <button type="button" id="changeLogoBtn" @click="triggerLogoUpload">
              Change Logo
            </button>
          </div>

          <!-- INFO -->
          <div class="info-section">
            <label>Store Name</label>
            <input v-model="form.store_name" type="text" placeholder="Soleil Laluna Boutique" />

            <label>GCash No.</label>
            <input v-model="form.gcash_number" type="text" placeholder="09XXXXXXXXXX" />

            <label>Contact No.</label>
            <input v-model="form.contact_number" type="text" placeholder="09XXXXXXXXXX" />

            <label>Email</label>
            <input v-model="form.email" type="email" placeholder="example@gmail.com" />

            <label>Address</label>
            <div class="address-row" style="display: flex; align-items: center;">
              <input v-model="form.address" type="text" placeholder="Store address here" />
            </div>
          </div>
        </div>

        <button
          type="submit"
          class="save-btn"
          style="background-color: chartreuse; padding: 8px 10px; border-radius: 5px;"
        >
          Save Changes
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: 'Settings',
  data() {
    return {
      successMessage: '',
      defaultLogo: '/images/hfhmn.jpg',
      selectedLogo: null,
      logoPreview: '',
      form: {
        store_name: '',
        gcash_number: '',
        contact_number: '',
        email: '',
        address: '',
        logo: null
      }
    }
  },

  mounted() {
    this.fetchSettings()
  },

  methods: {
    async fetchSettings() {
      try {
        const res = await fetch('/api/settings')

        if (!res.ok) {
          throw new Error('Failed to fetch settings')
        }

        const data = await res.json()

        this.form.store_name = data.store_name || ''
        this.form.gcash_number = data.gcash_number || ''
        this.form.contact_number = data.contact_number || ''
        this.form.email = data.email || ''
        this.form.address = data.address || ''

        this.logoPreview = data.logo ? `/${data.logo}` : this.defaultLogo
      } catch (error) {
        console.error(error)
        this.logoPreview = this.defaultLogo
      }
    },

    triggerLogoUpload() {
      this.$refs.logoInput.click()
    },

    handleLogoChange(event) {
      const file = event.target.files[0]
      if (!file) return

      this.selectedLogo = file
      this.form.logo = file

      this.logoPreview = URL.createObjectURL(file)
    },

    async saveSettings() {
      try {
        const formData = new FormData()

        formData.append('store_name', this.form.store_name)
        formData.append('gcash_number', this.form.gcash_number)
        formData.append('contact_number', this.form.contact_number)
        formData.append('email', this.form.email)
        formData.append('address', this.form.address)

        if (this.form.logo) {
          formData.append('logo', this.form.logo)
        }

        const res = await fetch('/api/settings', {
          method: 'POST',
          body: formData
        })

        if (!res.ok) {
          throw new Error('Failed to save settings')
        }

        const data = await res.json()

        this.successMessage = data.message || 'Settings updated successfully!'

        /* refresh preview */
        await this.fetchSettings()

        /* notify sidebar to reload logo */
        window.dispatchEvent(new Event('settings-updated'))

      } catch (error) {
        console.error(error)
      }
    }
  }
}
</script>