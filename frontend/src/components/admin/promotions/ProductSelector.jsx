import React, { useState, useEffect } from 'react';
import { X, Search, CheckSquare, Square, Loader2 } from 'lucide-react';
import ProductService from '@/services/admin/ProductService'; 

const ProductSelector = ({ selectedIds = [], onSave, onClose }) => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [localSelected, setLocalSelected] = useState([...selectedIds]);

    // Gọi API lấy sản phẩm thật
    useEffect(() => {
        const fetchProducts = async () => {
            setLoading(true);
            try {
                const params = { per_page: 50 }; 
                if (searchTerm) params.search = searchTerm;
                
                const response = await ProductService.getAll(params);
                setProducts(response.data?.data || response.data || []);
            } catch (error) {
                console.error("Lỗi tải sản phẩm:", error);
            } finally {
                setLoading(false);
            }
        };

        const timeoutId = setTimeout(() => {
            fetchProducts();
        }, 500);

        return () => clearTimeout(timeoutId);
    }, [searchTerm]);

    const toggleProduct = (id) => {
        setLocalSelected(prev => 
            prev.includes(id) ? prev.filter(pid => pid !== id) : [...prev, id]
        );
    };

    const handleSelectAll = () => {
        if (localSelected.length === products.length) {
            setLocalSelected([]);
        } else {
            setLocalSelected(products.map(p => p.id));
        }
    };

    return (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div className="bg-white rounded-xl w-full max-w-2xl h-[80vh] flex flex-col shadow-2xl animate-fade-in">
                {/* Header */}
                <div className="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
                    <h3 className="font-semibold text-lg text-gray-800">Chọn sản phẩm áp dụng</h3>
                    <button onClick={onClose} className="p-1 hover:bg-gray-200 rounded-full transition-colors">
                        <X size={20} className="text-gray-500"/>
                    </button>
                </div>

                {/* Search Bar */}
                <div className="p-4 border-b space-y-3">
                    <div className="flex items-center gap-2 bg-gray-100 px-3 py-2.5 rounded-lg border border-transparent focus-within:border-blue-400 focus-within:bg-white transition-all">
                        <Search size={18} className="text-gray-500"/>
                        <input 
                            className="bg-transparent outline-none w-full text-sm"
                            placeholder="Tìm kiếm theo tên hoặc mã SKU..."
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            autoFocus
                        />
                    </div>
                    {products.length > 0 && (
                        <div className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer" onClick={handleSelectAll}>
                            {localSelected.length === products.length && products.length > 0 ? <CheckSquare size={16} className="text-blue-600"/> : <Square size={16}/>}
                            <span>Chọn tất cả ({products.length})</span>
                        </div>
                    )}
                </div>

                {/* Product List */}
                <div className="flex-1 overflow-y-auto p-2 bg-gray-50/50">
                    {loading ? (
                        <div className="flex flex-col justify-center items-center h-full text-gray-400">
                            <Loader2 className="animate-spin mb-2" size={32}/>
                            <span className="text-sm">Đang tải sản phẩm...</span>
                        </div>
                    ) : products.length === 0 ? (
                        <div className="flex justify-center items-center h-full text-gray-400 text-sm">
                            Không tìm thấy sản phẩm nào.
                        </div>
                    ) : (
                        <div className="space-y-1">
                            {products.map(product => {
                                const isSelected = localSelected.includes(product.id);
                                return (
                                    <div 
                                        key={product.id}
                                        onClick={() => toggleProduct(product.id)}
                                        className={`flex items-center justify-between p-3 rounded-lg cursor-pointer border transition-all ${
                                            isSelected 
                                            ? 'bg-blue-50 border-blue-200 shadow-sm' 
                                            : 'bg-white hover:bg-gray-50 border-transparent hover:border-gray-200'
                                        }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            {/* Ảnh thumb nếu có */}
                                            <div className="w-10 h-10 rounded bg-gray-200 overflow-hidden flex-shrink-0">
                                                {product.image ? (
                                                    <img src={product.image} alt="" className="w-full h-full object-cover"/>
                                                ) : (
                                                    <div className="w-full h-full flex items-center justify-center text-xs text-gray-400">IMG</div>
                                                )}
                                            </div>
                                            <div>
                                                <div className="font-medium text-gray-800 text-sm">{product.name}</div>
                                                <div className="text-xs text-gray-500">
                                                    SKU: {product.sku || 'N/A'} <span className="mx-1">•</span> 
                                                    {parseInt(product.price || 0).toLocaleString()}đ
                                                </div>
                                            </div>
                                        </div>
                                        {isSelected ? <CheckSquare size={20} className="text-blue-600"/> : <Square size={20} className="text-gray-300"/>}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-4 border-t bg-white rounded-b-xl flex justify-between items-center shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                    <span className="text-sm font-medium text-gray-700">
                        Đã chọn: <span className="text-blue-600 font-bold">{localSelected.length}</span> sản phẩm
                    </span>
                    <div className="flex gap-3">
                        <button 
                            onClick={onClose} 
                            className="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium transition-colors"
                        >
                            Hủy bỏ
                        </button>
                        <button 
                            onClick={() => onSave(localSelected)} 
                            className="px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow-sm transition-colors"
                        >
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProductSelector;