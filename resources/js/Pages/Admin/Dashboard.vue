<template>
  <div class="main-content">

    <h1>Dashboard</h1>

    <!-- Summary Cards -->
    <div class="cards">
      <div class="card">
        <div class="card-icon">🗓️</div>
        <h2>Total Reservations</h2>
        <p>{{ totalReservations }}</p>
      </div>

      <div class="card">
        <div class="card-icon">✅</div>
        <h2>Pending Approvals</h2>
        <p>{{ pendingApprovals }}</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="charts">
      <div class="chart">
        <h3>Rental Trends</h3>
        <canvas ref="rentalTrends"></canvas>
      </div>

      <div class="chart">
        <h3>Most Rented Items</h3>
        <canvas ref="mostRented"></canvas>
      </div>
    </div>

    <!-- Recent Reservations Table -->
    <div class="table-container">
      <h3>Recent Reservations</h3>

      <table>
        <thead>
          <tr>
            <th>Customer</th>
            <th>Item</th>
            <th>Date Reserved</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="reservation in recentReservations" :key="reservation.id">
            <td>{{ reservation.user_name }}</td>
            <td>{{ reservation.item_name }}</td>
            <td>{{ reservation.rent_time }}</td>
            <td>{{ reservation.status }}</td>
          </tr>

          <tr v-if="recentReservations.length === 0">
            <td colspan="4" style="text-align:center; padding:10px;">
              No recent reservations found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  data() {
    return {
      totalReservations: 0,
      pendingApprovals: 0,
      recentReservations: [],
      monthlyData: [],
      menCount: 0,
      womenCount: 0,
      rentalChart: null,
      rentedChart: null
    }
  },

  mounted() {
    this.fetchDashboard()
  },

  methods: {
    async fetchDashboard() {
      try {
        const res = await fetch('/api/dashboard')
        const data = await res.json()

        this.totalReservations = data.totalReservations
        this.pendingApprovals = data.pendingApprovals
        this.recentReservations = data.recentReservations
        this.monthlyData = data.monthlyData
        this.menCount = data.menCount
        this.womenCount = data.womenCount

        this.initCharts()
      } catch (error) {
        console.error('Dashboard fetch error:', error)
      }
    },

    initCharts() {
      if (this.rentalChart) {
        this.rentalChart.destroy()
      }

      if (this.rentedChart) {
        this.rentedChart.destroy()
      }

      this.rentalChart = new Chart(this.$refs.rentalTrends, {
        type: 'line',
        data: {
          labels: this.monthlyData.map(d => d.month),
          datasets: [{
            label: 'Rentals',
            data: this.monthlyData.map(d => d.count),
            backgroundColor: 'rgba(204,153,102,0.2)',
            borderColor: '#cc9966',
            borderWidth: 2,
            fill: true
          }]
        }
      })

      this.rentedChart = new Chart(this.$refs.mostRented, {
        type: 'bar',
        data: {
          labels: ['Men', 'Women'],
          datasets: [{
            label: 'Most Rented Items',
            data: [this.menCount, this.womenCount],
            backgroundColor: ['#73553A', '#cc9966']
          }]
        }
      })
    }
  }
}
</script>
<style scoped>
/* Main Content */
.main-content {
  flex: 1;
   padding: 30px 20px;
  overflow-y: auto;
  margin-left: 250px;
}

.main-content h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #fffefe;
}
.cards {
  display: flex;
  gap: 20px;
  margin-bottom: 2rem;
  color: #000;
}

.card {
  background-color: white;
  border-radius: 12px;
  flex: 1;

  /* NEW: Center content vertically + horizontally */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;

  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  color: #000;

  /* NEW: Force equal height (optional, tweak as needed) */
  min-height: 180px;
}

.card-icon {
  font-size: 2rem;
  margin-bottom: 10px;
}


/* Charts */
.charts {
  display: flex;
  gap: 20px;
  margin-bottom: 2rem;
  color: #000;
}

.chart {
  background-color: white;
  border-radius: 12px;
  padding: 1rem;
  flex: 1;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  color: #000;
}

.table-container {
  width: 100%;
  background-color: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  color: #000;
  padding: 10px 20px 10px 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

</style>