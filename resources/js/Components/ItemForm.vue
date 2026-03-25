<template>
  <main class="main-content">
    <h2 style="cursor: pointer" @click="$router.back()">
      <i class="fa-solid fa-angle-left"></i>
      {{ isEdit ? "Edit Item" : "Add New Item" }}
    </h2>

    <div class="item-form">
      <!-- IMAGE UPLOAD -->
      <div class="image-upload">
        <img id="preview-image" :src="previewImage" alt="Preview" />

        <input
          ref="upload"
          type="file"
          id="upload"
          accept="image/*"
          style="display: none"
          @change="onImageChange"
        />

        <button id="upload-btn" type="button" @click="$refs.upload.click()">
          Upload Photo
        </button>
      </div>

      <!-- ITEM DETAILS -->
      <div class="item-details">
        <label>Item Name:</label>
        <input v-model="item.item_name" type="text" required />

        <label>Available:</label>
        <input v-model="item.quantity" type="number" required />

        <label>Rental Fee:</label>
        <input v-model="item.rental_fee" type="text" required />
      </div>
    </div>

    <!-- SIZES SECTION -->
    <div class="sizes-section">
      <h3>AVAILABLE SIZE (inches)</h3>
      <button id="add-size-btn" type="button" @click="openSizeModal">
        Add Size
      </button>

      <table id="size-table">
        <thead>
          <tr>
            <th>Size</th>
            <th>Chest</th>
            <th>Waist</th>
            <th>Hip</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(size, index) in item.sizes" :key="index">
            <td>{{ size.size }}</td>
            <td>{{ size.chest }}</td>
            <td>{{ size.waist }}</td>
            <td>{{ size.hip }}</td>
            <td>
              <button type="button" id="edit-size-btn"  @click="editSize(index)">Edit</button>
              <button type="button" id="delete-size-btn" @click="deleteSize(index)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <button
      type="button"
      id="add-item-btn"
      style="display: block"
      @click="submitForm"
    >
      {{ isEdit ? "Save Changes" : "Add This Item" }}
    </button>

    <!-- SIZE MODAL -->
    <div v-if="showSizeModal" id="size-modal" class="modal">
      <div class="modal-content">
        <input v-model="sizeTemp.size" placeholder="Size" />
        <input v-model="sizeTemp.chest" placeholder="Chest" />
        <input v-model="sizeTemp.waist" placeholder="Waist" />
        <input v-model="sizeTemp.hip" placeholder="Hip" />

        <button type="button" id="confirm-size-btn" @click="confirmSize">
          Confirm
        </button>
      </div>
    </div>
  </main>
</template>

