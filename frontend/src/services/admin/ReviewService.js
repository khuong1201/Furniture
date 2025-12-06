class ReviewService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!ReviewService._instance) {
            ReviewService._instance = new ReviewService();
        }
        return ReviewService._instance;
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
            console.error(`ReviewService Error:`, error);
            throw error;
        }
    }

    async getAll(params = {}) {
        const qs = new URLSearchParams(params).toString();
        return this._request(`/admin/reviews${qs ? '?' + qs : ''}`);
    }

    async getById(uuid) { return this._request(`/reviews/${uuid}`); }
    async create(data) { return this._request('/reviews', { method: 'POST', body: JSON.stringify(data) }); }
    async update(uuid, data) { return this._request(`/reviews/${uuid}`, { method: 'PUT', body: JSON.stringify(data) }); }
    async delete(uuid) { return this._request(`/reviews/${uuid}`, { method: 'DELETE' }); }

    static getAll(params) { return ReviewService.instance.getAll(params); }
    static getById(uuid) { return ReviewService.instance.getById(uuid); }
    static create(data) { return ReviewService.instance.create(data); }
    static update(uuid, data) { return ReviewService.instance.update(uuid, data); }
    static delete(uuid) { return ReviewService.instance.delete(uuid); }
}

export default ReviewService;
