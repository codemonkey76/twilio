import { useRingtone } from '@/composables/useRingtone';
import { useTwilio } from '@/composables/useTwilio';
import { InboundCallEvent, OutboundCallEvent } from '@/types/events';
import api from '@/utils/api';
import { Call } from '@twilio/voice-sdk';
import { defineStore } from 'pinia';
import { computed, markRaw, ref } from 'vue';

interface CallStoreRoutes {
    initiate_outbound: string;
    token: string;
    tones: {
        inbound: string;
        ringback: string;
    };
}
type CallStatus = 'initializing' | 'ringing' | 'ringing-connected' | 'active' | 'on-hold' | 'ended';

interface CallState {
    createdAt: Date;
    answeredAt: Date | null;
    endedAt: Date | null;
    direction: 'inbound' | 'outbound';
    status: CallStatus;
    to: string | null;
    from: string | null;
    conference: string | null;
    timeoutId: number | null;
    call: Call | null | any;
}

export const useCallStore = defineStore('call', () => {
    const callStoreRoutes = ref<CallStoreRoutes | null>(null);
    const isRefreshingToken = ref<boolean>(false);

    const currentCall = ref<CallState | null>(null);
    const callDuration = computed(() => {
        if (!currentCall.value || !currentCall.value.answeredAt) return 0;

        return currentCall.value.status === 'active' ? Math.floor((Date.now() - currentCall.value.answeredAt.getTime()) / 1000) : 0;
    });

    const refreshToken = async () => {
        if (isRefreshingToken.value || !callStoreRoutes.value) {
            console.log('🔄 Skipping token refresh');
            return;
        }

        try {
            isRefreshingToken.value = true;
            console.log('Refreshing Twilio token...');
            const { token } = await api.get(callStoreRoutes.value.token);
            if (device.value) {
                device.value.updateToken(token);
                console.log('✅ Token refreshed');
            } else {
                console.warn('Device not available for token update');
            }
        } catch (error) {
            console.error('Failed to refresh Twilio token:', error);
        } finally {
            isRefreshingToken.value = false;
        }
    };

    const {
        init: initDevice,
        reset,
        device,
    } = useTwilio({
        destroyed: () => {
            console.log('Device destroyed');
        },
        error: (error, call) => {
            console.error('Device error', error, call);
        },
        incoming: (call) => {
            console.log('Incoming call', call);
        },
        registered: () => {
            console.log('Device registered');
        },
        registering: () => {
            console.log('Device registering');
        },
        tokenWillExpire: async () => {
            await refreshToken();
        },
        unregistered: () => {
            console.log('Device unregistered');
        },
    });
    const { init: initRingtone, playInbound, stopInbound, playRingback, stopRingback } = useRingtone();

    /** Create a new CallState */
    const createCallState = (data: {
        direction: 'inbound' | 'outbound';
        from?: string | null;
        to?: string | null;
        conference?: string | null;
        status?: CallStatus;
    }): CallState => {
        return {
            direction: data.direction,
            status: data.status || 'initializing',
            answeredAt: null,
            endedAt: null,
            createdAt: new Date(),
            to: data.to || null,
            from: data.from || null,
            conference: data.conference || null,
            timeoutId: null,
            call: null,
        };
    };

    const init = async (routes: CallStoreRoutes) => {
        callStoreRoutes.value = routes;
        const { token } = await api.get(routes.token);
        await initDevice(token);
        await initRingtone({
            inbound: routes.tones.inbound,
            ringback: routes.tones.ringback,
        });
    };

    const requestCall = async (number: string, didId: number) => {
        if (!callStoreRoutes.value) return;

        try {
            await api.post(
                callStoreRoutes.value.initiate_outbound,
                {},
                {
                    to: number,
                    did_id: didId,
                },
            );
        } catch (err) {
            console.error('Failed to initiate outbound call:', err);
        }
    };

    const hangup = () => {
        // stopRingback();
        // stopInbound();

        if (!currentCall.value) return;

        if (currentCall.value.timeoutId) {
            console.log('Clearing timeout');
            clearTimeout(currentCall.value.timeoutId);
            currentCall.value.timeoutId = null;
        }

        if (currentCall.value.status === 'ringing') {
            console.log('Rejecting call');
            currentCall.value.call?.reject();
        } else {
            console.log('Disconnecting call');
            currentCall.value.call?.disconnect();
        }
        // currentCall.value = null;
    };

    const setRingTimer = () => {
        if (!currentCall.value) return;

        console.log('📞 Setting 30s ring timer');
        currentCall.value.timeoutId = window.setTimeout(() => {
            if (currentCall.value?.status === 'ringing' || currentCall.value?.status === 'ringing-connected') {
                console.error('Call timed out');
                hangup();
            }
        }, 30000);
    };

    const handleOutboundCallInitiated = async (event: OutboundCallEvent) => {
        const { conference } = event;

        const call = createCallState({
            direction: 'outbound',
            from: conference.from,
            to: conference.to,
            conference: conference.name,
            status: 'ringing',
        });

        currentCall.value = call;
        playRingback();
        setRingTimer();
        await startOutboundCall({
            conferenceName: conference.name,
        });
    };

    const handleInboundCall = async (event: InboundCallEvent) => {
        const { conference } = event;

        const call = createCallState({
            direction: 'inbound',
            from: conference.from,
            to: conference.to,
            conference: conference.name,
            status: 'ringing',
        });

        currentCall.value = call;
        playInbound();
    };

    const startOutboundCall = async (params: { to?: string; conferenceName?: string }) => {
        const callParams: Record<string, string> = {};
        if (!currentCall.value) return;

        if (params.conferenceName) {
            callParams.call_type = 'conference';
            callParams.conference_name = params.conferenceName;
        } else if (params.to) {
            callParams.to = params.to;
        }

        const call = await device.value?.connect({
            params: callParams,
        });
        if (!call) {
            console.error('Failed to connect to outbound call');
            return;
        }

        currentCall.value.call = markRaw(call);
        attachCallListeners(call);
    };

    const attachCallListeners = (call: Call) => {
        call.removeAllListeners();

        call.on('accept', (call: Call) => {
            if (!currentCall.value) return;
            currentCall.value.call = markRaw(call);
            currentCall.value.status = 'ringing-connected';
            stopInbound();
            stopRingback();
        });
        call.on('disconnect', (call) => {
            console.log('Call disconnected: ', call);

            if (!currentCall.value) {
                console.warn('Disconnect fired, but no currentCall found');
                return;
            }

            if (currentCall.value.timeoutId) {
                clearTimeout(currentCall.value.timeoutId);
                currentCall.value.timeoutId = null;
            }

            currentCall.value = null;

            // This is a hack to fix a bug where the device never returns to a usable state
            // after a call is disconnected. It stays in a "busy" state and can't be used again.
            if (device.value && device.value.isBusy) {
                device.value.destroy();
                device.value = null;

                reset();
            }
        });
        call.on('error', (err) => {
            console.error('TwilioEvent: (error):', err);
        });
    };

    return {
        // State
        currentCall,
        callDuration,

        // Actions
        init,
        requestCall,
        handleInboundCall,
        handleOutboundCallInitiated,
        hangup,
        // answerCall,
        // rejectCall,
        // endCall,
        // holdCall,
        // muteCall,
        // transferCall,
    };
});
