<script setup lang="ts">
import { useCallStore } from '@/stores/call';
import { storeToRefs } from 'pinia';
const callStore = useCallStore();
const { currentCall, callDuration } = storeToRefs(callStore);

/** Format seconds into MM:SS */
const formatDuration = (seconds: number) => {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const handlePickup = async () => {
    try {
        // await callStore.answer();
    } catch (error) {
        console.error('Failed to pickup call:', error);
    }
};

const handleHangup = async () => {
    try {
        callStore.hangup();
    } catch (error) {
        console.error('Failed to hangup call:', error);
    }
};

const handleToggleRecord = async () => {
    try {
        // await toggleRecording(callSid);
        // callStore.updateCall(callSid, {
        //     isRecording: !firstCall.value?.isRecording,
        // });
    } catch (error) {
        console.error('Failed to toggle recording:', error);
    }
};

const handleToggleHold = async () => {
    try {
        // const newHoldState = await toggleHold();
        // callStore.updateCall(callSid, {
        //     isOnHold: newHoldState,
        //     status: newHoldState ? "on-hold" : "in-progress",
        // });
    } catch (error) {
        console.error('Failed to toggle hold:', error);
    }
};
</script>
<template>
    <div v-if="currentCall" class="flex items-center space-x-3 rounded-lg border border-gray-600 bg-gray-700 px-3 py-2">
        <!-- Call Status Indicator -->
        <div class="flex items-center space-x-2">
            <div
                :class="[
                    'h-2 w-2 rounded-full',
                    currentCall.status === 'ringing' || currentCall.status === 'ringing-connected'
                        ? 'animate-pulse bg-yellow-500'
                        : currentCall.status === 'active'
                          ? 'bg-green-500'
                          : currentCall.status === 'on-hold'
                            ? 'animate-pulse bg-orange-500'
                            : 'bg-gray-500',
                ]"
            ></div>

            <!-- Recording Indicator -->
            <div v-if="currentCall.isRecording" class="flex items-center space-x-1">
                <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-500"></div>
                <span class="text-xs font-medium text-red-400">REC</span>
            </div>
        </div>

        <!-- Queue and Direction Info -->
        <div class="grid min-w-0 grid-cols-2 gap-x-2 text-xs font-medium">
            <div v-if="currentCall.queue" class="truncate text-xs font-medium text-white">Queue:</div>
            <div v-if="currentCall.queue" class="text-blue-300">
                {{ currentCall.queue }}
            </div>
            <div class="truncate text-xs font-medium text-white">Direction:</div>
            <div class="text-blue-300">
                {{ currentCall.direction === 'inbound' ? 'Inbound' : 'Outbound' }}
            </div>
        </div>

        <!-- Caller Information -->
        <div class="flex min-w-0 items-center space-x-2">
            <div v-if="currentCall.direction === 'inbound'" class="min-w-0 flex-1">
                <div class="truncate text-sm font-medium text-white">
                    {{ currentCall.callerName || currentCall.callerNumber || 'Unknown' }}
                </div>
                <div class="truncate text-xs text-gray-400">
                    {{ currentCall.callerNumber || 'No number' }}
                </div>
            </div>
            <div v-else class="min-w-0 flex-1">
                <div class="truncate text-sm font-medium text-white">
                    {{ currentCall.calledName || currentCall.calledNumber || 'Unknown' }}
                </div>
                <div class="truncate text-xs text-gray-400">
                    {{ currentCall.calledNumber || 'No number' }}
                </div>
            </div>
        </div>

        <!-- Call Timer -->
        <div v-if="currentCall.status === 'active'" class="flex-shrink-0 font-mono text-sm text-gray-300">
            {{ formatDuration(callDuration) }}
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-1">
            <!-- Pickup Button (only show when ringing) -->
            <button
                v-if="currentCall.status === 'ringing'"
                @click="handlePickup()"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600 transition-colors duration-200 hover:bg-green-700"
                title="Answer call"
            >
                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                    />
                </svg>
            </button>

            <!-- Record Button -->
            <button
                v-if="currentCall.status === 'active'"
                @click="handleToggleRecord()"
                :class="[
                    'flex h-7 w-7 items-center justify-center rounded-full transition-colors duration-200',
                    currentCall.isRecording ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-600 hover:bg-gray-700',
                ]"
                :title="currentCall.isRecording ? 'Stop recording' : 'Start recording'"
            >
                <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="10" r="6" />
                </svg>
            </button>

            <!-- Hold Button -->
            <button
                v-if="currentCall.status === 'active' || currentCall.status === 'on-hold'"
                @click="handleToggleHold()"
                :class="[
                    'flex h-7 w-7 items-center justify-center rounded-full transition-colors duration-200',
                    currentCall.status === 'on-hold' ? 'bg-orange-600 hover:bg-orange-700' : 'bg-gray-600 hover:bg-gray-700',
                ]"
                :title="currentCall.status === 'on-hold' ? 'Resume call' : 'Hold call'"
            >
                <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

            <!-- Hangup Button -->
            <button
                @click="handleHangup()"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600 transition-colors duration-200 hover:bg-red-700"
                title="End call"
            >
                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>
