import HttpService from './HttpService';

class PermissionService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!PermissionService._instance) PermissionService._instance = new PermissionService();
        return PermissionService._instance;
    }

    async getAll(params = {}) { return this.request('/admin/permissions', { params }); }
    async getById(uuid) { return this.request(`/admin/permissions/${uuid}`); }
    
    async create(data) {
        return this.request('/admin/permissions', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    async update(uuid, data) {
        return this.request(`/admin/permissions/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    async delete(uuid) { return this.request(`/admin/permissions/${uuid}`, { method: 'DELETE' }); }
    async getMyPermissions() { return this.request('/admin/my-permissions'); }

    // Static
    static getAll(params) { return PermissionService.instance.getAll(params); }
    static getById(uuid) { return PermissionService.instance.getById(uuid); }
    static create(data) { return PermissionService.instance.create(data); }
    static update(uuid, data) { return PermissionService.instance.update(uuid, data); }
    static delete(uuid) { return PermissionService.instance.delete(uuid); }
    static getMyPermissions() { return PermissionService.instance.getMyPermissions(); }
}

export default PermissionService;