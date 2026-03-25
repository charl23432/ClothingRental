<template>
  <div class="reservation-header">

    <h1>Reservations</h1>

    <!-- Top Controls: Tabs + Search -->
    <div class="top-controls">
      <div class="reservation-tabs">
        <button :class="{ active: activeTab === 'confirmed' }"
                @click="activeTab = 'confirmed'">
          Confirmed
        </button>

        <button :class="{ active: activeTab === 'pending' }"
                @click="activeTab = 'pending'">
          Pending
        </button>
      </div>

      <div class="search-container">
        <input v-model="search" placeholder="Search" />
      </div>
    </div>

    <!-- Table Content -->
    <component
      :is="currentTabComponent"
      :search="search"
    />
    
  </div>
</template>

<script>
import PendingReservations from '@/Components/PendingReservations.vue'
import ConfirmedReservations from '@/Components/ConfirmedReservations.vue'

export default {
  components: {
    ConfirmedReservations,
    PendingReservations
  },
  data() {
    return {
      activeTab: 'confirmed',
      search: ''
    }
  },
  computed: {
    currentTabComponent() {
      return this.activeTab === 'confirmed' ? 'ConfirmedReservations' : 'PendingReservations'
    }
  }
}
</script>
<style scoped>

/* ========== MAIN CONTENT ========== */
.reservation-header {
  flex: 1;
  padding: 30px 20px;
   margin-left: 190px;
}

/* Title */
.reservation-header h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #fffefe;
}

/* Tabs + Search Row */
.top-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

/* Tabs */
.reservation-tabs {
  display: flex;
  gap: 20px;
  align-items: center;
}

.reservation-tabs button {
  color: white;
  transition: 0.3s;
  background-color: transparent;
  border: none;
  font-size: 17px;
}

.reservation-tabs button.active,
.reservation-tabs a:hover {
  color: black;
}

/* Search Bar */
.search-container {
  display: flex;
  align-items: center;
  background-color: #fff;
  border-radius: 25px;
  padding: 8px 15px;
  width: 270px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.search-container input {
  border: none;
  outline: none;
  flex: 1;
  background: none;
  font-size: 15px;
  color: #333;
}

.search-container i {
  font-size: 16px;
  color: #000;
}


</style>