import HttpService from './HttpService'; // Giả sử bạn lưu HttpService ở file này

class UserService extends HttpService {
    static _instance = null;

    constructor() {
        super(); // Gọi constructor của HttpService để setup baseUrl và headers
    }

    static get instance() {
        if (!UserService._instance) {
            UserService._instance = new UserService();
        }
        return UserService._instance;
    }

    /**
     * Instance Methods
     * Sử dụng this.request() từ HttpService
     */

    // Get users list (admin)
    // HttpService đã tự xử lý việc chuyển object params thành query string (?key=value)
    async getUsers(params = {}) {
        return this.request('/admin/users', { params });
    }

    // Get single user
    async getUser(uuid) {
        return this.request(`/admin/users/${uuid}`);
    }

    // Get current user profile
    async getProfile() {
        return this.request('/profile');
    }

    // Create user (admin)
    async createUser(data) {
        return this.request('/admin/users', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    // Update user (admin)
    async updateUser(uuid, data) {
        return this.request(`/admin/users/${uuid}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    // Delete user (admin)
    async deleteUser(uuid) {
        return this.request(`/admin/users/${uuid}`, {
            method: 'DELETE',
        });
    }

    /**
     * Static Methods (Wrapper cho Singleton)
     * Giữ nguyên để tương thích với code cũ
     */
    static async getUsers(params) {
        return UserService.instance.getUsers(params);
    }

    static async getUser(uuid) {
        return UserService.instance.getUser(uuid);
    }

    static async getProfile() {
        return UserService.instance.getProfile();
    }

    static async createUser(data) {
        return UserService.instance.createUser(data);
    }

    static async updateUser(uuid, data) {
        return UserService.instance.updateUser(uuid, data);
    }

    static async deleteUser(uuid) {
        return UserService.instance.deleteUser(uuid);
    }
}

export default UserService;