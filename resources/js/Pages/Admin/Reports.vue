<template>
  <div class="report-content">
    <div class="report-wrapper">
    <div class="report-header">
      <h1>Report</h1>
      <form @submit.prevent="exportPDF" class="export-form">
        <button type="submit" class="export-btn">Export PDF</button>
      </form>
    </div>

    <div class="summary-grid">
      <div class="summary-card">
        <h4>Completed Rentals</h4>
        <p class="summary-number">{{ confirmed }}</p>
      </div>

      <div class="summary-card">
        <h4>Returned</h4>
        <p class="summary-number">{{ returned }}</p>
      </div>

      <div class="summary-card">
        <h4>Cancelled</h4>
        <p class="summary-number">{{ cancelled }}</p>
      </div>

      <div class="summary-card">
        <h4>Total Income</h4>
        <p class="summary-number">
          ₱{{ totalIncome.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
        </p>
      </div>
    </div>

    <div class="report-grid">
      <div class="chart-card">
        <h3>Monthly Reservations</h3>
        <canvas ref="monthlyReservations"></canvas>
      </div>

      <div class="chart-card">
        <h3>Most Rented Items</h3>
        <canvas ref="mostRentedItems"></canvas>
      </div>

      <div class="chart-card">
        <h3>Monthly Income</h3>
        <canvas ref="monthlyIncome"></canvas>
      </div>

      <div class="chart-card">
        <h3>Reservation Status</h3>
        <canvas ref="reservationStatus"></canvas>
      </div>
    </div>
  </div>
  </div>
</template>

<script>
import Chart from "chart.js/auto";

export default {
  name: "Report",
  data() {
    return {
      confirmed: 0,
      pending: 0,
      cancelled: 0,
      returned: 0,
      totalIncome: 0,
      monthlyReservations: [],
      monthlyIncome: [],
      mostRentedItems: [],

      charts: {
        monthlyReservations: null,
        mostRentedItems: null,
        monthlyIncome: null,
        reservationStatus: null,
      },
    };
  },
  methods: {
    async fetchReportData() {
      try {
        const res = await fetch("/api/reports");

        if (!res.ok) {
          throw new Error("Failed to fetch report data");
        }

        const data = await res.json();

        this.confirmed = data.confirmed || 0;
        this.pending = data.pending || 0;
        this.cancelled = data.cancelled || 0;
        this.returned = data.returned || 0;
        this.totalIncome = data.totalIncome || 0;
        this.monthlyReservations = data.monthlyReservations || [];
        this.monthlyIncome = data.monthlyIncome || [];
        this.mostRentedItems = data.mostRentedItems || [];

        this.renderCharts();
      } catch (error) {
        console.error(error);
      }
    },

    destroyCharts() {
      Object.keys(this.charts).forEach((key) => {
        if (this.charts[key]) {
          this.charts[key].destroy();
          this.charts[key] = null;
        }
      });
    },

    renderCharts() {
      this.destroyCharts();

      const ctxMonthly = this.$refs.monthlyReservations.getContext("2d");
      this.charts.monthlyReservations = new Chart(ctxMonthly, {
        type: "line",
        data: {
          labels: this.monthlyReservations.map((m) => m.month),
          datasets: [
            {
              label: "Reservations",
              data: this.monthlyReservations.map((m) => m.total),
              borderColor: "#c48a56",
              backgroundColor: "rgba(196,138,86,0.2)",
              fill: true,
              tension: 0.4,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        },
      });

      const ctxMostRented = this.$refs.mostRentedItems.getContext("2d");
      this.charts.mostRentedItems = new Chart(ctxMostRented, {
        type: "bar",
        data: {
          labels: this.mostRentedItems.map((i) => i.name),
          datasets: [
            {
              label: "Times Rented",
              data: this.mostRentedItems.map((i) => i.total),
              backgroundColor: [
                "#56c45c",
                "#f4c27a",
                "#8bc34a",
                "#e57373",
                "#8b5e3c",
              ],
              borderRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        },
      });

      const ctxMonthlyIncome = this.$refs.monthlyIncome.getContext("2d");
      this.charts.monthlyIncome = new Chart(ctxMonthlyIncome, {
        type: "bar",
        data: {
          labels: this.monthlyIncome.map((m) => m.month),
          datasets: [
            {
              label: "Income (₱)",
              data: this.monthlyIncome.map((m) => m.income),
              backgroundColor: "#8b5e3c",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        },
      });

      const ctxStatus = this.$refs.reservationStatus.getContext("2d");
      this.charts.reservationStatus = new Chart(ctxStatus, {
        type: "doughnut",
        data: {
          labels: ["Pending", "Confirmed", "Cancelled", "Returned"],
          datasets: [
            {
              data: [this.pending, this.confirmed, this.cancelled, this.returned],
              backgroundColor: ["#f4c27a", "#8bc34a", "#e57373", "#2196f3"],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
        },
      });
    },

    exportPDF() {
      window.open("/reports/pdf", "_blank");
    },
  },

  mounted() {
    this.fetchReportData();
  },

  beforeUnmount() {
    this.destroyCharts();
  },
};
</script>
<style scoped>

.report-content {
  flex: 1;
   padding: 30px 20px;
  overflow-y: auto;
  margin-left: 190px;
}
.report-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}
.report-header {
    display: flex;
    justify-content: space-between; /* h1 left, button right */
    align-items: center;            /* vertical alignment */
    margin-bottom: 25px;
}

.report-header h1 {
    margin-bottom: 0; /* remove old spacing */
}


h1 {
  margin-bottom: 25px;
  color: #f9f7f5;
}

.export-btn {
    background-color: #fff0e3; /* brown */
    color: #5a3e2b;
    border: none;
    padding: 7px 11px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
    margin-right: 4px;
}

.export-btn:hover {
    background-color: #a66f42;
}

/* SUMMARY CARDS */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 14px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
}

.summary-card h4 {
  font-size: 0.95em;
  color: #5a3e2b;
  margin-bottom: 8px;
}

.summary-number {
  font-size: 1.8em;
  font-weight: bold;
  color: #c48a56;
}

/* REPORT GRID */
.report-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
}

.chart-card {
  background-color: white;
  padding: 45px;
  border-radius: 14px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  height: 320px;
  display: flex;
  flex-direction: column;
}

.chart-card h3 {
  text-align: center;
  margin-bottom: 12px;
  color: #5a3e2b;
  font-size: 1.1em;
}

/* PLACEHOLDER (NO JS YET) */
.chart-placeholder {
  flex: 1;
  border: 2px dashed #d4b08c;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #b08a63;
  font-size: 0.95em;
  background: #fdf9f5;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .report-grid {
    grid-template-columns: 1fr;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }
}
.export-form {
    display: flex;
    justify-content: flex-end; /* RIGHT SIDE */
   
}


</style>
