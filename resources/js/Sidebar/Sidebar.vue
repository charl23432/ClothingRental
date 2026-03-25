<template>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img :src="logoSrc" alt="Logo" />
    </div>

    <nav class="sidebar-nav">
      <ul>
        <li>
          <router-link
            to="/admin/dashboard"
            :class="{ active: isActive('/admin/dashboard') }"
          >
            <i class="fa-solid fa-house"></i><span>Dashboard</span>
          </router-link>
        </li>

        <li>
          <router-link
            to="/admin/inventory-men"
            :class="{ active: isInventoryActive }"
          >
            <i class="fa-solid fa-box-open"></i><span>Inventory</span>
          </router-link>
        </li>

        <li>
          <router-link
            to="/admin/reservations"
            :class="{ active: isActive('/admin/reservations') }"
          >
            <i class="fa-solid fa-calendar-check"></i><span>Reservation</span>
          </router-link>
        </li>

        <li>
          <router-link
            to="/admin/reports"
            :class="{ active: isActive('/admin/reports') }"
          >
            <i class="fa-solid fa-chart-column"></i><span>Report</span>
          </router-link>
        </li>

        <li>
          <router-link
            to="/admin/users"
            :class="{ active: isActive('/admin/users') }"
          >
            <i class="fa-solid fa-user"></i><span>User List</span>
          </router-link>
        </li>

        <li>
          <router-link
            to="/admin/settings"
            :class="{ active: isActive('/admin/settings') }"
          >
            <i class="fa-solid fa-gear"></i><span>Settings</span>
          </router-link>
        </li>
      </ul>
    </nav>

    <button class="logout-btn" @click="logoutUser" :disabled="loggingOut">
      {{ loggingOut ? 'Logging out...' : 'Logout' }}
    </button>
  </aside>
</template>

<script>
export default {
  name: 'Sidebar',
  data() {
    return {
      setting: null,
      defaultLogo: '/images/hfhmn.jpg',
      loggingOut: false
    }
  },

  computed: {
    logoSrc() {
      return this.setting?.logo ? `/${this.setting.logo}` : this.defaultLogo
    },

    isInventoryActive() {
      return this.$route.path.startsWith('/admin/inventory')
    }
  },

  async mounted() {
    await this.fetchSettings()
    window.addEventListener('settings-updated', this.fetchSettings)
  },

  beforeUnmount() {
    window.removeEventListener('settings-updated', this.fetchSettings)
  },

  methods: {
    isActive(path) {
      return this.$route.path === path
    },

    async fetchSettings() {
      try {
        const res = await fetch('/api/settings', {
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        if (!res.ok) throw new Error('Failed to fetch settings')

        this.setting = await res.json()
      } catch (error) {
        console.error('Failed to load sidebar logo:', error)
      }
    },

    async logoutUser() {
      this.loggingOut = true

      try {
        const token = document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content')

        const res = await fetch('/logout', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
          }
        })

        const data = await res.json()

        this.$router.push(data.redirect || '/login')
      } catch (error) {
        console.error('Logout failed:', error)
      } finally {
        this.loggingOut = false
      }
    }
  }
}
</script>

<style scoped>
.sidebar {
  width: 203px;
  height: 100vh;
  background-color: #73553A;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 0;
  border-top-right-radius: 0px;
  border-bottom-right-radius: 0px;
  position: fixed;
}

.sidebar-logo img {
  width: 140px;
  margin-bottom: -5rem;
}

.sidebar-nav {
  width: 125%;
  margin-top: 15%;
  margin-left: 11%;
}

.sidebar-nav ul {
  list-style: none;
  width: 100%;
  align-items: center;
}

.sidebar a {
  color: inherit;
  text-decoration: none;
  align-items: center;
  gap: 10px;
}

.sidebar-nav li {
  align-items: center;
  padding: 1px 30px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  color: #fff;
  transition: all 0.3s ease;
  margin-bottom: 10px;
}

.sidebar-nav li i {
  width: 25px;
  text-align: center;
}

.sidebar-nav li a {
  display: flex;
  gap: 10px;
  align-items: center;
  padding: 14px 30px 14px 30px;
  width: 100%;
  border-radius: 30px 0 0 30px;
  box-sizing: border-box;
  transition: background 0.3s;
  color: inherit;
  text-decoration: none;
}

.sidebar-nav li a:hover {
  background-color: #cc9966;
}

.sidebar-nav li a.active {
  background-color: #cc9966;
}

.logout-btn {
  background-color: #fff;
  color: #0b61f7;
  border: none;
  border-radius: 10px;
  padding: 10px 30px;
  font-weight: 500;
  font-size: 0.95rem;
  cursor: pointer;
  margin-top: 1rem;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background-color: #e6e6e6;
}

.logout-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.logout-btn i {
  margin-right: 8px;
}
</style>