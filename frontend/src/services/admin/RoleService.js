import HttpService from './HttpService';

class RoleService extends HttpService {
    static _instance = null;

    constructor() {
        super();
    }

    static get instance() {
        if (!RoleService._instance) {
            RoleService._instance = new RoleService();
        }
        return RoleService._instance;
    }

    /**
     * Instance Methods
     */

    // Get roles list (admin)
    async getRoles(params = {}) {
        // HttpService tự động xử lý params thành query string
        return this.request('/admin/roles', { params });
    }

    // Get single role
    async getRole(uuid) {
        return this.request(`/admin/roles/${uuid}`);
    }

    // Create role (admin)
    async createRole(data) {
        return this.request('/admin/roles', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update role (admin)
    async updateRole(uuid, data) {
        return this.request(`/admin/roles/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete role (admin)
    async deleteRole(uuid) {
        return this.request(`/admin/roles/${uuid}`, {
            method: 'DELETE',
        });
    }

    /**
     * Static Methods (Wrapper)
     */
    static async getRoles(params) {
        return RoleService.instance.getRoles(params);
    }

    static async getRole(uuid) {
        return RoleService.instance.getRole(uuid);
    }

    static async createRole(data) {
        return RoleService.instance.createRole(data);
    }

    static async updateRole(uuid, data) {
        return RoleService.instance.updateRole(uuid, data);
    }

    static async deleteRole(uuid) {
        return RoleService.instance.deleteRole(uuid);
    }
}

export default RoleService;