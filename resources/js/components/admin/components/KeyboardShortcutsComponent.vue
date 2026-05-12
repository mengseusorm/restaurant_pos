<template>
    <div class="mb-4">
        <!-- Number Shortcuts Row (hidden when keyboard is shown) -->
        <div v-if="!showKeyboard" class="flex flex-wrap gap-2 justify-center mb-2">
            <button
                v-for="number in 10"
                :key="number - 1"
                @click="appendCharacter((number - 1).toString())"
                class="keyboard-btn w-10 h-10 rounded-lg border border-gray-300 bg-white hover:bg-blue-50 hover:border-blue-300 transition-colors flex items-center justify-center text-sm font-medium text-gray-700 hover:text-blue-600"
            >
                {{ number - 1 }}
            </button>

            <button
                @click="backspace"
                class="keyboard-btn h-10 px-3 rounded-lg border border-gray-300 bg-white hover:bg-blue-50 hover:border-blue-300 transition-colors flex items-center justify-center text-sm font-medium text-gray-700 hover:text-blue-600"
            >
                <Backspace :size="16" class="me-1" />
                {{ $t('button.delete') }}
            </button>
            
            <button
                @click="clearInput"
                class="keyboard-btn h-10 px-3 rounded-lg border border-gray-300 bg-white hover:bg-blue-50 hover:border-blue-300 transition-colors flex items-center justify-center text-sm font-medium text-gray-700 hover:text-blue-600"
            >
                <Close :size="16" class="me-1" />
                {{ $t('button.clear') }}
            </button>
            <button
                @click="toggleKeyboard"
                class="keyboard-btn h-10 px-3 rounded-lg border transition-colors flex items-center justify-center text-sm font-medium"
                :class="showKeyboard ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
            >
                <Keyboard class="text-sm" />
                <span class="ml-1">{{ showKeyboard ? $t('button.hide_keyboard') : 'ABC' }}</span>
            </button>
        </div>

        <!-- Full Keyboard (shown when toggled) -->
        <div v-if="showKeyboard" class="keyboard-container bg-gray-50 rounded-lg p-3 border border-gray-200">
            <!-- Close button -->
            <div class="flex justify-end mb-2">
                <button
                    @click="toggleKeyboard"
                    class="px-3 py-1 rounded-lg border border-blue-500 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors flex items-center gap-1 text-sm font-medium"
                >
                    <i class="lab lab-close text-sm"></i>
                    <span>{{ $t('button.hide_keyboard') || 'Hide' }}</span>
                </button>
            </div>
            
            <!-- Row 1: Numbers and symbols -->
            <div class="flex flex-wrap gap-1 justify-center mb-1">
                <button
                    v-for="char in ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '=']"
                    :key="'num-' + char"
                    @click="appendCharacter(char)"
                    class="keyboard-key"
                >
                    {{ char }}
                </button>
                <button
                    @click="backspace"
                    class="keyboard-key w-32 bg-orange-50 border-orange-300 text-orange-700 hover:bg-orange-100"
                >
                    <i class="lab lab-back-bold text-sm"></i>
                </button>
            </div>

            <!-- Row 2: QWERTY -->
            <div class="flex flex-wrap gap-1 justify-center mb-1">
                <button
                    v-for="char in ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', '[', ']']"
                    :key="'q-' + char"
                    @click="appendCharacter(char)"
                    class="keyboard-key"
                >
                    {{ char }}
                </button>
            </div>

            <!-- Row 3: ASDF -->
            <div class="flex flex-wrap gap-1 justify-center mb-1">
                <button
                    v-for="char in ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', ';', '\'']"
                    :key="'a-' + char"
                    @click="appendCharacter(char)"
                    class="keyboard-key"
                >
                    {{ char }}
                </button>
            </div>

            <!-- Row 4: ZXCV -->
            <div class="flex flex-wrap gap-1 justify-center mb-1">
                <button
                    @click="toggleShift"
                    class="keyboard-key w-16"
                    :class="isShift ? 'bg-blue-100 border-blue-400' : ''"
                >
                    <i class="lab lab-shift text-sm"></i>
                </button>
                <button
                    v-for="char in ['Z', 'X', 'C', 'V', 'B', 'N', 'M', ',', '.', '/']"
                    :key="'z-' + char"
                    @click="appendCharacter(char)"
                    class="keyboard-key"
                >
                    {{ char }}
                </button>
            </div>

            <!-- Row 5: Space bar and controls -->
            <div class="flex flex-wrap gap-1 justify-center">
                <button
                    @click="appendCharacter(' ')"
                    class="keyboard-key flex-1 min-w-[200px]"
                >
                    {{ $t('label.space') || 'Space' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import Keyboard from 'vue-material-design-icons/Keyboard.vue';
import Backspace from 'vue-material-design-icons/Backspace.vue';
import Close from 'vue-material-design-icons/Close.vue';
export default {
    name: 'KeyboardShortcutsComponent',
    props: {
        modelValue: {
            type: [String, Number],
            default: ''
        },
        inputId: {
            type: String,
            default: null
        }
    },
    emits: ['update:modelValue'],
    components: {
        Keyboard,
        Backspace,
        Close
    },
    data() {
        return {
            showKeyboard: false,
            isShift: false
        }
    },
    methods: {
        toggleKeyboard() {
            this.showKeyboard = !this.showKeyboard;
        },
        toggleShift() {
            this.isShift = !this.isShift;
        },
        appendCharacter(char) {
            // Apply shift if needed (lowercase letters)
            let actualChar = char;
            if (!this.isShift && char.length === 1 && /[A-Z]/.test(char)) {
                actualChar = char.toLowerCase();
            }

            if (this.inputId) {
                // If inputId is provided, find the input element and insert at cursor position
                const inputElement = document.getElementById(this.inputId);
                if (inputElement) {
                    const start = inputElement.selectionStart;
                    const end = inputElement.selectionEnd;
                    const currentValue = this.modelValue.toString();
                    const newValue = currentValue.substring(0, start) + actualChar + currentValue.substring(end);
                    
                    this.$emit('update:modelValue', newValue);
                    
                    // Restore cursor position after value update
                    this.$nextTick(() => {
                        inputElement.focus();
                        inputElement.setSelectionRange(start + actualChar.length, start + actualChar.length);
                    });
                }
            } else {
                // If no inputId, just append to the end
                const newValue = this.modelValue.toString() + actualChar;
                this.$emit('update:modelValue', newValue);
            }

            // Reset shift after character input (like real keyboard)
            if (this.isShift) {
                this.isShift = false;
            }
        },
        backspace() {
            if (this.inputId) {
                const inputElement = document.getElementById(this.inputId);
                if (inputElement) {
                    const start = inputElement.selectionStart;
                    const end = inputElement.selectionEnd;
                    const currentValue = this.modelValue.toString();
                    
                    let newValue;
                    if (start !== end) {
                        // If text is selected, delete selection
                        newValue = currentValue.substring(0, start) + currentValue.substring(end);
                    } else if (start > 0) {
                        // Delete one character before cursor
                        newValue = currentValue.substring(0, start - 1) + currentValue.substring(start);
                    } else {
                        return; // Nothing to delete
                    }
                    
                    this.$emit('update:modelValue', newValue);
                    
                    // Restore cursor position
                    this.$nextTick(() => {
                        inputElement.focus();
                        const newPos = start !== end ? start : Math.max(0, start - 1);
                        inputElement.setSelectionRange(newPos, newPos);
                    });
                }
            } else {
                // If no inputId, delete last character
                const currentValue = this.modelValue.toString();
                if (currentValue.length > 0) {
                    this.$emit('update:modelValue', currentValue.slice(0, -1));
                }
            }
        },
        clearInput() {
            this.$emit('update:modelValue', '');
            
            // Focus the input element after clearing if inputId is provided
            if (this.inputId) {
                this.$nextTick(() => {
                    const inputElement = document.getElementById(this.inputId);
                    if (inputElement) {
                        inputElement.focus();
                    }
                });
            }
        }
    }
}
</script>

<style scoped>
.keyboard-key {
    @apply w-9 h-9 rounded border border-gray-300 bg-white hover:bg-gray-100 transition-colors flex items-center justify-center text-sm font-medium text-gray-700;
}

.keyboard-container {
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
</style>
