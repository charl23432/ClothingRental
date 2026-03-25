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
        class="tab-btn notif-tab-btn"
        :class="{ active: activeTab === 'notif' }"
        @click="openNotificationsTab"
      >
        Notifications
        <span v-if="unreadCount > 0" class="notif-badge">
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
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
      <div v-if="activeTab === 'rented'" id="tab-rented" class="tab-content">
        <div class="rented-list">
          <template v-if="reservations.length">
            <div
              v-for="reservation in reservations"
              :key="reservation.id"
              class="rented-card"
              :class="{ 'rent-again': ['Returned', 'Cancelled'].includes(reservation.status) }"
            >
              <div class="rented-left">
                <img :src="productImage(reservation.product?.image)" alt="Product" />
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
      <div v-if="activeTab === 'notif'" id="tab-notif" class="tab-content">
        <div class="rented-list">
          <template v-if="notifications.length">
            <div
              v-for="notif in notifications"
              :key="notif.id"
              class="notif-card"
              :class="{ unread: !notif.is_read }"
            >
              <div class="notif-left">
                <img :src="productImage(notif.image)" alt="Notification Item" />
              </div>

              <div class="notif-body">
                <h4>
                  {{ notif.title || 'Notification' }}
                  <span v-if="!notif.is_read" class="notif-dot"></span>
                </h4>

                <p>{{ notif.message || '' }}</p>
                <small>{{ formatDateTime(notif.created_at) }}</small>
              </div>
            </div>
          </template>

          <p v-else>No notifications yet.</p>
        </div>
      </div>

      <!-- SETTINGS -->
      <div v-if="activeTab === 'settings'" id="tab-settings" class="tab-content">
        <form id="profileForm" @submit.prevent="updateProfile">
          <div class="settings-grid">
            <div class="avatar-edit">
              <div class="avatar-wrap-large">
                <img
                  id="settingsAvatar"
                  :src="settingsAvatarSrc"
                  alt="Settings Avatar"
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

    <!-- EDIT RENTAL DETAILS MODAL -->
    <div v-if="showEditModal" id="editDetailsModal" class="modal-overlay">
      <div class="modal-content">
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

          <div class="modal-actions">
            <button type="submit" class="update-btn">UPDATE DETAILS</button>
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
  </section>
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
      unreadCount: 0,
      pollingInterval: null,

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

    this.pollingInterval = setInterval(async () => {
      await this.fetchProfile()
    }, 5000)
  },

  beforeUnmount() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval)
    }
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

        const text = await res.text()
        console.log('PROFILE RESPONSE:', text)

        if (!res.ok) {
          throw new Error(`Failed to load profile (${res.status})`)
        }

        const data = JSON.parse(text)

        this.user = data.user || {}
        this.reservations = data.reservations || []
        this.notifications = data.notifications || []
        this.unreadCount = data.unread_count || 0

        this.form.name = this.user.name || ''
        this.form.number = this.user.number || this.user.contact_number || ''
        this.form.email = this.user.email || ''
        this.form.address = this.user.address || ''
      } catch (error) {
        console.error('Failed to load profile:', error)
      }
    },

    async openNotificationsTab() {
      this.activeTab = 'notif'
      this.unreadCount = 0

      this.notifications = this.notifications.map(notification => ({
        ...notification,
        is_read: true
      }))

      try {
        await this.markNotificationsAsRead()
      } catch (error) {
        console.error(error)
      }
    },

    async markNotificationsAsRead() {
      try {
        const res = await fetch(`${window.location.origin}/api/profile/notifications/read`, {
          method: 'POST',
          credentials: 'include',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrfToken()
          }
        })

        const data = await res.json()
        console.log('MARK NOTIFICATIONS READ RESPONSE:', data)

        if (!res.ok) {
          throw new Error(data.message || `Failed to mark notifications as read (${res.status})`)
        }

        this.unreadCount = 0
        this.notifications = this.notifications.map(notification => ({
          ...notification,
          is_read: true
        }))
      } catch (error) {
        console.error('Failed to mark notifications as read:', error)
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

        const text = await res.text()
        console.log('PROFILE UPDATE RESPONSE:', text)

        if (!res.ok) {
          throw new Error(`Failed to update profile (${res.status})`)
        }

        await this.fetchProfile()
        window.dispatchEvent(new Event('profile-updated'))
        this.activeTab = 'settings'
        alert('Profile updated successfully.')
      } catch (error) {
        console.error('Failed to update profile:', error)
        alert('Failed to update profile.')
      }
    },

    openEditModal(reservation) {
      console.log('clicked update', reservation)

      this.editForm.id = reservation.id
      this.editForm.size = (reservation.size || '').toLowerCase()
      this.editForm.delivery = (reservation.delivery || '').toLowerCase()
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

        const text = await res.text()
        console.log('RESERVATION UPDATE RESPONSE:', text)

        if (!res.ok) {
          throw new Error(`Failed to update reservation (${res.status})`)
        }

        this.closeEditModal()
        await this.fetchProfile()
        alert('Reservation updated successfully.')
      } catch (error) {
        console.error('Failed to update reservation:', error)
        alert('Failed to update reservation.')
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
        alert('Password reset successfully.')
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

<style scoped>
.profile-root {
  padding: 24px;
  background: #f3f3f3;
  min-height: 100vh;
  box-sizing: border-box;
}

.profile-top {
  display: flex;
  gap: 24px;
  align-items: center;
  margin-bottom: 24px;
}

.profile-left {
  flex: 0 0 auto;
}

.avatar-wrap {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  overflow: hidden;
  background: #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-right {
  flex: 1;
}

.user-lines {
  display: grid;
  gap: 10px;
  font-size: 16px;
}

.profile-tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 0;
}

