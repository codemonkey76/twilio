<script setup lang="ts">
import { useCallStore } from '@/stores/call';
import api from '@/utils/api';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';

const callStore = useCallStore();
const { currentCall } = storeToRefs(callStore);

const phoneNumber = ref('');
const isDialling = ref(false);
const error = ref<string | null>(null);
const dids = ref<
    Array<{
        id: number;
        number: string;
        name: string | null;
        description: string | null;
        display_name: string;
    }>
>([]);
const selectedDidId = ref<number | null>(null);
const loadingDids = ref(false);

/**
 * Format the number for display
 */
const formattedNumber = computed(() => {
    const cleaned = phoneNumber.value.replace(/[^\d+]/g, '');
    if (cleaned.length === 0) return '';

    if (cleaned.startsWith('+61') && cleaned.length >= 4) {
        const withoutCountryCode = cleaned.slice(3);

        // Mobile numbers (+61 4XX XXX XXX)
        if (withoutCountryCode.startsWith('4') && withoutCountryCode.length >= 1) {
            if (withoutCountryCode.length <= 3) return `+61 ${withoutCountryCode.slice(0, 1)} ${withoutCountryCode.slice(1)}`;
            if (withoutCountryCode.length <= 7)
                return `+61 ${withoutCountryCode.slice(0, 1)} ${withoutCountryCode.slice(1, 5)} ${withoutCountryCode.slice(5)}`;
            return `+61 ${withoutCountryCode.slice(0, 1)} ${withoutCountryCode.slice(1, 5)} ${withoutCountryCode.slice(5, 9)}`;
        }

        // Landline (+61 X XXXX XXXX)
        if (withoutCountryCode.length >= 1) {
            if (withoutCountryCode.length <= 1) return `+61 ${withoutCountryCode}`;
            if (withoutCountryCode.length <= 5) return `+61 ${withoutCountryCode.slice(0, 1)} ${withoutCountryCode.slice(1)}`;
            return `+61 ${withoutCountryCode.slice(0, 1)} ${withoutCountryCode.slice(1, 5)} ${withoutCountryCode.slice(5, 9)}`;
        }
    }

    // Mobile numbers (04XX XXX XXX)
    if (cleaned.startsWith('04') && cleaned.length >= 4) {
        if (cleaned.length <= 6) return `${cleaned.slice(0, 4)} ${cleaned.slice(4)}`;
        return `${cleaned.slice(0, 4)} ${cleaned.slice(4, 7)} ${cleaned.slice(7, 10)}`;
    }

    // 1800 numbers (1800 XXX XXX)
    if (cleaned.startsWith('1800') && cleaned.length >= 4) {
        if (cleaned.length <= 7) return `1800 ${cleaned.slice(4)}`;
        return `1800 ${cleaned.slice(4, 7)} ${cleaned.slice(7, 10)}`;
    }

    // 1300 numbers (1300 XXX XXX)
    if (cleaned.startsWith('1300') && cleaned.length >= 4) {
        if (cleaned.length <= 7) return `1300 ${cleaned.slice(4)}`;
        return `1300 ${cleaned.slice(4, 7)} ${cleaned.slice(7, 10)}`;
    }

    // 13 numbers (13 XX XX)
    if (cleaned.startsWith('13') && !cleaned.startsWith('1300') && cleaned.length >= 2) {
        if (cleaned.length <= 4) return `13 ${cleaned.slice(2)}`;
        return `13 ${cleaned.slice(2, 4)} ${cleaned.slice(4, 6)}`;
    }

    // Standard landline numbers (XX) XXXX-XXXX
    if (cleaned.length >= 2) {
        if (cleaned.length <= 2) return `(${cleaned}`;
        if (cleaned.length <= 6) return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2)}`;
        return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 6)}-${cleaned.slice(6, 10)}`;
    }

    return cleaned;
});

/**
 * Can we dial this number right now?
 */
