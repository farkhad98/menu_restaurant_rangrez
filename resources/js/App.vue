<template>
    <div class="site-shell">
        <header class="site-header">
            <div class="container header-inner">
                <router-link class="brand" :to="{name: 'Menu', params: {lang: $store.state.app_lang}}">
                    <span class="brand-mark">R</span>
                    <span>Rangrez</span>
                </router-link>

                <button
                    class="menu-toggle"
                    type="button"
                    :aria-label="menuIsOpen
                        ? (interfaceText.close_navigation || interfaceText.nav_menu || 'Menu')
                        : (interfaceText.open_navigation || interfaceText.nav_menu || 'Menu')"
                    :aria-expanded="menuIsOpen"
                    @click="menuIsOpen = !menuIsOpen"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="header-actions" :class="{'is-open': menuIsOpen}">
                    <router-link
                        class="header-link"
                        :to="{name: 'Menu', params: {lang: $store.state.app_lang}}"
                        @click="menuIsOpen = false"
                    >
                        {{interfaceText.nav_menu || 'Меню'}}
                    </router-link>

                    <div class="language-switcher" aria-label="Language">
                        <button
                            type="button"
                            :class="{active: $store.state.app_lang === 'ru'}"
                            @click="changeLanguage('ru')"
                        >RU</button>
                        <button
                            type="button"
                            :class="{active: $store.state.app_lang === 'en'}"
                            @click="changeLanguage('en')"
                        >EN</button>
                    </div>

                </div>
            </div>
        </header>

        <main>
            <router-view></router-view>
        </main>

        <footer class="site-footer">
            <div class="container footer-inner">
                <span>© 2022 Rangrez Restaurant</span>
                <span>{{interfaceText.footer_text || 'Готовим с любовью каждый день'}}</span>
            </div>
        </footer>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    data: () => ({
        menuIsOpen: false,
        currentYear: new Date().getFullYear(),
    }),

    computed: {
        interfaceText() {
            return this.$store.state.interfaceText
        }
    },

    watch: {
        '$route.params.lang': {
            immediate: true,
            handler(lang) {
                if (['ru', 'en'].includes(lang)) {
                    this.$store.state.app_lang = lang
                    this.getLocale()
                    document.documentElement.lang = lang
                }
            }
        }
    },

    methods: {
        changeLanguage(lang) {
            this.menuIsOpen = false
            this.$store.state.app_lang = lang
            this.$router.push({name: 'Menu', params: {lang: lang}})
        },

        async getLocale() {
            try {
                let response = await axios.get('/locales/' + this.$store.state.app_lang + '.json')
                this.$store.state.interfaceText = response.data
            } catch (error) {
                this.$store.state.interfaceText = {}
            }
        }
    }
}
</script>
