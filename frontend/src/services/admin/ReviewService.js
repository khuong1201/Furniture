import HttpService from './HttpService';

class ReviewService extends HttpService {
    static _instance = null;

    constructor() { super(); }

    static get instance() {
        if (!ReviewService._instance) ReviewService._instance = new ReviewService();
        return ReviewService._instance;
    }

    // Instance Methods
    async getAll(params = {}) {
        return this.request('/admin/reviews', { params });
    }

    async getById(uuid) { return this.request(`/reviews/${uuid}`); }
    async create(data) { return this.request('/reviews', { method: 'POST', body: JSON.stringify(data) }); }
    async update(uuid, data) { return this.request(`/reviews/${uuid}`, { method: 'PUT', body: JSON.stringify(data) }); }
    async delete(uuid) { return this.request(`/reviews/${uuid}`, { method: 'DELETE' }); }

    // Static Methods
    static getAll(params) { return ReviewService.instance.getAll(params); }
    static getById(uuid) { return ReviewService.instance.getById(uuid); }
    static create(data) { return ReviewService.instance.create(data); }
    static update(uuid, data) { return ReviewService.instance.update(uuid, data); }
    static delete(uuid) { return ReviewService.instance.delete(uuid); }
}

export default ReviewService;