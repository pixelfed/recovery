import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router'
import App from './components/App.vue';
const app = createApp(App);
import HomePage from './components/Home.vue';
import ForgotInstance from './components/ForgotInstance.vue';
import NotFound from './components/NotFound.vue';
const routes = [
  { path: '/', component: HomePage },
  { path: '/forgot-instance', component: ForgotInstance },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound },
];
const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
        return {
            el: `#${to.hash.slice(1)}`
        };
    } else {
        return { top: 0 };
    }
  }
});
app.use(router);
app.mount('#app');