const canDial = computed(() => {
    const cleaned = phoneNumber.value.replace(/[^\d+]/g, '');
    const hasValidNumber = (() => {
        if (cleaned.startsWith('+61')) return cleaned.length === 12;
        if (cleaned.startsWith('04')) return cleaned.length === 10;
        if (cleaned.startsWith('1800')) return cleaned.length === 10;
        if (cleaned.startsWith('1300')) return cleaned.length === 10;
        if (cleaned.startsWith('13') && !cleaned.startsWith('1300')) return cleaned.length === 6;
        return cleaned.length === 10; // Default landline
    })();

    return hasValidNumber && selectedDidId.value && !currentCall.value && !isDialling.value;
});

const addDigit = (digit: string) => {
    if (phoneNumber.value.replace(/\D/g, '').length < 15) {
        phoneNumber.value += digit;
    }
};

const clearNumber = () => {
    phoneNumber.value = '';
    error.value = null;
};

const backspace = () => {
    phoneNumber.value = phoneNumber.value.slice(0, -1);
    error.value = null;
};

/**
 * Make an outbound call
 */
const cleanNumber = (number: string) => {
    const cleanedNumber = phoneNumber.value.replace(/\D/g, '');
    let dialNumber = cleanedNumber;

    if (cleanedNumber.startsWith('04')) {
        dialNumber = `+614${cleanedNumber.slice(2)}`;
    } else if (cleanedNumber.length === 10 && !cleanedNumber.startsWith('1')) {
        dialNumber = `+61${cleanedNumber}`;
    } else if (cleanedNumber.startsWith('13') || cleanedNumber.startsWith('1800') || cleanedNumber.startsWith('1300')) {
        dialNumber = cleanedNumber; // Use as-is for special numbers
    } else {
        dialNumber = `+61${cleanedNumber}`;
    }
    return dialNumber;
};

const makeCall = async () => {
    if (!canDial.value) return;
    if (selectedDidId.value === null) return;

    const dialNumber = cleanNumber(phoneNumber.value);

    isDialling.value = true;
    console.log('📞 Dialing ' + dialNumber);
    await callStore.requestCall(dialNumber, selectedDidId.value);
    isDialling.value = false;
};

/**
 * DID Fetching and selection
 */
const fetchUserDids = async () => {
    try {
        loadingDids.value = true;
        const response = await api.get('dids.index');
        dids.value = response.dids;

        selectedDidId.value = dids.value[0].id;
    } catch (err) {
        console.error('Error fetching user DIDs:', err);
    } finally {
        loadingDids.value = false;
    }
};

const onDidChange = (didId: number) => {
    selectedDidId.value = didId;
};

onMounted(() => {
    fetchUserDids();
});

const showTips = ref<boolean>(true);

const keypadButtons = [
    [
        { digit: '1', letters: '' },
        { digit: '2', letters: 'ABC' },
        { digit: '3', letters: 'DEF' },
    ],
    [
        { digit: '4', letters: 'GHI' },
        { digit: '5', letters: 'JKL' },
        { digit: '6', letters: 'MNO' },
    ],
    [
        { digit: '7', letters: 'PQRS' },
        { digit: '8', letters: 'TUV' },
        { digit: '9', letters: 'WXYZ' },
    ],
    [
        { digit: '*', letters: '' },
        { digit: '0', letters: '+' },
        { digit: '#', letters: '' },
    ],
];
</script>