<script>
export default {
  props: {
    itemData: {
      type: Object,
      default: null,
    },
    category: {
      type: String,
      default: "men_tuxedo",
    },
  },

  data() {
    return {
      item: {
        item_name: "",
        quantity: "",
        rental_fee: "",
        category: this.category,
        sizes: [],
        image: null,
        id: null,
      },
      previewImage: "/images/hfhmn.jpg",
      showSizeModal: false,
      sizeTemp: {
        size: "",
        chest: "",
        waist: "",
        hip: "",
      },
      editIndex: undefined,
    };
  },

  computed: {
    isEdit() {
      return !!this.itemData && Object.keys(this.itemData).length > 0;
    },

    redirectUrl() {
      const category = this.item.category || this.category;

      const routes = {
        men_tuxedo: "/admin/inventory-men",
        men_prom: "/admin/inventory-men-ps",
        women_wedding: "/admin/inventory-women",
        women_prom: "/admin/inventory-women-ps",
      };

      return routes[category] || "/admin/inventory-men";
    },
  },

  watch: {
    itemData: {
      immediate: true,
      handler(newValue) {
        if (newValue && Object.keys(newValue).length > 0) {
          this.item = {
            id: newValue.id || null,
            item_name: newValue.item_name || "",
            quantity: newValue.quantity || "",
            rental_fee: newValue.rental_fee || "",
            category: newValue.category || this.category,
            sizes: Array.isArray(newValue.sizes)
              ? newValue.sizes
              : this.parseSizes(newValue.sizes),
            image: newValue.image || null,
          };

          this.previewImage = newValue.image
            ? `/storage/${newValue.image}`
            : "/images/hfhmn.jpg";
        }
      },
    },
  },

  methods: {
    parseSizes(sizes) {
      if (!sizes) return [];
      if (Array.isArray(sizes)) return sizes;

      try {
        return JSON.parse(sizes);
      } catch {
        return [];
      }
    },

    onImageChange(event) {
      const file = event.target.files[0];
      if (!file) return;

      this.item.image = file;
      this.previewImage = URL.createObjectURL(file);
    },

    openSizeModal() {
      this.showSizeModal = true;
      this.editIndex = undefined;
      this.sizeTemp = {
        size: "",
        chest: "",
        waist: "",
        hip: "",
      };
    },

    editSize(index) {
      this.editIndex = index;
      this.sizeTemp = { ...this.item.sizes[index] };
      this.showSizeModal = true;
    },

    deleteSize(index) {
      this.item.sizes.splice(index, 1);
    },

    confirmSize() {
      if (this.editIndex !== undefined) {
        this.item.sizes[this.editIndex] = { ...this.sizeTemp };
      } else {
        this.item.sizes.push({ ...this.sizeTemp });
      }

      this.closeSizeModal();
    },

    closeSizeModal() {
      this.showSizeModal = false;
      this.editIndex = undefined;
      this.sizeTemp = {
        size: "",
        chest: "",
        waist: "",
        hip: "",
      };
    },

    async submitForm() {
      try {
        const formData = new FormData();
        formData.append("item_name", this.item.item_name);
        formData.append("quantity", this.item.quantity);
        formData.append("rental_fee", this.item.rental_fee);
        formData.append("category", this.item.category);
        formData.append("sizes", JSON.stringify(this.item.sizes));

        if (this.item.image instanceof File) {
          formData.append("image", this.item.image);
        }

        if (this.isEdit) {
          formData.append("_method", "PUT");
        }

        const url = this.isEdit
          ? `/api/inventory/${this.item.id}`
          : `/api/inventory`;

        const res = await fetch(url, {
          method: "POST",
          body: formData,
          credentials: "same-origin",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
        });

        const text = await res.text();
        console.log("ITEM SAVE RESPONSE:", text);

        if (!res.ok) {
          throw new Error(text || "Failed to save item");
        }

        this.$router.push(this.redirectUrl);
      } catch (error) {
        console.error("Submit failed:", error);
        alert("Failed to save item. Check console/network.");
      }
    },
  },
};
</script>

<style scoped>
.main-content {
  flex: 1;
  padding: 20px;
  margin-top: 20px;
  margin-left: 200px;
}

.main-content h2 {
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #fff;
}

h2 i {
  transition: color 0.2s, transform 0.2s;
}

h2:hover i {
  color: #333;
  transform: translateX(-3px);
}

.item-form {
  display: flex;
  gap: 40px;
  margin: 20px 0;
}

.image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  gap: 10px;
}

.image-upload img {
  width: 150px;
  height: 160px;
  object-fit: cover;
  border-radius: 10px;
  border: 2px solid #fff;
  background: #f0f0f0;
}

.image-upload button {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}

.image-upload button:hover {
  background: #0056b3;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.item-details label {
  display: block;
  font-weight: bold;
  margin-top: 10px;
}

.item-details input {
  width: 250px;
  padding: 5px;
  border: none;
  border-radius: 5px;
  background: #f5f5f5;
}

.sizes-section {
  margin-top: 30px;
}

#add-size-btn {
  background: lime;
  border: none;
  border-radius: 5px;
  padding: 5px 10px;
  cursor: pointer;
}

#size-table {
  width: 98%;
  margin-top: 10px;
  border-collapse: collapse;
  color: #000000;
}

#size-table th,
#size-table td {
  padding: 5px 34px;
  border: 1px solid #0d0d0d;
  text-align: center;
  background-color: #e8e7e7;
}
#edit-size-btn{
  background: #007bff;
  color: white;
  border: none;
  padding: 5px 15px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s; 
}
#edit-size-btn:hover {
  background: #647b93;
}
#delete-size-btn {
  background: #dc3545;
  color: white;
  border: none;
  padding: 5px 15px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s; 
}
#delete-size-btn:hover {
  background: #a71d2a;
}

#add-item-btn {
  background: lime;
  color: black;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  position: fixed;
  bottom: 30px;
  right: 40px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

#add-item-btn:hover {
  background: #00cc00;
  transform: scale(1.05);
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #a78661;
  padding: 20px;
  border-radius: 15px;
  width: 350px;
  height: 250px;
  color: white;
}

.modal-content button {
  background: lime;
  color: black;
  border: none;
  border-radius: 10px;
  margin-top: 15px;
  padding: 5px 10px;
  cursor: pointer;
}
</style>