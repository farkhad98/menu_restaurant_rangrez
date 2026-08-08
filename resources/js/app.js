import './bootstrap'
import '../css/app.css'

import { createApp } from 'vue'
import { createStore } from 'vuex'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import MenuComponent from './components/MenuComponent.vue'

const supportedLanguages = ['ru', 'en']
const languageFromUrl = location.pathname.split('/')[1]
const currentLanguage = supportedLanguages.includes(languageFromUrl) ? languageFromUrl : 'ru'

const store = createStore({
    state() {
        return {
            app_lang: currentLanguage,
            current_host: location.protocol + '//' + location.host,
            interfaceText: {}
        }
    }
})

const routes = [
    {
        path: '/:lang(ru|en)',
        name: 'Menu',
        component: MenuComponent,
    },
    {
        path: '/:lang(ru|en)/menu',
        redirect: to => ({name: 'Menu', params: {lang: to.params.lang}}),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

const app = createApp(App)

app.use(store)
app.use(router)
app.mount('#app')
