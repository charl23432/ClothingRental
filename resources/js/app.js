import '../css/admin.css'
import '../css/user.css'
import '../css/style.css'
import '../css/profile.css'
import '../css/product.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

createApp(App)
  .use(router)
  .mount('#app')

  