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

          <label for="password">Password</label>
          <div class="password-container">
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              v-model="form.password"
              placeholder="Password"
              required
            />
            <span class="eye-icon" @click="showPassword = !showPassword">👁</span>
          </div>

          <label for="password_confirmation">Confirm Password</label>
          <div class="password-container">
            <input
              :type="showConfirmPassword ? 'text' : 'password'"
              id="password_confirmation"
              v-model="form.password_confirmation"
              placeholder="Confirm Password"
              required
            />
            <span class="eye-icon" @click="showConfirmPassword = !showConfirmPassword">👁</span>
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

/* Warm gradient overlay */
.background {
  position: fixed;
  inset: 0;
  /* use your color with transparency */
  background: rgba(194, 158, 114, 0.45); /* #C29E72 at ~45% opacity */
  backdrop-filter: blur(4px); /* slightly stronger blur for softness */
  z-index: 0;
}


body {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: url('../images/boutique_background.png') no-repeat center center/cover;
  position: relative;
  /* enhance brightness and color for that warm glow */
  filter: brightness(1.18) saturate(1.15);
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
              0 0 30px rgba(194, 158, 114, 0.25); /* soft warm glow */
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #121111;
}

.logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: -0; /* remove large extra space */
}

.logo img {
  width: 100px; /* adjust if needed */
  margin-bottom: -5px; /* pull the heading closer */
}

h2 {
  margin-top: 25px; /* small breathing space */
  margin-bottom: 25px; /* balanced before form */
  color: #2b2b2b;
  font-weight: 600;
}



/* Form styling */
form {
  display: flex;
  flex-direction: column;
  gap: 3px;
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
  padding-right: 10px; /* space for the icon */
}

.password-container i {
  position: absolute;
  right: px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #555;
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

#toggleConfirm {
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


/* Button styling */
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

.login-link a {
  color: #0044ff;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}



.error-box {
  color: red;
  margin-bottom: 12px;
}

.eye-icon {
  cursor: pointer;
}
</style>