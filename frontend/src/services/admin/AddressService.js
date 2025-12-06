class AddressService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!AddressService._instance) {
            AddressService._instance = new AddressService();
        }
        return AddressService._instance;
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
            console.error(`Address Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get addresses (can filter by user_id for admin)
    async getAddresses(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/addresses${queryString ? `?${queryString}` : ''}`);
    }

    // Static methods
    static async getAddresses(params) {
        return AddressService.instance.getAddresses(params);
    }
}

export default AddressService;
