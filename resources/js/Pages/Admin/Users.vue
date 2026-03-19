<template>
  <div class="user-content">
    <h1>User List</h1>

    <div class="userlist-container">
      <div class="search-bar">
        <input
          v-model="search"
          type="text"
          placeholder="Search user..."
        />
      </div>

      <div class="user-wrapper">
        <table class="user-table">
          <thead>
            <tr>
              <th>Profile</th>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>
                <img :src="user.photo_url" class="user-img" />
              </td>

              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>

              <td>
                <span
                  v-if="user.is_online"
                  class="status-active"
                >
                  🟢 Active now
                </span>

                <span
                  v-else-if="user.last_seen_human"
                  class="status-inactive"
                >
                  Active {{ user.last_seen_human }}
                </span>

                <span
                  v-else
                  class="status-offline"
                >
                  ⚪ Offline
                </span>
              </td>

              <td>
                <button
                  class="action-btn view-btn"
                  @click="openModal(user)"
                >
                  View
                </button>
              </td>
            </tr>

            <tr v-if="filteredUsers.length === 0">
              <td colspan="5" style="text-align: center;">No users found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- USER VIEW MODAL -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content" style="max-width:450px;">
        <span class="close-btn" @click="closeModal">×</span>

        <div style="text-align:center;">
          <img
            :src="selectedUser.photo_url"
            style="width:100px;height:100px;border-radius:50%;margin-bottom:10px;"
          />
        </div>

        <p><strong>Name:</strong> {{ selectedUser.name }}</p>
        <p><strong>Email:</strong> {{ selectedUser.email }}</p>
        <p><strong>Number:</strong> {{ selectedUser.number || '—' }}</p>
        <p><strong>Address:</strong> {{ selectedUser.address || '—' }}</p>
        <p><strong>Status:</strong> {{ modalStatus }}</p>

        <div style="margin-top:15px; text-align:center;">
          <button class="action-btn cancel-btn" @click="closeModal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Users',
  data() {
    return {
      users: [],
      search: '',
      showModal: false,
      selectedUser: {}
    }
  },

  computed: {
    filteredUsers() {
      const keyword = this.search.toLowerCase()

      return this.users.filter(user =>
        user.name.toLowerCase().includes(keyword) ||
        user.email.toLowerCase().includes(keyword)
      )
    },

    modalStatus() {
  if (!this.selectedUser || !Object.keys(this.selectedUser).length) return ''

  if (this.selectedUser.is_online) {
    return 'Active now'
  }

  if (this.selectedUser.last_seen_human) {
    return `Active ${this.selectedUser.last_seen_human}`
  }

  return 'Offline'
}
  },

  methods: {
    async fetchUsers() {
      try {
        const response = await axios.get('/api/admin/users')
        this.users = response.data
      } catch (error) {
        console.error('Failed to fetch users:', error)
      }
    },

    openModal(user) {
      this.selectedUser = user
      this.showModal = true
    },

    closeModal() {
      this.showModal = false
      this.selectedUser = {}
    }
  },

  mounted() {
    this.fetchUsers()
  }
}
</script>

<style scoped>
@import '/resources/css/Admin.css';
</style>