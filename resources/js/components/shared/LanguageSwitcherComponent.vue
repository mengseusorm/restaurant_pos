<template>
    <!-- <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE" class="relative dropdown-group border-b border-[#EFF0F6]">
        <button class="dropdown-btn paper-link transition w-full flex items-center gap-3.5 py-3">
            <img :src="language.image" alt="flag" class="w-5 h-5 rounded-full" />
            <span class="text-sm leading-6 capitalize flex-1 text-left">{{ language.name }}</span>
            <i class="fas fa-chevron-down text-xs"></i>
        </button>
        <ul v-if="languages.length > 0" class="p-2 min-w-[320px] rounded-lg shadow-xl absolute top-full left-0 sm:top-0 sm:right-full sm:left-auto sm:mr-2 z-10 border border-gray-200 bg-white hidden dropdown-list">
            <li @click="changeLanguage(lang.id, lang.code)" v-for="lang in languages" :key="lang.id" class="flex items-center gap-2 py-2 px-3 rounded-md cursor-pointer hover:bg-gray-100" :class="{ 'bg-primary/10 text-primary': isCurrentLanguage(lang.id) }">
                <img :src="lang.image" alt="flag" class="w-5 h-5 rounded-full" />
                <span class="text-heading capitalize text-sm flex-1">{{ lang.name }}</span>
                <i v-if="isCurrentLanguage(lang.id)" class="fa-solid fa-check text-primary text-xs"></i>
            </li>
        </ul>
    </div> -->
      <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE && languages.length > 0" class="flex items-center justify-between md:justify-center gap-4">
            <div class="dropdown-group relative">
                <button class="dropdown-btn px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-primary/5 text-primary hover:bg-primary/10 transition-colors duration-200 gap-0.5 min-w-16">
                    <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full" />
                    <span class="whitespace-nowrap text-[10px] font-medium capitalize text-heading mt-3">
                        {{ language.name }}
                    </span>
                </button>
                <ul v-if="languages.length > 0" class="p-2 min-w-[180px] rounded-lg shadow-xl absolute top-[72px] right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                    <li @click="changeLanguage(lang.id, lang.code)" v-for="lang in languages" :key="lang.id" class="flex items-center gap-2 py-1.5 px-2.5 rounded-md cursor-pointer hover:bg-gray-100" :class="{ 'bg-primary/10 text-primary': isCurrentLanguage(lang.id) }">
                        <img :src="lang.image" alt="flag" class="w-4 h-4 rounded-full" />
                        <span class="text-heading capitalize text-sm">{{ lang.name }}</span>
                        <i v-if="isCurrentLanguage(lang.id)" class="fa-solid fa-check text-primary text-xs ml-auto"></i>
                    </li>
                </ul>
            </div>
        </div>
</template>

<script>
import activityEnum from '../../enums/modules/activityEnum';
import i18n from '../../i18n';

export default {
    name: 'LanguageSwitcherComponent',
    data() {
        return {
            enums: {
                activityEnum: activityEnum,
            },
            languageProps: {
                paginate: 0,
                order_column: 'id',
                order_type: 'asc',
            },
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        languages: function () {
            return this.$store.getters['frontendLanguage/lists'];
        },
        language: function () {
            const globalState = this.$store.getters['globalState/get'];
            if (globalState && globalState.language_id && this.languages && this.languages.length > 0) {
                return this.languages.find((lang) => String(lang.id) === String(globalState.language_id)) || this.languages[0];
            }
            if (globalState && globalState.language_code && this.languages && this.languages.length > 0) {
                return this.languages.find((lang) => lang.code === globalState.language_code) || this.languages[0];
            }
            return this.languages && this.languages.length > 0 ? this.languages[0] : this.$store.getters['frontendLanguage/show'] || {};
        },
    },
    mounted() {
        Promise.all([this.loadSettings(), this.loadLanguages()]).then(() => {
            this.loadCurrentLanguage();
        });
    },
    methods: {
        changeLanguage: function (id, code) {
            this.setCurrentLocale(code);

            this.$store
                .dispatch('globalState/set', { language_id: id, language_code: code })
                .then((res) => {
                    this.loadCurrentLanguage(id);
                    document.querySelectorAll('.dropdown-list').forEach((dropdown) => {
                        dropdown.classList.add('hidden');
                        dropdown.classList.remove('active');
                    });
                })
                .catch((err) => {
                    console.error('Error changing language:', err);
                });
        },
        isCurrentLanguage: function (languageId) {
            const globalState = this.$store.getters['globalState/get'];
            return globalState && String(globalState.language_id) === String(languageId);
        },
        loadSettings: function () {
            if (this.setting && Object.keys(this.setting).length > 0) {
                this.initDefaultLanguage(this.setting);
                return Promise.resolve();
            }

            return this.$store
                .dispatch('frontendSetting/lists')
                .then((res) => {
                    this.initDefaultLanguage(res.data.data);
                    return res;
                })
                .catch((err) => {
                    console.error('Error loading settings:', err);
                });
        },
        loadLanguages: function () {
            if (this.languages && this.languages.length > 0) {
                return Promise.resolve();
            }

            return this.$store.dispatch('frontendLanguage/lists', this.languageProps).catch((err) => {
                console.error('Error loading languages:', err);
            });
        },
        setCurrentLocale: function (code) {
            if (!code) {
                return;
            }

            if (i18n.global.locale && typeof i18n.global.locale === 'object' && Object.prototype.hasOwnProperty.call(i18n.global.locale, 'value')) {
                i18n.global.locale.value = code;
            } else {
                i18n.global.locale = code;
            }

            if (this.$i18n) {
                if (this.$i18n.locale && typeof this.$i18n.locale === 'object' && Object.prototype.hasOwnProperty.call(this.$i18n.locale, 'value')) {
                    this.$i18n.locale.value = code;
                } else {
                    this.$i18n.locale = code;
                }
            }

            localStorage.setItem('language_code', code);
            localStorage.setItem('locale', code);
        },
        initDefaultLanguage: function (setting) {
            const globalState = this.$store.getters['globalState/get'];
            if (!globalState.language_id && setting && setting.site_default_language) {
                this.$store.dispatch('globalState/init', {
                    language_id: setting.site_default_language,
                });
            }
        },
        syncLanguageState: function (language) {
            if (!language || !language.id || !language.code) {
                return;
            }

            this.$store.commit('frontendLanguage/show', language);
            this.$store.dispatch('globalState/set', {
                language_id: language.id,
                language_code: language.code,
            });
            this.setCurrentLocale(language.code);
        },
        loadCurrentLanguage(languageId) {
            const globalState = this.$store.getters['globalState/get'];
            const currentLangId = languageId || (globalState && globalState.language_id);
            if (!currentLangId) return;

            const existingLanguage = this.$store.getters['frontendLanguage/get'](currentLangId);
            if (existingLanguage && existingLanguage.code) {
                this.syncLanguageState(existingLanguage);
                return;
            }
            this.$store
                .dispatch('frontendLanguage/show', currentLangId)
                .then((res) => {
                    this.syncLanguageState(res.data.data);
                })
                .catch((err) => {
                    console.error('Error loading current language:', err);
                });
        },
    },
};
</script>

<style scoped>
.dropdown-btn {
    cursor: pointer;
}

.dropdown-list {
    max-height: 400px;
    overflow-y: auto;
}
</style>
