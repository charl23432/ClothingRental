<template>
  <div class="table-wrapper">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Customer</th>
            <th>Item</th>
            <th>Date Reserved</th>
            <th>Reference No.</th>
            <th>Action</th>
          </tr>
        </thead>
      </table>

      <div class="table-scroll">
        <table>
          <tbody>
            <tr v-for="reservation in filteredReservations" :key="reservation.id">
              <td>{{ reservation.user?.name || 'N/A' }}</td>
              <td>{{ reservation.product?.item_name || 'Deleted Product' }}</td>
              <td>{{ formatDate(reservation.rent_time) }}</td>
              <td>{{ reservation.gcash_reference || '-' }}</td>
              <td>
                <button class="confirm" @click="confirmReservation(reservation.id)" :disabled="reservation.product?.quantity === 0">
                  Confirm
                </button>
                <button class="cancel-btn" @click="cancelReservation(reservation.id)">
                  Cancel
                </button>
              </td>
            </tr>

            <tr v-if="filteredReservations.length === 0">
              <td colspan="5" style="text-align:center; padding:20px; opacity:0.7;">
                No pending reservations.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['search'],
  data() {
    return {
      reservations: []
    }
  },
  computed: {
    filteredReservations() {
      const keyword = this.search.toLowerCase()

      return this.reservations.filter(r =>
        (r.user?.name || '').toLowerCase().includes(keyword) ||
        (r.product?.item_name || '').toLowerCase().includes(keyword)
      )
    }
  },
  mounted() {
    this.fetchReservations()
  },
  methods: {
    async fetchReservations() {
      try {
        const res = await fetch('/api/reservations/pending')
        this.reservations = await res.json()
      } catch (error) {
        console.error('Failed to fetch pending reservations:', error)
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleString()
    },
    async confirmReservation(id) {
      if (!confirm('Confirm this reservation?')) return

      try {
        await fetch(`/api/reservations/${id}/confirm`, { method: 'POST' })
        this.fetchReservations()
      } catch (error) {
        console.error('Failed to confirm reservation:', error)
      }
    },
    async cancelReservation(id) {
      if (!confirm('Cancel this reservation?')) return

      try {
        await fetch(`/api/reservations/${id}/cancel`, { method: 'POST' })
        this.fetchReservations()
      } catch (error) {
        console.error('Failed to cancel reservation:', error)
      }
    }
  }
}
</script>
<style scoped>

/* ========== MAIN CONTENT ========== */
.reservation-header {
  flex: 1;
  padding: 30px 40px;
  overflow-y: hidden;
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

.reservation-tabs a {
  text-decoration: none;
  color: white;
  transition: 0.3s;
}

.reservation-tabs a.active,
.reservation-tabs a:hover {
  color: black;
  border-bottom: 2px solid black;
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


/* Table */
.table-wrapper {
  display: flex;
  justify-content: none;
  width: 100%;
  

}

.table-container {
  width: 100%;
  max-width: 1100px;
  margin-top: -30px;
  border-radius: 15px;
  padding: 20px 0px 20px;
  


}

/* Table layout */
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 11px;
  font-size: 1rem;
  color: #000;
}

/* Header */
thead th {
  background-color: #fff;
  color: #000;
  font-weight: 700;
  padding: 15px 50px;
  text-align: center;

}

thead th:first-child {
  border-top-left-radius: 15px;
}

thead th:last-child {
  border-top-right-radius: 15px;
}

/* Body */
tbody tr {
  background: #fff;
  border-radius: 13px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

tbody td {
  padding: 15px 50px;
  text-align: center;
  color: #000;
  font-weight: 500;
}

tbody tr td:first-child {
  border-top-left-radius: 15px;
  border-bottom-left-radius: 15px;
}

tbody tr td:last-child {
  border-top-right-radius: 15px;
  border-bottom-right-radius: 15px;
}
.confirm{
  background-color: #9cff57;
  color: #000;
  border: none;
  padding: 6px 14px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.3s ease;
}

/* Cancel button */
.cancel-btn {
  background-color: #e6e6e6;
  color: #000;
  border: none;
  padding: 6px 14px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.3s ease;
}

.cancel-btn:hover {
  background-color: #d5d5d5;
}

/* === FIX ALIGNMENT & ADD FADE EFFECT === */

/* Ensure header and body columns stay aligned */
.table-container table,
.table-scroll table {
  table-layout: fixed;
  width: 100%;

}

/* Uniform cell layout */
.table-container th,
.table-scroll td {
  padding: 15px 45px;
  text-align: center;
  box-sizing: border-box;
}

/* Scrollable area */
.table-scroll {
  max-height: 70vh;
  overflow-y: auto;
  margin-top: 0;
  position: relative;
  top: -1px; /* removes small gap between header and body */
}

/* Optional fade shadow at bottom */
.table-scroll::after {
  content: "";
  position: sticky;
  bottom: 0;
  left: 0;
  right: 0;
  height: 35px; /* fade height */
  background: linear-gradient(to bottom, rgba(204,153,102,0), rgba(204,153,102,1));
  pointer-events: none;
}

/* Scrollbar styling (optional, matches theme) */
.table-scroll::-webkit-scrollbar {
  width: 2px;
}
.table-scroll::-webkit-scrollbar-thumb {
  background-color: #ffffff00;
  border-radius: 100px;
}
.table-scroll::-webkit-scrollbar-track {
  background-color: #cc9966;
}

</style>