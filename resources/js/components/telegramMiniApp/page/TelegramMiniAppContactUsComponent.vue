<template>
    <div class="contact-us-page">
        <TelegramMiniAppLoadingComponent :props="loading" />
        
        <section class="py-8">
            <div class="container max-w-2xl">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-heading mb-2">{{ $t('label.contact_us') }}</h1>
                    <p class="text-paragraph">{{ $t('message.get_in_touch') }}</p>
                </div>

                <!-- Restaurant Info Card -->
                <div class="bg-white rounded-2xl shadow-sm border p-6 mb-6">
                    <div class="flex items-center gap-4 mb-6">
                        <img v-if="branch.logo" :src="branch.logo" :alt="branch.name" 
                             class="w-16 h-16 rounded-2xl">
                        <div>
                            <h2 class="text-xl font-semibold text-heading">{{ branch.name }}</h2>
                            <p v-if="branch.description" class="text-sm text-paragraph">{{ branch.description }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div v-if="branch.address" class="flex items-start gap-3">
                            <i class="lab lab-location-pin lab-font-size-20 text-primary mt-1"></i>
                            <div>
                                <h3 class="font-medium text-heading">{{ $t('label.address') }}</h3>
                                <p class="text-paragraph">{{ branch.address }}</p>
                            </div>
                        </div>

                        <div v-if="branch.phone" class="flex items-center gap-3">
                            <i class="lab lab-phone lab-font-size-20 text-primary"></i>
                            <div>
                                <h3 class="font-medium text-heading">{{ $t('label.phone') }}</h3>
                                <a :href="`tel:${branch.phone}`" class="text-primary hover:underline">{{ branch.phone }}</a>
                            </div>
                        </div>

                        <div v-if="branch.email" class="flex items-center gap-3">
                            <i class="lab lab-envelope lab-font-size-20 text-primary"></i>
                            <div>
                                <h3 class="font-medium text-heading">{{ $t('label.email') }}</h3>
                                <a :href="`mailto:${branch.email}`" class="text-primary hover:underline">{{ branch.email }}</a>
                            </div>
                        </div>

                        <div v-if="branch.opening_hours" class="flex items-start gap-3">
                            <i class="lab lab-clock lab-font-size-20 text-primary mt-1"></i>
                            <div>
                                <h3 class="font-medium text-heading">{{ $t('label.opening_hours') }}</h3>
                                <p class="text-paragraph">{{ branch.opening_hours }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Telegram Contact -->
                <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <i class="lab lab-telegram lab-font-size-24 text-blue-500"></i>
                        <h3 class="font-semibold text-heading">{{ $t('label.telegram_support') }}</h3>
                    </div>
                    <p class="text-paragraph mb-4">{{ $t('message.telegram_support_desc') }}</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button v-if="telegramUser" @click="startChat" 
                                class="bg-blue-500 text-white py-3 px-4 rounded-xl font-medium hover:bg-blue-600 transition">
                            <i class="lab lab-chat lab-font-size-16 mr-2"></i>
                            {{ $t('button.start_chat') }}
                        </button>
                        <button @click="shareContact" 
                                class="bg-blue-100 text-blue-600 py-3 px-4 rounded-xl font-medium hover:bg-blue-200 transition">
                            <i class="lab lab-share lab-font-size-16 mr-2"></i>
                            {{ $t('button.share_contact') }}
                        </button>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <router-link :to="{ name: 'telegram.mini.app.menu', params: { slug: this.$route.params.slug } }"
                        class="bg-white rounded-2xl p-6 shadow-sm border hover:shadow-md transition-shadow text-center">
                        <i class="lab lab-restaurant lab-font-size-32 text-primary mb-3"></i>
                        <h3 class="font-semibold text-heading mb-2">{{ $t('label.view_menu') }}</h3>
                        <p class="text-sm text-paragraph">{{ $t('message.browse_our_menu') }}</p>
                    </router-link>
                    
                    <div class="bg-white rounded-2xl p-6 shadow-sm border text-center">
                        <i class="lab lab-headset lab-font-size-32 text-green-500 mb-3"></i>
                        <h3 class="font-semibold text-heading mb-2">{{ $t('label.customer_service') }}</h3>
                        <p class="text-sm text-paragraph">{{ $t('message.we_are_here_to_help') }}</p>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h3 class="font-semibold text-heading mb-4">{{ $t('label.send_message') }}</h3>
                    <form @submit.prevent="sendMessage" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.name') }}</label>
                            <input v-model="contactForm.name" type="text" 
                                   class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                   :placeholder="$t('placeholder.your_name')" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.email') }}</label>
                            <input v-model="contactForm.email" type="email" 
                                   class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                   :placeholder="$t('placeholder.your_email')" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.subject') }}</label>
                            <input v-model="contactForm.subject" type="text" 
                                   class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                   :placeholder="$t('placeholder.message_subject')" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.message') }}</label>
                            <textarea v-model="contactForm.message" rows="4" 
                                      class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                      :placeholder="$t('placeholder.your_message')" required></textarea>
                        </div>
                        
                        <button type="submit" :disabled="loading.form" 
                                class="w-full bg-primary text-white py-3 rounded-xl font-medium hover:bg-primary-dark transition disabled:opacity-50">
                            <span v-if="!loading.form">{{ $t('button.send_message') }}</span>
                            <span v-else>{{ $t('button.sending') }}...</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import TelegramMiniAppLoadingComponent from "../components/TelegramMiniAppLoadingComponent.vue";
import alertService from "../../../services/alertService";

export default {
    name: "TelegramMiniAppContactUsComponent",
    components: {
        TelegramMiniAppLoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
                form: false
            },
            telegramUser: null,
            contactForm: {
                name: '',
                email: '',
                subject: '',
                message: ''
            }
        }
    },
    computed: {
        branch: function () {
            return this.$store.getters['frontendBranch/show'];
        }
    },
    mounted() {
        this.initializeTelegramWebApp();
        this.loadData();
    },
    methods: {
        initializeTelegramWebApp() {
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                
                // Get user data and pre-fill form
                const user = tg.initDataUnsafe?.user;
                if (user) {
                    this.telegramUser = user;
                    this.contactForm.name = `${user.first_name} ${user.last_name || ''}`.trim();
                }
                
                // Show back button
                tg.BackButton.show();
                tg.BackButton.onClick(() => {
                    this.$router.go(-1);
                });
            }
        },
        loadData() {
            this.loading.isActive = true;
            this.$store.dispatch('frontendBranch/show', this.$route.params.slug).then(() => {
                this.loading.isActive = false;
            }).catch(() => {
                this.loading.isActive = false;
            });
        },
        startChat() {
            if (window.Telegram && window.Telegram.WebApp) {
                // This would typically open a chat with the restaurant's support
                window.Telegram.WebApp.showAlert(this.$t('message.chat_feature_coming_soon'));
            }
        },
        shareContact() {
            if (window.Telegram && window.Telegram.WebApp) {
                const contactInfo = `📍 ${this.branch.name}\n📞 ${this.branch.phone}\n📧 ${this.branch.email}\n🏠 ${this.branch.address}`;
                
                // Share contact info
                if (navigator.share) {
                    navigator.share({
                        title: this.branch.name,
                        text: contactInfo
                    });
                } else {
                    // Fallback: copy to clipboard
                    navigator.clipboard.writeText(contactInfo).then(() => {
                        alertService.success(this.$t('message.contact_copied'));
                    });
                }
            }
        },
        sendMessage() {
            this.loading.form = true;
            
            // Add Telegram user info if available
            const messageData = {
                ...this.contactForm,
                telegram_user_id: this.telegramUser?.id,
                telegram_username: this.telegramUser?.username,
                source: 'telegram_mini_app'
            };
            
            // Simulate API call with optimized timing
            requestAnimationFrame(() => {
                this.loading.form = false;
                alertService.success(this.$t('message.message_sent_successfully'));
                
                // Reset form
                this.contactForm = {
                    name: this.telegramUser ? `${this.telegramUser.first_name} ${this.telegramUser.last_name || ''}`.trim() : '',
                    email: '',
                    subject: '',
                    message: ''
                };
                
                // Haptic feedback with error handling
                if (window.Telegram && window.Telegram.WebApp) {
                    try {
                        if (window.Telegram.WebApp.HapticFeedback && typeof window.Telegram.WebApp.HapticFeedback.notificationOccurred === 'function') {
                            window.Telegram.WebApp.HapticFeedback.notificationOccurred('success');
                        }
                    } catch (error) {
                        console.warn('Haptic feedback not available:', error);
                    }
                }
            });
        }
    }
}
</script>