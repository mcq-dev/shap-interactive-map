import Vue from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import './styles/main.scss'
import ReadMore from 'vue-read-more'
import { t_ } from './utils/i18n'
 
Vue.use(ReadMore);

//create a global event hub
export const bus = new Vue();

Vue.mixin({
    methods: {
      t_
    },

})

new Vue({
  render: h => h(App)
}).$mount('#app')
