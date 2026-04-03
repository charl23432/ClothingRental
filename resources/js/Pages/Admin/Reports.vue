<template>
  <div class="report-content">
    <div class="report-wrapper">
      <div class="report-header">
        <h1>Report</h1>

        <div class="report-actions">
          <div class="filter-inline">
            <div class="filter-group">
              <label for="from">From</label>
              <input id="from" v-model="from" type="date" />
            </div>

            <div class="filter-group">
              <label for="to">To</label>
              <input id="to" v-model="to" type="date" />
            </div>

            <button type="button" class="filter-btn" @click="applyFilter">
              Filter
            </button>
          </div>

          <form @submit.prevent="reportPdf" class="export-form">
            <button type="submit" class="export-btn">Export PDF</button>
          </form>
        </div>
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
      from: "",
      to: "",
      confirmed: 0,
      pending: 0,
      cancelled: 0,
      returned: 0,
      totalIncome: 0,
      totalInventory: 0,
      rentedCount: 0,
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
        const params = new URLSearchParams();

        if (this.from) params.append("from", this.from);
        if (this.to) params.append("to", this.to);

        const res = await fetch(`/api/reports?${params.toString()}`);

        if (!res.ok) {
          throw new Error("Failed to fetch report data");
        }

        const data = await res.json();

        this.confirmed = data.confirmed || 0;
        this.pending = data.pending || 0;
        this.cancelled = data.cancelled || 0;
        this.returned = data.returned || 0;
        this.totalIncome = data.totalIncome || 0;
        this.totalInventory = data.totalInventory || 0;
        this.rentedCount = data.rentedCount || 0;
        this.monthlyReservations = data.monthlyReservations || [];
        this.monthlyIncome = data.monthlyIncome || [];
        this.mostRentedItems = data.mostRentedItems || [];

        this.renderCharts();
      } catch (error) {
        console.error(error);
      }
    },

    applyFilter() {
      this.fetchReportData();
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
              backgroundColor: ["#56c45c", "#f4c27a", "#8bc34a", "#e57373", "#8b5e3c"],
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

    reportPdf() {
      const params = new URLSearchParams();

      if (this.from) params.append("from", this.from);
      if (this.to) params.append("to", this.to);

      window.open(`/reports/pdf?${params.toString()}`, "_blank");
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
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 25px;
  gap: 20px;
  flex-wrap: wrap;
}

.report-header h1 {
  margin-bottom: 0;
}

h1 {
  margin-bottom: 25px;
  color: #f9f7f5;
}

.report-actions {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  flex-wrap: wrap;
}

.filter-inline {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  color: #f9f7f5;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 4px;
}

.filter-group input {
  border: none;
  border-radius: 8px;
  padding: 7px 10px;
  background: #fff;
  color: #5a3e2b;
  outline: none;
}

.filter-btn {
  background-color: #fff0e3;
  color: #5a3e2b;
  border: none;
  padding: 7px 11px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.filter-btn:hover {
  background-color: #a66f42;
  color: #fff;
}

.export-btn {
  background-color: #fff0e3;
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
  color: #fff;
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

  .report-header {
    align-items: stretch;
  }

  .report-actions {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .filter-inline {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .filter-group input,
  .filter-btn,
  .export-btn {
    width: 100%;
  }
}

.export-form {
  display: flex;
  justify-content: flex-end;
}
</style>