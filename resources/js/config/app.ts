export const appConfig = {
    callStoreRoutes: {
        initiate_outbound: 'api.calls.outbound.initiate',
        token: 'api.twilio.token',
        tones: {
            inbound: 'api.audio.inbound',
            ringback: 'api.audio.ringback',
        },
    },
} as const;
