class PromotionService {
    static _instance = null;

    constructor() {
        this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    }

    static get instance() {
        if (!PromotionService._instance) {
            PromotionService._instance = new PromotionService();
        }
        return PromotionService._instance;
    }

    async _request(endpoint, options = {}) {
        try {
            const token = localStorage.getItem('access_token');
            const headers = { ...this.headers, ...options.headers };

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                ...options,
                headers,
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result?.message || 'API Error');
            }

            return result;
        } catch (error) {
            console.error(`PromotionService Error (${endpoint}):`, error);
            throw error;
        }
    }

    // ✅ Get All Promotions
    async getAll(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this._request(`/admin/promotions${queryString ? '?' + queryString : ''}`);
    }

    // ✅ Get Single Promotion
    async getById(uuid) {
        return this._request(`/admin/promotions/${uuid}`);
    }

    // ✅ Create Promotion
    async create(data) {
        return this._request('/admin/promotions', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // ✅ Update Promotion
    async update(uuid, data) {
        return this._request(`/admin/promotions/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // ✅ Delete Promotion
    async delete(uuid) {
        return this._request(`/admin/promotions/${uuid}`, {
            method: 'DELETE',
        });
    }

    // --- STATIC WRAPPERS ---
    static getAll(params) { return PromotionService.instance.getAll(params); }
    static getById(uuid) { return PromotionService.instance.getById(uuid); }
    static create(data) { return PromotionService.instance.create(data); }
    static update(uuid, data) { return PromotionService.instance.update(uuid, data); }
    static delete(uuid) { return PromotionService.instance.delete(uuid); }
}

export default PromotionService;
