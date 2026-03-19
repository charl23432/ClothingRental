import { createRouter, createWebHistory } from 'vue-router'

import Login from './Pages/Auth/Login.vue'
import Register from './Pages/Auth/Register.vue'
import ForgotPassword from './Pages/Auth/ForgotPassword.vue'

import AdminLayout from './Layouts/AdminLayout.vue'
import UserLayout from './layouts/UserLayout.vue'

import Dashboard from './Pages/Admin/Dashboard.vue'
import InventoryMen from './Pages/Admin/Inventory/InventoryMen.vue'
import InventoryMenPS from './Pages/Admin/Inventory/InventoryMenPS.vue'
import AddItemPage from './Pages/Admin/Inventory/AddItemPage.vue'
import EditItemPage from './Pages/Admin/Inventory/EditItemPage.vue'
import InventoryWomen from './Pages/Admin/Inventory/InventoryWomen.vue'
import InventoryWomenPS from './Pages/Admin/Inventory/InventoryWomenPS.vue'
import Reservations from './Pages/Admin/Reservations.vue'
import Reports from './Pages/Admin/Reports.vue'
import Settings from './Pages/Admin/Settings.vue'
import Users from './Pages/Admin/Users.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },

  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword
  },

  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', name: 'Dashboard', component: Dashboard },
      { path: 'inventory-men', name: 'InventoryMen', component: InventoryMen },
      { path: 'inventory-men-ps', name: 'InventoryMenPS', component: InventoryMenPS },
      { path: 'inventory-women', name: 'InventoryWomen', component: InventoryWomen },
      { path: 'inventory-women-ps', name: 'InventoryWomenPS', component: InventoryWomenPS },
      { path: 'inventory/add/:category', name: 'AddItemPage', component: AddItemPage, props: true },
      { path: 'inventory/edit/:id', name: 'EditItemPage', component: EditItemPage, props: true },
      { path: 'reservations', name: 'Reservations', component: Reservations },
      { path: 'reports', name: 'Reports', component: Reports },
      { path: 'settings', name: 'Settings', component: Settings },
      { path: 'users', name: 'Users', component: Users }
    ]
  },

  {
    path: '/',
    component: UserLayout,
    children: [
      { path: 'men', name: 'MenDashboard', component: () => import('./Pages/User/Men/MenDashboard.vue') },
      { path: 'men/tuxedo', name: 'MenTuxedo', component: () => import('./Pages/User/Men/MenTuxedo.vue') },
      { path: 'men/prom', name: 'MenProm', component: () => import('./Pages/User/Men/MenProm.vue') },

      { path: 'women', name: 'WomenDashboard', component: () => import('./Pages/User/Women/WomenDashboard.vue') },
      { path: 'women/wedding', name: 'WomenWedding', component: () => import('./Pages/User/Women/WomenWedding.vue') },
      { path: 'women/prom', name: 'WomenProm', component: () => import('./Pages/User/Women/WomenProm.vue') },

      { path: 'products/:id', name: 'ProductDetails', component: () => import('./Pages/User/Product/ProductDetails.vue') },
      { path: 'checkout/:id', name: 'Checkout', component: () => import('./Pages/User/Product/Checkout.vue') },

      { path: 'about', name: 'About', component: () => import('./Pages/User/About.vue') },
      { path: 'howto', name: 'HowTo', component: () => import('./Pages/User/HowTo.vue') },
      { path: 'faq', name: 'Faq', component: () => import('./Pages/User/Faq.vue') },
      { path: 'profile', name: 'Profile', component: () => import('./Pages/User/Profile.vue') }
    ]
  },

  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  }
]

export default createRouter({
  history: createWebHistory(),
  routes
})