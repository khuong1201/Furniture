class ProductService {
  // 1. Singleton Instance
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/public';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  // 2. Lấy instance duy nhất
  static get instance() {
    if (!ProductService._instance) {
      ProductService._instance = new ProductService();
    }
    return ProductService._instance;
  }

  // 3. Hàm Private xử lý request chung (Tự động gắn Token)
  async _request(endpoint, options = {}) {
    try {
      // 🟢 TỰ ĐỘNG LẤY TOKEN TỪ LOCAL STORAGE
      // (Cách này an toàn nhất, không lo bị mất token khi F5)
      const token = localStorage.getItem('access_token');
      
      const headers = { ...this.headers, ...options.headers };
      
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const url = `${this.baseUrl}${endpoint}`;
      const config = {
        ...options,
        headers,
      };

      const response = await fetch(url, config);
      
      // Xử lý lỗi HTTP
      if (!response.ok) {
        throw new Error(`Lỗi API (${response.status}): ${response.statusText}`);
      }

      const result = await response.json();

      // Laravel thường trả về data bọc trong biến .data, ta check để trả về gọn gàng
      return result.data || result; 

    } catch (error) {
      console.error(`ProductService Error (${endpoint}):`, error);
      throw error;
    }
  }


  async getAllProducts() {
    return this._request('/products', { method: 'GET' });
  }


  async getProductDetail(id) {
    return this._request(`/products/${id}`, { method: 'GET' });
  }
  
  async searchProducts(keyword) {
    return this._request(`/products/search?q=${keyword}`, { method: 'GET' });
  }


  static async getAllProducts() {
    return ProductService.instance.getAllProducts();
  }

  static async getProductDetail(id) {
    return ProductService.instance.getProductDetail(id);
  }

  static async searchProducts(keyword) {
    return ProductService.instance.searchProducts(keyword);
  }
}

export default ProductService;