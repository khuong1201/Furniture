class ProductService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost/api';
    this.defaultHeaders = {
      Accept: 'application/json',
    };
  }

  static get instance() {
    if (!this._instance) {
      this._instance = new ProductService();
    }
    return this._instance;
  }

  async _request(endpoint, options = {}) {
    const token = localStorage.getItem('access_token');

    const headers = {
      ...this.defaultHeaders,
      ...options.headers,
    };

    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }

    // ✅ CHỈ set Content-Type nếu có body
    if (options.body && !headers['Content-Type']) {
      headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(
      `${this.baseUrl}${endpoint}`,
      { ...options, headers }
    );

    if (!res.ok) {
      throw new Error(`API ${res.status}: ${res.statusText}`);
    }

    const json = await res.json();
    return json.data ?? json;
  }

  // ---------- GET (KHÔNG Content-Type) ----------

  async getAllProducts(params = {}) {
    const query = new URLSearchParams(
      Object.entries(params).filter(([, v]) => v != null && v !== '')
    ).toString();

    return this._request(`/public/products?${query}`, {
      method: 'GET',
    });
  }

  async getProductDetail(id) {
    return this._request(`/public/products/${id}`, {
      method: 'GET',
    });
  }

  async searchProducts(keyword) {
    return this.getAllProducts({ search: keyword, page: 1 });
  }

  // ---------- POST / PUT / PATCH (CÓ Content-Type) ----------

  // async createProduct(payload) {
  //   return this._request('/products', {
  //     method: 'POST',
  //     body: JSON.stringify(payload),
  //   });
  // }

  // async updateProduct(id, payload) {
  //   return this._request(`/products/${id}`, {
  //     method: 'PUT',
  //     body: JSON.stringify(payload),
  //   });
  // }

  static getAllProducts(p) { return this.instance.getAllProducts(p); }
  static getProductDetail(id) { return this.instance.getProductDetail(id); }
}
export default ProductService;