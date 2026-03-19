<template>
  <div class="auth-page">
    <div class="background"></div>

    <div class="container">
      <div class="card">
        <div class="logo">
          <img src="/public/images/hfhmn-Picsart-BackgroundRemover.jpg" alt="logo">
        </div>

        <h2>Forgot Password</h2>

        <p v-if="successMessage" class="success-message">
          {{ successMessage }}
        </p>

        <div v-if="errors.length" class="error-box">
          <ul>
            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
          </ul>
        </div>

        <p class="instruction">
          Enter your email and we'll send you a password reset link.
        </p>

        <form @submit.prevent="sendResetLink">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            placeholder="username@gmail.com"
            required
          />

          <button :disabled="loading">
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </form>

        <p class="login-link">
          Back to
          <router-link to="/auth/login">Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  name: 'ForgotPassword',
  data() {
    return {
      loading: false,
      successMessage: '',
      errors: [],
      form: {
        email: ''
      }
    }
  },
  methods: {
    async sendResetLink() {
      this.loading = true
      this.errors = []
      this.successMessage = ''

      try {
        const res = await fetch('/forgot-password', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(this.form)
        })

        const data = await res.json()

        if (!res.ok) {
          if (data.errors) {
            this.errors = Object.values(data.errors).flat()
          } else {
            this.errors = [data.message || 'Failed to send reset link.']
          }
          return
        }

        this.successMessage = data.message
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
/* ===============================
   GLOBAL STYLES
================================*/
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

body {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: url('../images/boutique_background.png') no-repeat center center/cover;
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
  margin-bottom: -0px;
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

/* ===============================
   FORM STYLES
================================*/
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
}

.password-container {
  position: relative;
  width: 100%;
}

.password-container input {
  width: 100%;
}

#togglePassword {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 18px;
  color: #555;
  cursor: pointer;
  background: transparent;
  transition: color 0.2s ease;
  z-index: 2;
}

#togglePassword:hover {
  color: #3b2314;
}

/* ===============================
   BUTTONS + LINKS
================================*/
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

.login-link,
.register-link,
.forgot-link {
  margin-top: 10px;
  font-size: 14px;
}

.login-link a,
.register-link a,
.forgot-link a {
  color: #0044ff;
  text-decoration: none;
}

.login-link a:hover,
.register-link a:hover,
.forgot-link a:hover {
  text-decoration: underline;
}

.password-container {
  position: relative;
  width: 100%;
}

.password-container input {
  width: 100%;
  padding-right: 35px; /* space for the eye */
}

.eye-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 18px;
  color: #555;
  user-select: none;
  transition: color 0.2s ease;
}

.eye-icon:hover {
  color: #3b2314;
}


.error-box {
  color: red;
  margin-bottom: 12px;
}

.success-message {
  color: green;
  margin-bottom: 10px;
}

.instruction {
  font-size: 14px;
  margin-bottom: 10px;
}
</style>