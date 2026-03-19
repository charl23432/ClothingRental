<template>
  <div class="admin-layout">
    <Sidebar />
    <div class="main-content">
      <router-view />
    </div>
  </div>
</template>

<script>
import Sidebar from '../Sidebar/Sidebar.vue'

export default {
  components: {
    Sidebar
  }
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  width: 100%;
  min-height: 100vh;
  background-color: #cc9966;
}


.main-content {
  flex: 1;
  min-width: 0;
  padding: 20px;
  overflow-x: auto;
}
</style>