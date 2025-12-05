class PaymentService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!PaymentService._instance) {
            PaymentService._instance = new PaymentService();
        }
        return PaymentService._instance;
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
            console.error(`Payment Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get all payments with pagination
    async getAll(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/payments${queryString ? `?${queryString}` : ''}`);
    }

    // Get single payment
    async getById(uuid) {
        return this._request(`/payments/${uuid}`);
    }

    // Create payment
    async create(data) {
        return this._request('/payments', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update payment
    async update(uuid, data) {
        return this._request(`/payments/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Static methods
    static getAll(params) { return PaymentService.instance.getAll(params); }
    static getById(uuid) { return PaymentService.instance.getById(uuid); }
    static create(data) { return PaymentService.instance.create(data); }
    static update(uuid, data) { return PaymentService.instance.update(uuid, data); }
}

export default PaymentService;