<template>
    <div v-if="dids.length > 0" class="mt-6 rounded-lg bg-gray-800 p-4">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Manual Dial</h2>
            <div class="flex items-center space-x-2">
                <div v-if="currentCall" class="h-2 w-2 animate-pulse rounded-full bg-green-400"></div>
                <span v-if="currentCall" class="text-xs text-green-400">Call Active</span>
            </div>
        </div>

        <!-- DID Selection -->
        <div class="mb-4">
            <label class="mb-2 block text-sm font-medium text-gray-300"> Call From </label>
            <div v-if="loadingDids" class="rounded-lg bg-gray-700 p-3 text-center text-gray-400">Loading your phone numbers...</div>
            <div v-else-if="dids.length === 0" class="rounded-lg bg-gray-700 p-3 text-center text-gray-400">No phone numbers assigned to you</div>
            <select
                v-else
                v-model="selectedDidId"
                @change="onDidChange(selectedDidId!)"
                :disabled="currentCall"
                class="w-full rounded-lg border border-gray-600 bg-gray-700 px-3 py-2 text-white focus:border-transparent focus:ring-2 focus:ring-blue-500 disabled:bg-gray-800 disabled:text-gray-500"
            >
                <option value="" disabled>Select a phone number</option>
                <option v-for="did in dids" :key="did.id" :value="did.id">
                    {{ did.display_name }}
                </option>
            </select>
        </div>

        <!-- Phone Number Display -->
        <div class="mb-4 flex min-h-[3rem] flex-col justify-between rounded-lg bg-gray-700 p-3">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div>
                        <input id="number" v-model="phoneNumber" type="text" class="mt-1 block w-full" placeholder="+61 X XXXX XXXX" required />
                    </div>
                </div>
                <button
                    v-if="phoneNumber"
                    @click="backspace"
                    class="ml-2 p-1 text-gray-400 transition-colors hover:text-white"
                    :disabled="currentCall"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"
                        />
                    </svg>
                </button>
            </div>
            <div v-if="formattedNumber && formattedNumber !== phoneNumber" class="mt-1 text-sm text-gray-400">
                {{ formattedNumber }}
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-4 rounded border border-red-700 bg-red-900/50 p-3 text-sm text-red-200">
            {{ error }}
            <button @click="error = null" class="float-right text-red-400 hover:text-red-200">×</button>
        </div>

        <!-- Keypad -->
        <div class="mb-4 grid grid-cols-3 gap-2">
            <template v-for="row in keypadButtons" :key="row">
                <button
                    v-for="button in row"
                    :key="button.digit"
                    @click="addDigit(button.digit)"
                    :disabled="currentCall"
                    class="rounded-lg bg-gray-700 p-3 text-white transition-colors hover:bg-gray-600 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:bg-gray-800 disabled:text-gray-500"
                >
                    <div class="text-lg font-semibold">{{ button.digit }}</div>
                    <div v-if="button.letters" class="mt-1 text-xs text-gray-400">
                        {{ button.letters }}
                    </div>
                </button>
            </template>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-2">
            <button
                @click="makeCall"
                :disabled="!canDial"
                class="flex flex-1 items-center justify-center space-x-2 rounded-lg bg-green-600 p-3 font-medium text-white transition-colors hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none disabled:bg-gray-700 disabled:text-gray-500"
            >
                <svg v-if="isDialling" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                    />
                </svg>
                <svg v-else class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                    />
                </svg>
                <span>{{ isDialling ? 'Dialing...' : 'Call' }}</span>
            </button>

            <button
                @click="clearNumber"
                :disabled="!phoneNumber || currentCall"
                class="rounded-lg bg-gray-700 px-4 py-3 text-white transition-colors hover:bg-gray-600 focus:ring-2 focus:ring-gray-500 focus:outline-none disabled:bg-gray-800 disabled:text-gray-500"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                </svg>
            </button>
        </div>

        <!-- Help Text -->
        <div class="mt-4 rounded bg-gray-700/50 p-3 text-sm text-gray-400">
            <p class="mb-1 font-medium text-gray-300">
                💡 Tips:
                <input
                    type="checkbox"
                    v-model="showTips"
                    class="ml-2 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500 focus:ring-offset-gray-800"
                />
            </p>
            <div class="overflow-hidden transition-all duration-300 ease-in-out" :class="showTips ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'">
                <ul class="space-y-1 text-xs">
                    <li>• Select a phone number to call from</li>
                    <li>• Mobile: 04XX XXX XXX (10 digits)</li>
                    <li>• Landline: (XX) XXXX-XXXX (10 digits)</li>
                    <li>• Toll-free: 1800 XXX XXX or 1300 XXX XXX</li>
                    <li>• Local rate: 13 XX XX (6 digits)</li>
                    <li>• Cannot dial while another call is active</li>
                </ul>
            </div>
        </div>
    </div>
</template>
