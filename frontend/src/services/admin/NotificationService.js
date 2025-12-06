class NotificationService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!NotificationService._instance) {
            NotificationService._instance = new NotificationService();
        }
        return NotificationService._instance;
    }

    setToken(token) {
        if (token) {
            this.headers['Authorization'] = `Bearer ${token}`;
        } else {
            delete this.headers['Authorization'];
        }
    }

    async _request(endpoint, options = {}) {
        try {
            const token = localStorage.getItem('access_token');
            if (token) {
                this.setToken(token);
            }

            const url = `${this.baseUrl}${endpoint}`;
            const config = {
                ...options,
                headers: {
                    ...this.headers,
                    ...options.headers,
                },
            };

            const response = await fetch(url, config);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || `API Error: ${response.status}`);
            }

            return result;
        } catch (error) {
            console.error(`Notification Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get notifications
    async getNotifications(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/notifications${queryString ? `?${queryString}` : ''}`);
    }

    // Mark as read
    async markAsRead(uuid) {
        return this._request(`/notifications/${uuid}/read`, {
            method: 'PATCH',
        });
    }

    // Mark all as read
    async markAllAsRead() {
        return this._request('/notifications/read-all', {
            method: 'POST',
        });
    }

    // Delete notification
    async deleteNotification(uuid) {
        return this._request(`/notifications/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Static methods
    static async getNotifications(params) {
        return NotificationService.instance.getNotifications(params);
    }

    static async markAsRead(uuid) {
        return NotificationService.instance.markAsRead(uuid);
    }

    static async markAllAsRead() {
        return NotificationService.instance.markAllAsRead();
    }

    static async deleteNotification(uuid) {
        return NotificationService.instance.deleteNotification(uuid);
    }
}

export default NotificationService;
