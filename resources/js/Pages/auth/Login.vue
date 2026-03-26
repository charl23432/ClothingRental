<template>
  <div class="auth-page">
    <div class="background"></div>

    <div class="container">
      <div class="card">
        <div class="logo">
          <img src="/public/images/hfhmn-Picsart-BackgroundRemover.jpg" alt="logo">
        </div>

        <h2>Logins</h2>

        <div v-if="errors.length" class="error-box">
          <ul>
            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
          </ul>
        </div>

        <form @submit.prevent="loginUser">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            placeholder="username@gmail.com"
            required
          />

          <label for="password">Password</label>
          <div class="password-container">
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              v-model="form.password"
              placeholder="Password"
              required
            />

            <span class="eye-icon" @click="showPassword = !showPassword">
              <svg
                v-if="!showPassword"
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>

              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.17-6.58"/>
                <path d="M1 1l22 22"/>
                <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-4.21 5.34"/>
              </svg>
            </span>
          </div>

          <p class="forgot-link">
            <router-link to="/forgot-password">Forgot Password?</router-link>
          </p>

          <button class="login-btn" :disabled="loading">
            {{ loading ? 'Logging in...' : 'Login' }}
          </button>
        </form>

        <p class="login-link">
          Don’t have an account?
          <router-link to="/register">Register here</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      loading: false,
      showPassword: false,
      errors: [],
      form: {
        email: '',
        password: ''
      }
    }
  },

  methods: {
    async loginUser() {
      this.loading = true
      this.errors = []

      try {
        const token = document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content')

        const res = await fetch('/login', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify(this.form)
        })

        const data = await res.json()

        if (!res.ok) {
          if (data.errors) {
            this.errors = Object.values(data.errors).flat()
          } else {
            this.errors = [data.message || 'Login failed.']
          }
          return
        }

        this.$router.push(data.redirect || '/men')
      } catch (error) {
        console.error(error)
        this.errors = ['Something went wrong. Please try again.']
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

.auth-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: url('/public/images/boutique_background.png') no-repeat center center / cover;
  position: relative;
  filter: brightness(1.18) saturate(1.15);
}

.background {
  position: fixed;
  inset: 0;
  background: rgba(194, 158, 114, 0.45);
  backdrop-filter: blur(4px);
  z-index: 0;
}

.container {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: center;
  align-items: center;
}

.card {
  background: rgba(253, 250, 250, 0.155);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 40px;
  width: 380px;
  text-align: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0),
              0 0 30px rgba(194, 158, 114, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #0e0e0e;
}

.logo {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 0;
}

.logo img {
  width: 250px;
}

h2 {
  margin-top: 22px;
  margin-bottom: 22px;
  color: #2b2b2b;
  font-weight: 600;
}

form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

label {
  text-align: left;
  font-size: 14px;
  color: #3c3c3c;
}

input {
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  outline: none;
  width: 100%;
}

.password-container {
  position: relative;
  width: 100%;
}

.password-container input {
  width: 100%;
  padding-right: 45px;
  box-sizing: border-box;
}

/* Hide browser default password reveal icon */
.password-container input::-ms-reveal,
.password-container input::-ms-clear {
  display: none;
}

.eye-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
  z-index: 2;
  line-height: 1;
  user-select: none;
}

.eye-icon:hover {
  color: #3b2314;
}

button {
  margin-top: 15px;
  background-color: #3b2314;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.3s;
}

button:hover {
  background-color: #5a321e;
}

.login-link {
  margin-top: 10px;
  font-size: 14px;
}

.login-link a,
.forgot-link a {
  color: #0044ff;
  text-decoration: none;
}

.login-link a:hover,
.forgot-link a:hover {
  text-decoration: underline;
}

.forgot-link {
  margin-top: 4px;
  text-align: right;
  font-size: 13px;
}

.error-box {
  color: red;
  margin-bottom: 12px;
}
</style>