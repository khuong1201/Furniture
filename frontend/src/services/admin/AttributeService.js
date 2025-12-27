import HttpService from './HttpService';

class AttributeService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!AttributeService._instance) AttributeService._instance = new AttributeService();
        return AttributeService._instance;
    }

    async getAll(params = {}) { return this.request('/admin/attributes', { params }); }
    async getById(uuid) { return this.request(`/admin/attributes/${uuid}`); }
    
    async create(data) {
        return this.request('/admin/attributes', { method: 'POST', body: JSON.stringify(data) });
    }

    async update(uuid, data) {
        return this.request(`/admin/attributes/${uuid}`, { method: 'PUT', body: JSON.stringify(data) });
    }

    async delete(uuid) { return this.request(`/admin/attributes/${uuid}`, { method: 'DELETE' }); }

    // Static
    static getAll(params) { return AttributeService.instance.getAll(params); }
    static getById(uuid) { return AttributeService.instance.getById(uuid); }
    static create(data) { return AttributeService.instance.create(data); }
    static update(uuid, data) { return AttributeService.instance.update(uuid, data); }
    static delete(uuid) { return AttributeService.instance.delete(uuid); }
}

export default AttributeService;