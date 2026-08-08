<template>
    <div>
        <section class="hero-section" style="--hero-image: url('/img/rangrez-bg.webp')">
            <div class="container hero-content">
                <p class="eyebrow">{{text.hero_eyebrow || 'Rangrez Restaurant'}}</p>
                <h1>{{text.hero_title || 'Меню, которое хочется попробовать'}}</h1>
                <p class="hero-description">
                    {{text.hero_text || 'Свежие продукты, яркие вкусы и любимые блюда в одном меню.'}}
                </p>
                <a class="primary-button" href="#menu">{{text.open_menu || 'Смотреть меню'}}</a>
            </div>
        </section>

        <section id="menu" class="menu-section">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">{{text.menu_eyebrow || 'Наше меню'}}</p>
                    <h2>{{text.menu_title || 'Выберите любимое блюдо'}}</h2>
                    <p>{{text.menu_subtitle || 'Фильтруйте блюда по категориям.'}}</p>
                </div>

                <div v-if="!loading && categories.length" class="category-navigation">
                    <button
                        type="button"
                        class="category-scroll-button"
                        :class="{'is-hidden': !categoryListOverflow}"
                        :disabled="!canScrollCategoriesLeft"
                        :aria-label="text.previous_categories || 'Предыдущие категории'"
                        aria-controls="category-filter-list"
                        @click="scrollCategories(-1)"
                    >‹</button>

                    <div
                        id="category-filter-list"
                        ref="categoryList"
                        class="category-list"
                        :aria-label="text.category_filter || 'Категории меню'"
                        @scroll="updateCategoryScrollButtons"
                        @wheel="scrollCategoriesWithWheel"
                    >
                        <button
                            type="button"
                            class="category-button"
                            :class="{active: activeCategory === null}"
                            :aria-pressed="activeCategory === null"
                            @click="selectCategory(null, $event)"
                        >
                            {{text.all_categories || 'Все'}}
                        </button>

                        <button
                            v-for="category in categories"
                            :key="category.id"
                            type="button"
                            class="category-button"
                            :class="{active: activeCategory === category.id}"
                            :aria-pressed="activeCategory === category.id"
                            @click="selectCategory(category.id, $event)"
                        >
                            {{translate(category, 'title')}}
                            <span>{{category.products_count}}</span>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="category-scroll-button"
                        :class="{'is-hidden': !categoryListOverflow}"
                        :disabled="!canScrollCategoriesRight"
                        :aria-label="text.next_categories || 'Следующие категории'"
                        aria-controls="category-filter-list"
                        @click="scrollCategories(1)"
                    >›</button>
                </div>

                <div v-if="loading" class="state-box">
                    <span class="loader"></span>
                    <p>{{text.loading || 'Загружаем меню...'}}</p>
                </div>

                <div v-else-if="hasError" class="state-box error-box">
                    <p>{{text.load_error || 'Не удалось загрузить меню.'}}</p>
                    <button class="secondary-button" type="button" @click="getMenu">
                        {{text.retry || 'Повторить'}}
                    </button>
                </div>

                <div v-else-if="productGroups.length" class="product-groups">
                    <section
                        v-for="group in productGroups"
                        :key="group.category.id"
                        class="product-group"
                        :aria-labelledby="'product-group-title-' + group.category.id"
                    >
                        <div class="product-group-heading">
                            <div>
                                <p>{{text.category_label || 'Категория'}}</p>
                                <h3 :id="'product-group-title-' + group.category.id">
                                    {{translate(group.category, 'title')}}
                                </h3>
                            </div>
                            <span>{{group.products.length}}</span>
                        </div>

                        <div class="product-grid">
                            <article v-for="product in group.products" :key="product.id" class="product-card">
                                <div class="product-image-wrap">
                                    <img
                                        v-if="product.image"
                                        :src="product.image"
                                        :alt="translate(product, 'title')"
                                        class="product-image"
                                        loading="lazy"
                                    >
                                    <div v-else class="product-image-placeholder">
                                        <span>R</span>
                                        <small>{{text.no_image || 'Фото скоро появится'}}</small>
                                    </div>
                                    <span class="product-category">{{translate(product.category, 'title')}}</span>
                                </div>

                                <div class="product-body">
                                    <div class="product-title-row">
                                        <h3>{{translate(product, 'title')}}</h3>
                                        <span class="product-weight">{{formatWeight(product.netto)}}</span>
                                    </div>
                                    <p>{{plainText(translate(product, 'description'))}}</p>
                                    <div class="product-footer">
                                        <strong>{{formatPrice(product.price_uzs)}}</strong>
                                        <button
                                            type="button"
                                            class="product-details-button"
                                            :aria-label="(text.product_details || 'Подробнее') + ': ' + translate(product, 'title')"
                                            @click="openProduct(product)"
                                        >
                                            {{text.product_details || 'Подробнее'}}
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <div v-else class="state-box">
                    <p>{{text.empty || 'В этой категории пока нет блюд.'}}</p>
                </div>
            </div>
        </section>

        <div v-if="selectedProduct" class="product-modal" @click.self="closeProduct">
            <section
                ref="productModal"
                class="product-modal-card"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="'product-modal-title-' + selectedProduct.id"
                tabindex="-1"
            >
                <button
                    type="button"
                    class="product-modal-close"
                    :aria-label="text.close_modal || 'Закрыть'"
                    @click="closeProduct"
                >×</button>

                <div class="product-modal-layout">
                    <div class="product-modal-media">
                        <img
                            v-if="selectedProduct.image"
                            :src="selectedProduct.image"
                            :alt="translate(selectedProduct, 'title')"
                        >
                        <div v-else class="product-image-placeholder">
                            <span>R</span>
                            <small>{{text.no_image || 'Фото скоро появится'}}</small>
                        </div>
                    </div>

                    <div class="product-modal-content">
                        <span class="product-modal-category">
                            {{translate(selectedProduct.category, 'title')}}
                        </span>
                        <h2 :id="'product-modal-title-' + selectedProduct.id">
                            {{translate(selectedProduct, 'title')}}
                        </h2>
                        <p class="product-modal-description">
                            {{plainText(translate(selectedProduct, 'description'))}}
                        </p>

                        <div class="product-modal-meta">
                            <span>
                                {{text.weight_label || 'Вес'}}
                                <strong>{{formatWeight(selectedProduct.netto)}}</strong>
                            </span>
                            <span>
                                {{text.price_label || 'Цена'}}
                                <strong>{{formatPrice(selectedProduct.price_uzs)}}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'MenuComponent',

    data: () => ({
        categories: [],
        products: [],
        activeCategory: null,
        loading: true,
        hasError: false,
        selectedProduct: null,
        previousBodyOverflow: '',
        categoryListOverflow: false,
        canScrollCategoriesLeft: false,
        canScrollCategoriesRight: false,
    }),

    computed: {
        text() {
            return this.$store.state.interfaceText
        },

        productGroups() {
            let visibleCategories = this.activeCategory === null
                ? this.categories
                : this.categories.filter(category => category.id === this.activeCategory)

            return visibleCategories
                .map(category => ({
                    category: category,
                    products: this.products.filter(product => product.category_id === category.id),
                }))
                .filter(group => group.products.length)
        }
    },

    mounted() {
        this.getMenu()
        window.addEventListener('keydown', this.handleKeydown)
        window.addEventListener('resize', this.updateCategoryScrollButtons)
    },

    beforeUnmount() {
        window.removeEventListener('keydown', this.handleKeydown)
        window.removeEventListener('resize', this.updateCategoryScrollButtons)
        document.body.style.overflow = this.previousBodyOverflow
    },

    methods: {
        async getMenu() {
            this.loading = true
            this.hasError = false

            try {
                let responses = await Promise.all([
                    axios.get('/api/categories?limit=100'),
                    axios.get('/api/products?limit=100'),
                ])

                this.categories = responses[0].data.data
                this.products = responses[1].data.data
            } catch (error) {
                this.hasError = true
            } finally {
                this.loading = false

                this.$nextTick(() => {
                    this.updateCategoryScrollButtons()
                })
            }
        },

        selectCategory(categoryId, event) {
            this.activeCategory = categoryId

            event.currentTarget.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center',
            })
        },

        scrollCategories(direction) {
            let categoryList = this.$refs.categoryList

            if (!categoryList) {
                return
            }

            categoryList.scrollBy({
                left: direction * Math.min(categoryList.clientWidth * 0.7, 420),
                behavior: 'smooth',
            })
        },

        scrollCategoriesWithWheel(event) {
            let categoryList = this.$refs.categoryList

            if (!categoryList || !this.categoryListOverflow || Math.abs(event.deltaX) >= Math.abs(event.deltaY)) {
                return
            }

            let maxScrollLeft = categoryList.scrollWidth - categoryList.clientWidth
            let canScrollInDirection = event.deltaY < 0
                ? categoryList.scrollLeft > 0
                : categoryList.scrollLeft < maxScrollLeft

            if (!canScrollInDirection) {
                return
            }

            event.preventDefault()
            categoryList.scrollLeft += event.deltaY
        },

        updateCategoryScrollButtons() {
            let categoryList = this.$refs.categoryList

            if (!categoryList) {
                this.categoryListOverflow = false
                this.canScrollCategoriesLeft = false
                this.canScrollCategoriesRight = false
                return
            }

            let maxScrollLeft = categoryList.scrollWidth - categoryList.clientWidth

            this.categoryListOverflow = maxScrollLeft > 1
            this.canScrollCategoriesLeft = categoryList.scrollLeft > 1
            this.canScrollCategoriesRight = categoryList.scrollLeft < maxScrollLeft - 1
        },

        openProduct(product) {
            this.selectedProduct = product
            this.previousBodyOverflow = document.body.style.overflow
            document.body.style.overflow = 'hidden'

            this.$nextTick(() => {
                this.$refs.productModal.focus()
            })
        },

        closeProduct() {
            this.selectedProduct = null
            document.body.style.overflow = this.previousBodyOverflow
        },

        handleKeydown(event) {
            if (event.key === 'Escape' && this.selectedProduct) {
                this.closeProduct()
            }
        },

        translate(item, field) {
            if (!item) {
                return ''
            }

            let currentField = field + '_' + this.$store.state.app_lang
            let fallbackField = field + '_ru'

            return item[currentField] || item[fallbackField] || ''
        },

        plainText(value) {
            if (!value) {
                return ''
            }

            let valueWithSpaces = String(value)
                .replace(/<br\s*\/?\s*>/gi, ' ')
                .replace(/<\/(p|div|li|h[1-6])>/gi, ' ')
            let documentFromHtml = new DOMParser().parseFromString(valueWithSpaces, 'text/html')

            return (documentFromHtml.body.textContent || '').replace(/\s+/g, ' ').trim()
        },

        formatWeight(weight) {
            let value = String(weight || '').trim()

            if (!value || value === '-') {
                return '-'
            }

            let gramValue = value.match(/^(\d+(?:[.,]\d+)?)\s*(?:г|гр|грам(?:м(?:а|ов)?)?|g)?$/i)

            if (!gramValue) {
                return value
            }

            let unit = this.$store.state.app_lang === 'ru' ? ' грам' : ' g'

            return gramValue[1] + unit
        },

        formatPrice(price) {
            let locale = this.$store.state.app_lang === 'ru' ? 'ru-RU' : 'en-US'
            let currency = this.$store.state.app_lang === 'ru' ? 'сум' : 'UZS'

            return new Intl.NumberFormat(locale, {maximumFractionDigits: 0}).format(Number(price)) + ' ' + currency
        }
    }
}
</script>
