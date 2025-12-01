const API_URL = 'http://localhost:8000/api'; 

export const getFlashSaleProducts = async () => {
  try {
    const response = await fetch(`${API_URL}/products`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    });
    
    if (!response.ok) {
      throw new Error(`Lỗi API: ${response.status}`);
    }
    
    const result = await response.json();
    console.log("Dữ liệu từ Laravel:", result);
    
    return result.data || result; 

  } catch (error) {
    console.error("Lỗi gọi API:", error);
    throw error;
  }
};