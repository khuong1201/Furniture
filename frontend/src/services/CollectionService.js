class CollectionService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!CollectionService._instance) {
            CollectionService._instance = new CollectionService();
        }
        return CollectionService._instance;
    }

    async _request(endpoint, options = {}) {
        try {
            const token = localStorage.getItem('access_token');
            const headers = { ...this.headers, ...options.headers };
            if (token) headers['Authorization'] = `Bearer ${token}`;

            const response = await fetch(`${this.baseUrl}${endpoint}`, { ...options, headers });
            const result = await response.json();

            if (!response.ok) throw new Error(result?.message || 'API Error');
            return result;
        } catch (error) {
            console.error(`CollectionService Error:`, error);
            throw error;
        }
    }

    async getAll(params = {}) {
        const qs = new URLSearchParams(params).toString();
        return this._request(`/public/collections${qs ? '?' + qs : ''}`);
    }

    async getById(uuid) { return this._request(`/public/collections/${uuid}`); }
    async create(data) { return this._request('/admin/collections', { method: 'POST', body: JSON.stringify(data) }); }
    async update(uuid, data) { return this._request(`/admin/collections/${uuid}`, { method: 'PUT', body: JSON.stringify(data) }); }
    async delete(uuid) { return this._request(`/admin/collections/${uuid}`, { method: 'DELETE' }); }

    static getAll(params) { return CollectionService.instance.getAll(params); }
    static getById(uuid) { return CollectionService.instance.getById(uuid); }
    static create(data) { return CollectionService.instance.create(data); }
    static update(uuid, data) { return CollectionService.instance.update(uuid, data); }
    static delete(uuid) { return CollectionService.instance.delete(uuid); }
}

export default CollectionService;
