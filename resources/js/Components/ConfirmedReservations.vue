<template>
  <div class="table-wrapper">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Customer</th>
            <th>Item</th>
            <th>Date Reserved</th>
            <th>Pickup/Delivery</th>
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
              <td>{{ capitalize(reservation.delivery) }}</td>
              <td>
                <button class="view-btn" @click="openModal(reservation)">View</button>
                <button class="cancel-btn" @click="markReturned(reservation.id)">Returned</button>
              </td>
            </tr>

            <tr v-if="filteredReservations.length === 0">
              <td colspan="5" style="text-align:center; padding:20px; opacity:0.7;">
                No confirmed reservations.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL -->
<div v-if="showModal" class="modal-overlay" @click="closeModal">
  <div class="modal-box" @click.stop>
    <h3>Reservation Details</h3>

    <!-- IMAGE -->
    <div class="modal-image">
      <img
        :src="productImage(selectedReservation.product?.image)"
        alt="Item Image"
      />
    </div>

    <p><strong>Customer:</strong> {{ selectedReservation.user?.name || 'N/A' }}</p>
    <p><strong>Item:</strong> {{ selectedReservation.product?.item_name || 'Deleted Product' }}</p>
    <p><strong>Date Reserved:</strong> {{ formatDate(selectedReservation.rent_time) }}</p>
    <p><strong>Pickup/Delivery:</strong> {{ capitalize(selectedReservation.delivery) }}</p>
    <p><strong>Size:</strong> {{ capitalize(selectedReservation.size) }}</p>
    <p><strong>Reference No.:</strong> {{ selectedReservation.gcash_reference || '-' }}</p>
    <p><strong>Price:</strong> ₱{{ formatPrice(selectedReservation.price) }}</p>
    <p><strong>Status:</strong> {{ selectedReservation.status || '-' }}</p>

    <button class="close-btn" @click="closeModal">Close</button>
  </div>
</div>
  </div>
</template>

<<script>
export default {
  props: ['search'],
  data() {
    return {
      reservations: [],
      showModal: false,
      selectedReservation: {}
    }
  },
  computed: {
    filteredReservations() {
      const keyword = (this.search || '').toLowerCase()

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
        const res = await fetch('/api/reservations/confirmed', {
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        this.reservations = await res.json()
      } catch (error) {
        console.error('Failed to fetch confirmed reservations:', error)
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleString()
    },

    formatPrice(price) {
      return Number(price || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    capitalize(str) {
      return str ? str.charAt(0).toUpperCase() + str.slice(1) : '-'
    },

    productImage(image) {
      return image ? `/storage/${image}` : '/images/hfhmn.jpg'
    },

    openModal(reservation) {
      this.selectedReservation = reservation
      this.showModal = true
    },

    closeModal() {
      this.showModal = false
      this.selectedReservation = {}
    },

    async markReturned(id) {
      if (!confirm('Mark this item as returned?')) return

      try {
        await fetch(`/api/reservations/${id}/return`, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        this.fetchReservations()
      } catch (error) {
        console.error('Failed to return reservation:', error)
      }
    }
  }
}
</script>

<style scoped>
.reservation-header {
  flex: 1;
  padding: 30px 40px;
  overflow-y: hidden;
}

.reservation-header h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #fffefe;
}

.top-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

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

.table-wrapper {
  display: flex;
  width: 100%;
}

.table-container {
  width: 100%;
  max-width: 1100px;
  margin-top: -30px;
  border-radius: 15px;
  padding: 20px 0px 20px;
}

table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 11px;
  font-size: 1rem;
  color: #000;
}

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

.view-btn {
  background-color: #9cff57;
  color: #000;
  border: none;
  padding: 6px 14px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 500;
  margin-right: 8px;
}

.view-btn:hover {
  background-color: #89e64b;
}

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

.table-container table,
.table-scroll table {
  table-layout: fixed;
  width: 100%;
}

.table-container th,
.table-scroll td {
  padding: 15px 45px;
  text-align: center;
  box-sizing: border-box;
}

.table-scroll {
  max-height: 70vh;
  overflow-y: auto;
  margin-top: 0;
  position: relative;
  top: -1px;
}

.table-scroll::after {
  content: "";
  position: sticky;
  bottom: 0;
  left: 0;
  right: 0;
  height: 35px;
  background: linear-gradient(to bottom, rgba(204,153,102,0), rgba(204,153,102,1));
  pointer-events: none;
}

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

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-box {
  background: white;
  border-radius: 14px;
  padding: 24px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.modal-box h3 {
  margin-bottom: 15px;
}

.modal-box p {
  margin: 8px 0;
}

.close-btn {
  margin-top: 15px;
  background: #cc9966;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 10px;
  cursor: pointer;
}
.modal-image {
  display: flex;
  justify-content: center;
  margin-bottom: 15px;
}

.modal-image img {
  width: 150px;
  height: 160px;
  object-fit: cover;
  border-radius: 10px;
  border: 2px solid #ddd;
}
</style>