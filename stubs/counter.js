import { defineStore } from "pinia";

export const useCounterStore = defineStore("counter", {
    state: () => ({
        count: 0,
        history: [],
    }),

    getters: {
        doubleCount: (state) => state.count * 2,
        isEven: (state) => state.count % 2 === 0,
        lastAction: (state) => state.history[state.history.length - 1] || null,
    },

    actions: {
        increment() {
            this.count++;
            this.history.push({
                action: "increment",
                timestamp: new Date().toISOString(),
                value: this.count,
            });
        },

        decrement() {
            this.count--;
            this.history.push({
                action: "decrement",
                timestamp: new Date().toISOString(),
                value: this.count,
            });
        },

        reset() {
            this.count = 0;
            this.history.push({
                action: "reset",
                timestamp: new Date().toISOString(),
                value: this.count,
            });
        },

        setCount(value) {
            this.count = value;
            this.history.push({
                action: "set",
                timestamp: new Date().toISOString(),
                value: this.count,
            });
        },

        clearHistory() {
            this.history = [];
        },
    },

    // Optional: Persist state
    persist: {
        key: "counter-store",
        storage: localStorage,
        paths: ["count"], // Only persist count, not history
    },
});
