import { route } from 'ziggy-js';
window.route = route;
class ApiClient {
    private getCSRFToken(): string {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    private getDefaultHeaders() {
        return {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': this.getCSRFToken(),
        };
    }

    async get(routeName: string, params?: any, options: RequestInit = {}) {
        const url = route(routeName, params);
        return this.request(url, {
            ...options,
            method: 'GET',
        });
    }

    async post(routeName: string, params?: any, data?: any, options: RequestInit = {}) {
        const url = route(routeName, params);
        return this.request(url, {
            ...options,
            method: 'POST',
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    async put(routeName: string, params?: any, data?: any, options: RequestInit = {}) {
        const url = route(routeName, params);
        return this.request(url, {
            ...options,
            method: 'PUT',
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    async delete(routeName: string, params?: any, options: RequestInit = {}) {
        const url = route(routeName, params);
        return this.request(url, {
            ...options,
            method: 'DELETE',
        });
    }

    private async request(url: string, options: RequestInit = {}) {
        const config: RequestInit = {
            ...options,
            headers: {
                ...this.getDefaultHeaders(),
                ...options.headers,
            },
        };

        const response = await fetch(url, config);

        if (!response.ok) {
            throw new Error(`API request failed: ${response.statusText}`);
        }

        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        }

        return response.text();
    }
}

export default new ApiClient();
