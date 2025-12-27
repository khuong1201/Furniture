import HttpService from './HttpService';

class CollectionService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!CollectionService._instance) CollectionService._instance = new CollectionService();
        return CollectionService._instance;
    }

    async getAll(params = {}) { return this.request('/public/collections', { params }); }
    async getById(uuid) { return this.request(`/public/collections/${uuid}`); }
    
    async create(data) {
        return this.request('/admin/collections', { method: 'POST', body: JSON.stringify(data) });
    }
    
    async update(uuid, data) {
        return this.request(`/admin/collections/${uuid}`, { method: 'PUT', body: JSON.stringify(data) });
    }
    
    async delete(uuid) { return this.request(`/admin/collections/${uuid}`, { method: 'DELETE' }); }

    // Static
    static getAll(params) { return CollectionService.instance.getAll(params); }
    static getById(uuid) { return CollectionService.instance.getById(uuid); }
    static create(data) { return CollectionService.instance.create(data); }
    static update(uuid, data) { return CollectionService.instance.update(uuid, data); }
    static delete(uuid) { return CollectionService.instance.delete(uuid); }
}

export default CollectionService;