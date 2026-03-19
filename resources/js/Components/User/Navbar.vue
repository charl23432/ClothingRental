<template>
  <header>
    <nav class="navbar">
      <ul class="nav-links">
        <li>
          <router-link to="/men" class="nav-link" active-class="active">MEN</router-link>
        </li>

        <li>
          <router-link to="/women" class="nav-link" active-class="active">WOMEN</router-link>
        </li>

        <li>
          <router-link to="/about" class="nav-link" active-class="active">ABOUT</router-link>
        </li>

        <li>
          <router-link to="/howto" class="nav-link" active-class="active">HOW TO</router-link>
        </li>

        <li>
          <router-link to="/faq" class="nav-link" active-class="active">FAQ</router-link>
        </li>
      </ul>

      <div class="user-icon">
        <router-link :to="user ? '/profile' : '/login'">
          <img
            :src="userAvatar"
            alt="User"
            class="navbar-avatar"
          />
        </router-link>
      </div>
    </nav>
  </header>
</template>

<script>
export default {
  name: 'Navbar',
  data() {
    return {
      user: null,
      defaultAvatar: '/images/user-icon.png'
    }
  },

  computed: {
    userAvatar() {
      if (this.user && this.user.profile_photo) {
        return `/storage/${this.user.profile_photo}?t=${Date.now()}`
      }
      return this.defaultAvatar
    }
  },

  async mounted() {
    await this.fetchUser()
    window.addEventListener('profile-updated', this.fetchUser)
  },

  beforeUnmount() {
    window.removeEventListener('profile-updated', this.fetchUser)
  },

  methods: {
    async fetchUser() {
      try {
        const res = await fetch('/me', {
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        if (!res.ok) {
          this.user = null
          return
        }

        this.user = await res.json()
      } catch (error) {
        this.user = null
        console.log('User not logged in')
      }
    }
  }
}
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #cc9966;
  padding: 12px 20px;
}

.nav-links {
  display: flex;
  gap: 30px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-link {
  color: #000;
  text-decoration: none;
  font-weight: 500;
}

.nav-link.active,
.nav-link.router-link-exact-active {
  text-decoration: underline;
}

.user-icon {
  display: flex;
  align-items: center;
}

.navbar-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
}
</style>