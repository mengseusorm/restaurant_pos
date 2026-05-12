<template>
    <header class="therapist-navbar bg-white border-b border-gray-200">
        <div class="flex items-center justify-between px-4 py-3 sm:px-6">
            <!-- Left: Logo/Branding -->
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold text-gray-900">{{ $t('label.therapist_app') || 'Therapist App' }}</h2>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-3">
                <!-- Language Switcher -->
                <LanguageSwitcherComponent />

                <!-- User Menu -->
                <div class="relative dropdown-group">
                    <button class="dropdown-btn flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <img v-if="authInfo.image" class="w-8 h-8 rounded-full object-cover" :src="authInfo.image" alt="avatar" />
                        <div v-else class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-semibold text-white">
                            {{ userInitials }}
                        </div>
                        <span class="hidden sm:inline text-sm font-medium text-gray-700">{{ textShortener(authInfo.name, 20) }}</span>
                        <i class="fas fa-chevron-down text-xs text-gray-600"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div class="dropdown-list absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900">{{ authInfo.name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ authInfo.email }}</p>
                        </div>
                        <nav class="py-2">
                            <button @click="logout" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors">
                                <i class="lab lab-logout-line text-base"></i>
                                <span>{{ $t('button.logout') || 'Logout' }}</span>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import LanguageSwitcherComponent from '../shared/LanguageSwitcherComponent.vue';

export default {
    name: 'TherapistNavbarComponent',
    components: {
        LanguageSwitcherComponent,
    },
    computed: {
        authInfo: function () {
            return this.$store.getters.authInfo || { name: 'Guest', email: '', image: '' };
        },
        userInitials: function () {
            const name = this.authInfo.name || 'T';
            return String(name)
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part[0])
                .join('')
                .toUpperCase();
        },
    },
    methods: {
        textShortener: function (text, number = 30) {
            if (!text) return '';
            return text.length > number ? text.substring(0, number) + '...' : text;
        },
        logout: function () {
            this.$store
                .dispatch('logout')
                .then(() => {
                    this.$router.push({ name: 'auth.login' });
                })
                .catch((err) => {
                    console.error('Logout error:', err);
                });
        },
    },
};
</script>

<style scoped>
.therapist-navbar {
    position: sticky;
    top: 0;
    z-index: 40;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.dropdown-btn {
    cursor: pointer;
    transition: all 0.2s ease;
}

.dropdown-btn:hover {
    background-color: #f3f4f6;
}

.dropdown-list {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 640px) {
    .therapist-navbar {
        padding: 0.5rem;
    }
}
</style>
