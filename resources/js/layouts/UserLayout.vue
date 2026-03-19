<template>
  <div>
    <Navbar />
    <router-view />
  </div>
</template>

<script>
import Navbar from '../Components/User/Navbar.vue'

export default {
  components: {
    Navbar
  }
}
</script>