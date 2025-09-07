import { Call, Device } from '@twilio/voice-sdk';
import { computed, markRaw, ref } from 'vue';
export interface TwilioError {
    causes: string[];
    code: number;
    description: string;
    explanation: string;
    message: string;
    name: string;
    originalError?: string;
    solutions: string[];
}
interface TwilioDeviceHandlers {
    destroyed?: () => void;
    error?: (error: TwilioError, call: Call) => void;
    incoming?: (call: Call) => void;
    registered?: () => void;
    registering?: () => void;
    tokenWillExpire?: () => void;
    unregistered?: () => void;
}
export const useTwilio = (handlers: TwilioDeviceHandlers) => {
    const device = ref<Device | null>(null);
    const accessToken = ref<string | null>(null);
    const initialized = ref<boolean>(false);
    const isReady = computed(() => initialized.value && device.value?.state === 'registered');
    const addListeners = (handlers: TwilioDeviceHandlers) => {
        if (!device.value) {
            return;
        }
        if (handlers?.destroyed) {
            device.value.on('destroyed', handlers.destroyed);
        }
        if (handlers?.error) {
            device.value.on('error', handlers.error);
        }
        if (handlers?.incoming) {
            device.value.on('incoming', handlers.incoming);
        }
        if (handlers?.registered) {
            device.value.on('registered', handlers.registered);
        }
        if (handlers?.registering) {
            device.value.on('registering', handlers.registering);
        }
        if (handlers?.tokenWillExpire) {
            device.value.on('tokenWillExpire', handlers.tokenWillExpire);
        }
        if (handlers?.unregistered) {
            device.value.on('unregistered', handlers.unregistered);
        }
    };
    const init = async (token: string) => {
        if (isReady.value) {
            return;
        }
        if (device.value) {
            device.value.destroy();
            device.value = null;
            initialized.value = false;
        }
        accessToken.value = token;
        device.value = markRaw(
            new Device(token, {
                logLevel: 'debug',
                disableAudioContextSounds: true,
            }),
        );
        addListeners(handlers);
        await device.value.register();
        initialized.value = true;
    };
    const reset = async () => {
        if (device.value) {
            device.value.destroy();
            device.value = null;
        }
        if (accessToken.value) await init(accessToken.value);
    };
    return {
        device,
        init,
        reset,
    };
};
