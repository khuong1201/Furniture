class AttributeService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!AttributeService._instance) {
            AttributeService._instance = new AttributeService();
        }
        return AttributeService._instance;
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
            console.error(`Attribute Service Error (${endpoint}):`, error);
            throw error;
        }
    }

    // Get all attributes
    async getAll(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/attributes${queryString ? `?${queryString}` : ''}`);
    }

    // Get single attribute
    async getById(uuid) {
        return this._request(`/admin/attributes/${uuid}`);
    }

    // Create attribute
    async create(data) {
        return this._request('/admin/attributes', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update attribute
    async update(uuid, data) {
        return this._request(`/admin/attributes/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete attribute
    async delete(uuid) {
        return this._request(`/admin/attributes/${uuid}`, {
            method: 'DELETE',
        });
    }

    // Static methods
    static getAll(params) { return AttributeService.instance.getAll(params); }
    static getById(uuid) { return AttributeService.instance.getById(uuid); }
    static create(data) { return AttributeService.instance.create(data); }
    static update(uuid, data) { return AttributeService.instance.update(uuid, data); }
    static delete(uuid) { return AttributeService.instance.delete(uuid); }
}

export default AttributeService;
