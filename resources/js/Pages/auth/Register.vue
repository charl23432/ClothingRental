<template>
  <div class="auth-page">
    <div class="background"></div>

    <div class="container">
      <div class="card">
        <div class="logo">
          <img src="/public/images/hfhmn-Picsart-BackgroundRemover.jpg" alt="logo">
        </div>

        <h2>Create Account</h2>

        <div v-if="errors.length" class="error-box">
          <ul>
            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
          </ul>
        </div>

        <form @submit.prevent="registerUser">
          <label for="name">Full Name</label>
          <input
            type="text"
            id="name"
            v-model="form.name"
            placeholder="John Doe"
            required
          />

          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            placeholder="username@gmail.com"
            required
          />

          <!-- PASSWORD -->
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
              <!-- EYE -->
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>

              <!-- EYE SLASH -->
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.77 21.77 0 015.17-6.58"/>
                <path d="M1 1l22 22"/>
                <path d="M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.8 21.8 0 01-4.21 5.34"/>
              </svg>
            </span>
          </div>

          <!-- CONFIRM PASSWORD -->
          <label for="password_confirmation">Confirm Password</label>
          <div class="password-container">
            <input
              :type="showConfirmPassword ? 'text' : 'password'"
              id="password_confirmation"
              v-model="form.password_confirmation"
              placeholder="Confirm Password"
              required
            />

            <span class="eye-icon" @click="showConfirmPassword = !showConfirmPassword">
              <!-- EYE -->
              <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>

              <!-- EYE SLASH -->
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.77 21.77 0 015.17-6.58"/>
                <path d="M1 1l22 22"/>
                <path d="M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.8 21.8 0 01-4.21 5.34"/>
              </svg>
            </span>
          </div>

          <button class="signup-btn" :disabled="loading">
            {{ loading ? 'Signing up...' : 'Sign up' }}
          </button>
        </form>

        <p class="login-link">
          Already have an account?
          <router-link to="/auth/login">Login now</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Register',
  data() {
    return {
      loading: false,
      showPassword: false,
      showConfirmPassword: false,
      errors: [],
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      }
    }
  },
  methods: {
    async registerUser() {
      this.loading = true
      this.errors = []

      try {
        const token = document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content')

        const res = await fetch('/register', {
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
            this.errors = [data.message || 'Registration failed.']
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
}

.logo img {
  width: 100px;
}

h2 {
  margin: 25px 0;
}

form {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

label {
  text-align: left;
  font-size: 14px;
}

input {
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.password-container {
  position: relative;
}

.password-container input {
  width: 100%;
  padding-right: 45px;
}

/* REMOVE browser default eye */
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
  color: #555;
}

button {
  margin-top: 15px;
  background: #3b2314;
  color: white;
  padding: 10px;
  border: none;
  border-radius: 5px;
}

.login-link {
  margin-top: 10px;
}

.error-box {
  color: red;
}
</style>