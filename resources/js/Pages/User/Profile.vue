<template>
  <section class="profile-root">
    <!-- USER SUMMARY -->
    <div class="profile-top">
      <div class="profile-left">
        <div class="avatar-wrap">
          <img :src="avatarSrc" alt="Avatar" id="profileAvatar" />
        </div>
      </div>

      <div class="profile-right">
        <div class="user-lines">
          <div><strong>Name:</strong> <span id="userNameLine">{{ user.name || '—' }}</span></div>
          <div><strong>Number:</strong> <span id="userNumberLine">{{ user.number || user.contact_number || '—' }}</span></div>
          <div><strong>Email:</strong> <span id="userEmailLine">{{ user.email || '—' }}</span></div>
          <div><strong>Home Address:</strong> <span id="userAddressLine">{{ user.address || '—' }}</span></div>
        </div>
      </div>
    </div>

    <!-- TABS -->
    <div class="profile-tabs">
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'rented' }"
        @click="activeTab = 'rented'"
      >
        Rented Items
      </button>

      <button
        class="tab-btn"
        :class="{ active: activeTab === 'notif' }"
        @click="activeTab = 'notif'"
      >
        Notifications
      </button>

      <button
        class="tab-btn"
        :class="{ active: activeTab === 'settings' }"
        @click="activeTab = 'settings'"
      >
        Settings
      </button>
    </div>

    <div class="profile-panel">
      <!-- RENTED ITEMS -->
      <div v-if="activeTab === 'rented'" id="tab-rented" class="tab-content active">
        <div class="rented-list">
          <template v-if="reservations.length">
            <div
              v-for="reservation in reservations"
              :key="reservation.id"
              class="rented-card"
            >
              <div
                class="rented-card"
                :class="{ 'rent-again': ['Returned', 'Cancelled'].includes(reservation.status) }"
              ></div>

              <div class="rented-left">
                <img :src="productImage(reservation.product?.image)" />
              </div>

              <div class="rented-middle">
                <h4>{{ reservation.product?.item_name || 'Deleted Product' }}</h4>

                <div class="meta">
                  <span class="chip">{{ capitalize(reservation.size || '-') }}</span>
                  <span class="chip">{{ capitalize(reservation.delivery || '-') }}</span>
                </div>

                <div class="small">Rent Date: {{ formatDateTime(reservation.rent_time) }}</div>
                <div class="small">GCash Ref: {{ reservation.gcash_reference || '-' }}</div>
                <div class="small">Status: {{ reservation.status || '-' }}</div>
              </div>

              <div class="rented-right">
                <div class="price-strip">
                  ₱{{ formatPrice(reservation.price || 0) }}
                </div>

                <button
                  type="button"
                  class="link-update"
                  :class="{ disabled: ['Returned', 'Cancelled'].includes(reservation.status) }"
                  :disabled="['Returned', 'Cancelled'].includes(reservation.status)"
                  @click="openEditModal(reservation)"
                >
                  Update Details
                </button>
              </div>
            </div>
          </template>

          <p v-else>You have no rented items yet.</p>
        </div>
      </div>

            <!-- NOTIFICATIONS -->
      <div v-if="activeTab === 'notif'" id="tab-notif" class="tab-content active">
        <div class="rented-list">
          <template v-if="notifications.length">
            <div v-for="notif in notifications" :key="notif.id" class="notif-card">
              <div class="notif-left">
                <img :src="productImage(notif.data?.image)" />
              </div>

              <div class="notif-body">
                <h4>{{ notif.data?.title || 'Notification' }}</h4>
                <p>{{ notif.data?.message || '' }}</p>
                <small>{{ formatDateTime(notif.created_at) }}</small>
              </div>
            </div>
          </template>

          <p v-else>No notifications yet.</p>
        </div>
      </div>

      <!-- SETTINGS -->
      <div v-if="activeTab === 'settings'" id="tab-settings" class="tab-content active">
        <form id="profileForm" @submit.prevent="updateProfile">
          <div class="settings-grid">
            <!-- AVATAR -->
            <div class="avatar-edit">
              <div class="avatar-wrap-large">
                <img
                  id="settingsAvatar"
                  :src="settingsAvatarSrc"
                  style="width:150px;height:150px; border-radius:50%; object-fit:cover; display:block;"
                />
              </div>

              <input
                type="file"
                id="avatarUpload"
                accept="image/*"
                @change="onAvatarChange"
              />

              <button
                type="button"
                id="btnResetPassword"
                class="small-link"
                @click="showResetPasswordModal = true"
              >
                Reset Password
              </button>
            </div>

            <!-- FORM FIELDS -->
            <div class="profile-form">
              <label>Name</label>
              <input v-model="form.name" id="inputName" type="text" />

              <label>Number</label>
              <input v-model="form.number" id="inputNumber" type="text" />

              <label>Email</label>
              <input v-model="form.email" id="inputEmail" type="email" />

              <label>Home Address</label>
              <input v-model="form.address" id="inputAddress" type="text" />

              <div class="settings-actions">
                <button type="submit" class="action-btn update">
                  UPDATE PROFILE
                </button>

                <button type="button" class="action-btn logout" @click="logout">
                  LOGOUT
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- EDIT RENTAL DETAILS MODAL -->
  <div v-if="showEditModal" id="editDetailsModal" class="modal-overlay">
    <div class="modal-content" style="width: fit-content;">
      <span class="close-btn" @click="closeEditModal">×</span>
      <h2>Edit Details</h2>

      <form id="editReservationForm" @submit.prevent="updateReservation">
        <input type="hidden" v-model="editForm.id" />

        <label>Select Size</label>
        <div class="size-options">
          <button
            v-for="size in availableSizes"
            :key="size"
            type="button"
            class="size-btn"
            :class="{ active: editForm.size === size }"
            @click="editForm.size = size"
          >
            {{ capitalize(size) }}
          </button>
        </div>

        <div class="delivery-method">
          <label>
            <input type="radio" v-model="editForm.delivery" value="delivery" />
            For Delivery
          </label>

          <label>
            <input type="radio" v-model="editForm.delivery" value="pickup" />
            For Pick Up
          </label>
        </div>

        <label>Rent Time</label>
        <input type="datetime-local" v-model="editForm.rent_time" id="rent_time" />

        <div class="modal-actions" style="text-align: center;">
          <button type="submit" class="update-btn">UPDATE DETAILS</button><br />
          <button type="button" class="cancel-btn" @click="closeEditModal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- RESET PASSWORD MODAL -->
  <div v-if="showResetPasswordModal" id="resetPasswordModal" class="modal-overlay">
    <div class="modal-content reset-modal">
      <span class="close-btn" @click="closeResetPasswordModal">×</span>
      <h2>Reset Password</h2>

      <form id="resetPasswordForm" class="reset-form" @submit.prevent="resetPassword">
        <input
          type="password"
          v-model="passwordForm.current_password"
          placeholder="Current Password"
          required
        />
        <small class="error-msg">{{ passwordErrors.current_password || '' }}</small>

        <input
          type="password"
          v-model="passwordForm.password"
          placeholder="New Password"
          required
        />
        <small class="error-msg">{{ passwordErrors.password || '' }}</small>

        <input
          type="password"
          v-model="passwordForm.password_confirmation"
          placeholder="Confirm Password"
          required
        />
        <small class="error-msg">{{ passwordErrors.password_confirmation || '' }}</small>

        <button type="submit" class="reset-btn">RESET PASSWORD</button>
        <button type="button" class="cancel-btn" @click="closeResetPasswordModal">
          CANCEL
        </button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Profile',
  data() {
    return {
      activeTab: 'rented',

      user: {
        name: '',
        number: '',
        email: '',
        address: '',
        profile_photo: ''
      },

      reservations: [],
      notifications: [],

      form: {
        name: '',
        number: '',
        email: '',
        address: '',
        avatar: null
      },

      showEditModal: false,
      showResetPasswordModal: false,

      editForm: {
        id: null,
        size: '',
        delivery: '',
        rent_time: ''
      },

      availableSizes: [],

      passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: ''
      },

      passwordErrors: {},

      defaultAvatar: '/images/user-icon.png',
      avatarPreview: ''
    }
  },

  computed: {
    avatarSrc() {
      return this.user.profile_photo
        ? `/storage/${this.user.profile_photo}`
        : this.defaultAvatar
    },
    settingsAvatarSrc() {
      return this.avatarPreview || this.avatarSrc
    }
  },

  async mounted() {
    await this.fetchProfile()
  },

  methods: {
    getCsrfToken() {
      return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || ''
    },

    async fetchProfile() {
      try {
        const res = await fetch('/api/profile', {
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        if (!res.ok) {
          throw new Error('Failed to load profile')
        }

        const data = await res.json()

        this.user = data.user || {}
        this.reservations = data.reservations || []
        this.notifications = data.notifications || []

        this.form.name = this.user.name || ''
        this.form.number = this.user.number || this.user.contact_number || ''
        this.form.email = this.user.email || ''
        this.form.address = this.user.address || ''
      } catch (error) {
        console.error('Failed to load profile:', error)
      }
    },

    onAvatarChange(event) {
      const file = event.target.files[0]
      if (!file) return

      this.form.avatar = file
      this.avatarPreview = URL.createObjectURL(file)
    },

    async updateProfile() {
  try {
    const formData = new FormData()
    formData.append('name', this.form.name)
    formData.append('number', this.form.number || '')
    formData.append('email', this.form.email)
    formData.append('address', this.form.address || '')

    if (this.form.avatar) {
      formData.append('avatar', this.form.avatar)
    }

    const res = await fetch('/api/profile/update', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': this.getCsrfToken(),
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.message || 'Failed to update profile')
    }

    await this.fetchProfile()

    // force navbar/avatar refresh
    window.dispatchEvent(new Event('profile-updated'))

    this.activeTab = 'settings'
  } catch (error) {
    console.error('Failed to update profile:', error)
  }
},

    openEditModal(reservation) {
      this.editForm.id = reservation.id
      this.editForm.size = (reservation.size || '').toLowerCase()
      this.editForm.delivery = reservation.delivery || ''
      this.editForm.rent_time = this.toDateTimeLocal(reservation.rent_time)

      this.availableSizes = this.extractAvailableSizes(reservation.product?.sizes)
      this.showEditModal = true
    },

    closeEditModal() {
      this.showEditModal = false
      this.editForm = {
        id: null,
        size: '',
        delivery: '',
        rent_time: ''
      }
      this.availableSizes = []
    },

    extractAvailableSizes(sizes) {
      if (!sizes) return []

      try {
        const parsed = typeof sizes === 'string' ? JSON.parse(sizes) : sizes
        return Array.isArray(parsed)
          ? parsed.map(s => (s.size || '').toLowerCase()).filter(Boolean)
          : []
      } catch {
        return []
      }
    },

    async updateReservation() {
      try {
        const res = await fetch(`/api/reservations/${this.editForm.id}/update`, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          body: JSON.stringify({
            size: this.editForm.size,
            delivery: this.editForm.delivery,
            rent_time: this.editForm.rent_time
          })
        })

        if (!res.ok) {
          throw new Error('Failed to update reservation')
        }

        this.closeEditModal()
        await this.fetchProfile()
      } catch (error) {
        console.error('Failed to update reservation:', error)
      }
    },

    closeResetPasswordModal() {
      this.showResetPasswordModal = false
      this.passwordForm = {
        current_password: '',
        password: '',
        password_confirmation: ''
      }
      this.passwordErrors = {}
    },

    async resetPassword() {
      try {
        this.passwordErrors = {}

        const res = await fetch('/api/profile/reset-password', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          body: JSON.stringify(this.passwordForm)
        })

        const data = await res.json()

        if (!res.ok) {
          this.passwordErrors = data.errors || {}
          return
        }

        this.closeResetPasswordModal()
      } catch (error) {
        console.error('Failed to reset password:', error)
      }
    },

    async logout() {
      try {
        const res = await fetch('/logout', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': this.getCsrfToken(),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const data = await res.json()
        window.location.href = data.redirect || '/login'
      } catch (error) {
        console.error('Logout failed:', error)
      }
    },

    productImage(image) {
      return image ? `/storage/${image}` : '/images/user-icon.png'
    },

    formatPrice(price) {
      return Number(price).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    formatDateTime(value) {
      if (!value) return '-'
      const date = new Date(value)
      if (Number.isNaN(date.getTime())) return value
      return date.toLocaleString()
    },

    toDateTimeLocal(value) {
      if (!value) return ''
      const date = new Date(value)
      if (Number.isNaN(date.getTime())) return ''
      const pad = n => String(n).padStart(2, '0')
      return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
    },

    capitalize(value) {
      if (!value || value === '-') return value
      return value.charAt(0).toUpperCase() + value.slice(1)
    }
  }
}
</script>