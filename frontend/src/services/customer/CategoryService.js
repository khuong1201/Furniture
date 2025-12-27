class CategoryService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!CategoryService._instance) {
      CategoryService._instance = new CategoryService();
    }
    return CategoryService._instance;
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

      if (!response.ok) {
        throw new Error(`Lỗi API (${response.status}): ${response.statusText}`);
      }

      const result = await response.json();
      return result.data || result; 
    } catch (error) {
      console.error(`CategoryService Error (${endpoint}):`, error);
      throw error;
    }
  }

  async getCategories(params = {}) {
    const validParams = Object.fromEntries(
      Object.entries(params).filter(([_, v]) => v != null && v !== '')
    );

    const queryString = new URLSearchParams(validParams).toString();

    return this._request(`/public/categories?${queryString}`, { method: 'GET' });
  }

  async getCategoryTree() {
    return this.getCategories({ tree: true });
  }

  // Static helpers
  static async getCategories(params) { return CategoryService.instance.getCategories(params); }
  static async getCategoryTree() { return CategoryService.instance.getCategoryTree(); }
}

export default CategoryService;