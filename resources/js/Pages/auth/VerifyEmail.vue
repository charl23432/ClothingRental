<template>
  <div class="auth-page">
    <div class="container">
      <div class="card">
        <h2>Verify Your Email</h2>
        <p class="message">
          Your account was created successfully. Please check your Gmail inbox
          and click the verification link before logging in.
        </p>

        <button @click="resendVerification" :disabled="loading">
          {{ loading ? 'Sending...' : 'Resend Verification Email' }}
        </button>

        <p v-if="successMessage" class="success">{{ successMessage }}</p>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

        <router-link to="/auth/login">Go to Login</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VerifyEmail',
  data() {
    return {
      loading: false,
      successMessage: '',
      errorMessage: ''
    }
  },
  methods: {
    async resendVerification() {
      this.loading = true
      this.successMessage = ''
      this.errorMessage = ''

      try {
        const token = document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content')

        const res = await fetch('/email/verification-notification', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
          }
        })

        const data = await res.json()

        if (!res.ok) {
          this.errorMessage = data.message || 'Failed to resend verification email.'
          return
        }

        this.successMessage = data.message || 'Verification email sent successfully.'
      } catch (error) {
        this.errorMessage = 'Something went wrong. Please try again.'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
.container {
  width: 100%;
  display: flex;
  justify-content: center;
}
.card {
  width: 400px;
  padding: 30px;
  border-radius: 16px;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);
  text-align: center;
}
.message {
  margin: 15px 0;
}
button {
  margin-top: 15px;
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  background: #3b2314;
  color: white;
  cursor: pointer;
}
.success {
  color: green;
  margin-top: 15px;
}
.error {
  color: red;
  margin-top: 15px;
}
</style>