import api from '@/utils/api';
import { ref } from 'vue';

const inbound = ref<HTMLAudioElement | null>(null);
const ringback = ref<HTMLAudioElement | null>(null);

export function useRingtone() {
    let inboundRouteName: string;
    let ringbackRouteName: string;

    async function init({ inbound, ringback }: { inbound: string; ringback: string }): Promise<void> {
        inboundRouteName = inbound;
        ringbackRouteName = ringback;

        await Promise.all([initInbound(), initRingback()]);
    }
    /** 🔈 Init inbound ringtone */
    async function initInbound(): Promise<void> {
        if (inbound.value) return;

        try {
            const { url } = await api.get(inboundRouteName);

            inbound.value = new Audio(url);
            inbound.value.loop = true;
            inbound.value.volume = 0.7;
            inbound.value.preload = 'auto';
        } catch (err) {
            console.error('🔈 Failed to init inbound ringtone:', err);
        }
    }

    /** 🔈 Init ringback ringback */
    async function initRingback(): Promise<void> {
        if (ringback.value) return;

        try {
            const { url } = await api.get(ringbackRouteName);

            ringback.value = new Audio(url);
            ringback.value.loop = true;
            ringback.value.volume = 0.7;
            ringback.value.preload = 'auto';
        } catch (err) {
            console.error('🔈 Failed to init ringback ringtone:', err);
        }
    }

    /** ▶️Play inbound */
    async function playInbound(): Promise<void> {
        if (!inbound.value) await initInbound();

        try {
            await inbound.value?.play();
        } catch (err) {
            console.error('🔈 Failed to play inbound ringtone:', err);
        }
    }

    /** Stop inbound */
    function stopInbound(): void {
        if (!inbound.value) return;
        inbound.value.pause();
        inbound.value.currentTime = 0;
    }

    /** ▶️Play ringback */
    async function playRingback(): Promise<void> {
        if (!ringback.value) await initRingback();

        try {
            await ringback.value?.play();
        } catch (err) {
            console.error('🔈 Failed to play ringback ringtone:', err);
        }
    }
    /** Stop ringback */
    function stopRingback(): void {
        if (!ringback.value) return;
        ringback.value.pause();
        ringback.value.currentTime = 0;
    }

    /** 💣 Destroy both */
    function destroy(): void {
        stopInbound();
        stopRingback();
        inbound.value = null;
        ringback.value = null;
    }

    return {
        init,
        playInbound,
        stopInbound,
        playRingback,
        stopRingback,
        destroy,
    };
}