.tab-btn {
  background: #fff;
  border: none;
  padding: 14px 28px;
  cursor: pointer;
  font-weight: 700;
  border-radius: 16px 16px 0 0;
  font-size: 16px;
}

.tab-btn.active {
  background: #b6845d;
  color: #fff;
}

.notif-tab-btn {
  position: relative;
  padding-right: 46px;
}

.notif-badge {
  position: absolute;
  top: 6px;
  right: 10px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ff1f3d;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  box-sizing: border-box;
}

.profile-panel {
  background: #b6845d;
  padding: 35px 25px;
  border-radius: 0 0 16px 16px;
}

.tab-content {
  display: block;
}

.rented-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.rented-card,
.notif-card {
  background: #e9e9e9;
  border-radius: 24px;
  padding: 22px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  position: relative;
}

.rent-again {
  opacity: 0.75;
}

.rented-left img,
.notif-left img {
  width: 140px;
  height: 175px;
  object-fit: cover;
  border-radius: 12px;
  display: block;
}

.rented-middle,
.notif-body {
  flex: 1;
}

.rented-middle h4,
.notif-body h4 {
  margin: 0 0 12px;
  font-size: 20px;
}

.meta {
  display: flex;
  gap: 8px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.chip {
  background: #ccb08c;
  color: #fff;
  border-radius: 999px;
  padding: 8px 14px;
  font-size: 14px;
}

.small {
  font-size: 15px;
  margin-bottom: 4px;
  color: #2c2c2c;
}

.rented-right {
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-end;
  justify-content: center;
  min-width: 170px;
  position: relative;
  z-index: 2;
}

.price-strip {
  background: #a8c85a;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 18px;
  font-weight: 700;
}

.link-update {
  background: transparent;
  border: none;
  text-decoration: underline;
  font-size: 16px;
  cursor: pointer;
  color: #111;
  pointer-events: auto;
}

.link-update.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  text-decoration: none;
}

.settings-grid {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 30px;
  align-items: start;
}

.avatar-edit {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.avatar-wrap-large {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  background: #ddd;
}

.avatar-wrap-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.profile-form {
  display: grid;
  gap: 10px;
}

.profile-form input {
  width: 100%;
  max-width: 500px;
  padding: 12px 14px;
  border: none;
  border-radius: 10px;
  font-size: 15px;
}

.settings-actions {
  display: flex;
  gap: 14px;
  margin-top: 12px;
  flex-wrap: wrap;
}

.action-btn,
.update-btn,
.cancel-btn,
.reset-btn,
.small-link {
  border: none;
  border-radius: 10px;
  cursor: pointer;
  padding: 12px 18px;
  font-weight: 700;
}

.action-btn.update,
.update-btn,
.reset-btn {
  background: #89d64a;
  color: #111;
}

.action-btn.logout,
.cancel-btn {
  background: #d9534f;
  color: #fff;
}

.small-link {
  background: #fff;
  color: #111;
  width: fit-content;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.modal-content {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  width: 100%;
  max-width: 520px;
  position: relative;
  z-index: 10000;
  box-sizing: border-box;
}

.reset-modal {
  max-width: 430px;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 14px;
  font-size: 28px;
  cursor: pointer;
  line-height: 1;
}

.size-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 10px 0 16px;
}

.size-btn {
  border: 1px solid #ccc;
  background: #f8f8f8;
  border-radius: 10px;
  padding: 8px 14px;
  cursor: pointer;
}

.size-btn.active {
  background: #b6845d;
  color: #fff;
  border-color: #b6845d;
}

.delivery-method {
  display: flex;
  gap: 20px;
  margin: 16px 0;
  flex-wrap: wrap;
}

.modal-content input[type="datetime-local"],
.reset-form input {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #ddd;
  border-radius: 10px;
  margin-top: 6px;
  margin-bottom: 12px;
  box-sizing: border-box;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 14px;
  justify-content: center;
  flex-wrap: wrap;
}

.error-msg {
  display: block;
  color: #d9534f;
  min-height: 18px;
  margin-top: -6px;
  margin-bottom: 8px;
}

.notif-card {
  display: flex;
  gap: 15px;
  padding: 15px;
  border-radius: 12px;
  background: #f5f5f5;
  margin-bottom: 12px;
  transition: 0.3s;
}

.notif-card.unread {
  transition: 0.4s;
  background: #b9b5b5;
  font-weight: 600;
}

.notif-left img {
  width: 70px;
  height: 90px;
  object-fit: cover;
  border-radius: 8px;
}

.notif-body h4 {
  display: flex;
  align-items: center;
  gap: 6px;
}

.notif-dot {
  width: 8px;
  height: 8px;
  background: rgb(162, 18, 18);
  border-radius: 50%;
  display: inline-block;
}

@media (max-width: 900px) {
  .profile-top {
    flex-direction: column;
    align-items: flex-start;
  }

  .settings-grid {
    grid-template-columns: 1fr;
  }

  .rented-card,
  .notif-card {
    flex-direction: column;
    align-items: flex-start;
  }

  .rented-right {
    align-items: flex-start;
    min-width: auto;
  }
}
</style>