import HttpService from './HttpService';

class NotificationService extends HttpService {
    static _instance = null;
    constructor() { super(); }

    static get instance() {
        if (!NotificationService._instance) NotificationService._instance = new NotificationService();
        return NotificationService._instance;
    }

    async getNotifications(params = {}) { return this.request('/notifications', { params }); }
    
    async markAsRead(uuid) {
        return this.request(`/notifications/${uuid}/read`, { method: 'PATCH' });
    }

    async markAllAsRead() {
        return this.request('/notifications/read-all', { method: 'POST' });
    }

    async deleteNotification(uuid) {
        return this.request(`/notifications/${uuid}`, { method: 'DELETE' });
    }

    // Static
    static getNotifications(params) { return NotificationService.instance.getNotifications(params); }
    static markAsRead(uuid) { return NotificationService.instance.markAsRead(uuid); }
    static markAllAsRead() { return NotificationService.instance.markAllAsRead(); }
    static deleteNotification(uuid) { return NotificationService.instance.deleteNotification(uuid); }
}

export default NotificationService;