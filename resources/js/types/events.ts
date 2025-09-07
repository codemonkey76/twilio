import { Conference } from './conference';

export interface OutboundCallEvent {
    conference: Conference;
}

export interface InboundCallEvent {
    conference: Conference;
}
