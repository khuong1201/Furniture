class NotificationService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!NotificationService._instance) {
      NotificationService._instance = new NotificationService();
    }
    return NotificationService._instance;
  }

  async _request(endpoint, options = {}) {
    try {
      const token = localStorage.getItem('access_token');
      const headers = { ...this.headers, ...options.headers };

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const url = `${this.baseUrl}${endpoint}`;
      const response = await fetch(url, { ...options, headers });
      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || `Lỗi API: ${response.status}`);
      }

      // Lưu ý: Controller trả về { success, data, meta }
      // Ta trả về nguyên object result để Hook có thể lấy được cả 'data' và 'meta'
      return result; 
    } catch (error) {
      console.error(`Notification Service Error (${endpoint}):`, error);
      throw error;
    }
  }

  // --- 1. Lấy danh sách thông báo ---
  // Params: page, per_page
  async getNotifications(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this._request(`/notifications?${queryString}`, {
      method: 'GET',
    });
  }

  // --- 2. Đánh dấu đã đọc 1 cái ---
  async markAsRead(uuid) {
    return this._request(`/notifications/${uuid}/read`, {
      method: 'PATCH',
    });
  }

  // --- 3. Đánh dấu đã đọc tất cả ---
  async markAllAsRead() {
    return this._request(`/notifications/read-all`, {
      method: 'POST',
    });
  }

  // --- 4. Xóa thông báo ---
  async deleteNotification(uuid) {
    return this._request(`/notifications/${uuid}`, {
      method: 'DELETE',
    });
  }

  // --- Static Helpers ---
  static getNotifications(params) { return NotificationService.instance.getNotifications(params); }
  static markAsRead(uuid) { return NotificationService.instance.markAsRead(uuid); }
  static markAllAsRead() { return NotificationService.instance.markAllAsRead(); }
  static deleteNotification(uuid) { return NotificationService.instance.deleteNotification(uuid); }
}

export default NotificationService;