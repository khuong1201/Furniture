import HttpService from './HttpService';

class PromotionService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!PromotionService._instance) PromotionService._instance = new PromotionService();
        return PromotionService._instance;
    }

    async getAll(params = {}) { return this.request('/admin/promotions', { params }); }
    async getById(uuid) { return this.request(`/admin/promotions/${uuid}`); }
    
    async create(data) {
        return this.request('/admin/promotions', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    async update(uuid, data) {
        return this.request(`/admin/promotions/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    async delete(uuid) {
        return this.request(`/admin/promotions/${uuid}`, { method: 'DELETE' });
    }

    // Static Wrappers
    static getAll(params) { return PromotionService.instance.getAll(params); }
    static getById(uuid) { return PromotionService.instance.getById(uuid); }
    static create(data) { return PromotionService.instance.create(data); }
    static update(uuid, data) { return PromotionService.instance.update(uuid, data); }
    static delete(uuid) { return PromotionService.instance.delete(uuid); }
}

export default PromotionService;