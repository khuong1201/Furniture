class OrderService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!OrderService._instance) {
            OrderService._instance = new OrderService();
        }
        return OrderService._instance;
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
            console.error(`Order Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get all orders (admin)
    async getOrders(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/orders${queryString ? `?${queryString}` : ''}`);
    }

    // Get single order (admin)
    async getOrder(uuid) {
        return this._request(`/admin/orders/${uuid}`);
    }

    // Update order status
    async updateOrderStatus(uuid, status) {
        return this._request(`/admin/orders/${uuid}/status`, {
            method: 'PUT',
            body: JSON.stringify({ status }),
        });
    }

    // Static methods
    static async getOrders(params) {
        return OrderService.instance.getOrders(params);
    }

    static async getOrder(uuid) {
        return OrderService.instance.getOrder(uuid);
    }

    static async updateOrderStatus(uuid, status) {
        return OrderService.instance.updateOrderStatus(uuid, status);
    }
}

export default OrderService;
