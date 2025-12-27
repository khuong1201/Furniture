import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, Edit, Trash2, Package, RefreshCw, ChevronLeft, ChevronRight } from 'lucide-react';

// Hooks & Services
import { useProduct } from '@/hooks/admin/useProduct';
import { useCategory } from '@/hooks/admin/useCategory';
import { useBrand } from '@/hooks/admin/useBrand';

// Components
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import LoadingSpinner from '@/components/admin/shared/LoadingSpinner';
import './ProductList.css';

// --- Helper Functions (Pure) ---
const calculateStock = (p) => p.variants?.reduce((acc, v) => acc + (v.stock_quantity || 0), 0) || 0;
const getMainImage = (p) => p.images?.find(img => img.is_primary)?.url || p.images?.[0]?.url;

const ProductList = () => {
    const navigate = useNavigate();
    
    // Hooks
    const { products, meta, loading, fetchProducts, deleteProduct } = useProduct();
    const { categories, fetchCategories } = useCategory();
    const { brands, fetchBrands } = useBrand();

    // State
    const [filters, setFilters] = useState({ search: '', category_uuid: '', brand_uuid: '', is_active: '' });
    const [confirmDelete, setConfirmDelete] = useState(null);
    const [isDeleting, setIsDeleting] = useState(false);

    // 1. Init Metadata
    useEffect(() => {
        fetchCategories({ per_page: 100 }); 
        fetchBrands();
        // eslint-disable-next-line
    }, []); 

    // 2. Load Data on Filter Change
    useEffect(() => {
        handleLoadData(1);
        // eslint-disable-next-line
    }, [filters]); 

    // Actions
    const handleLoadData = async (page) => {
        const cleanParams = Object.fromEntries(Object.entries(filters).filter(([_, v]) => v !== ''));
        await fetchProducts({ ...cleanParams, page, per_page: 15 });
    };

    const handleRefresh = () => setFilters({ search: '', category_uuid: '', brand_uuid: '', is_active: '' });

    // --- Delete Logic ---
    const openDeleteModal = (e, product) => {
        e.stopPropagation(); 
        setConfirmDelete(product);
    };

    const handleDelete = async () => {
        if (!confirmDelete) return;
        
        setIsDeleting(true);
        try {
            await deleteProduct(confirmDelete.uuid);
            setConfirmDelete(null); 
            
            // Logic: Back to previous page if current page becomes empty
            const pageToLoad = (products.length === 1 && meta.current_page > 1) 
                ? meta.current_page - 1 
                : meta.current_page;
                
            await handleLoadData(pageToLoad);
        } catch (e) {
            console.error("Delete Failed:", e);
            alert("Delete failed: " + (e.message || "Unknown error"));
        } finally {
            setIsDeleting(false);
        }
    };

    // First load spinner
    if (loading && products.length === 0) return <LoadingSpinner />;

    return (
        <div className="product_list-container">
            {/* --- FILTER BAR --- */}
            <div className="filter-bar">
                <div className="search-group">
                    <Search className="search-icon" size={18} />
                    <input 
                        className="filter-input" 
                        placeholder="Search product..." 
                        value={filters.search} 
                        onChange={e => setFilters(p => ({...p, search: e.target.value}))} 
                    />
                </div>
                
                <select className="filter-select" value={filters.category_uuid} onChange={e => setFilters(p => ({...p, category_uuid: e.target.value}))}>
                    <option value="">All Categories</option>
                    {categories.map(c => <option key={c.uuid} value={c.uuid}>{c.name}</option>)}
                </select>

                <select className="filter-select" value={filters.brand_uuid} onChange={e => setFilters(p => ({...p, brand_uuid: e.target.value}))}>
                    <option value="">All Brands</option>
                    {brands.map(b => <option key={b.uuid} value={b.uuid}>{b.name}</option>)}
                </select>

                <button onClick={handleRefresh} className="btn-refresh" disabled={loading} title="Refresh Data">
                    <RefreshCw size={18} className={loading ? 'animate-spin' : ''}/>
                </button>
            </div>

            {/* --- DATA TABLE --- */}
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th className="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {products.length > 0 ? products.map((p, index) => {
                            const imageUrl = getMainImage(p);
                            const totalStock = calculateStock(p);
                            
                            // REFACTOR: Optimize Image Loading
                            // Load eager (ngay lập tức) cho 8 item đầu tiên để tránh Intervention
                            // Load lazy cho các item bên dưới để tiết kiệm băng thông
                            const loadingType = index < 8 ? "eager" : "lazy";

                            return (
                                <tr key={p.uuid} onClick={() => navigate(`/admin/products/${p.uuid}/edit`)} className="cursor-pointer hover:bg-gray-50">
                                    <td className="product-cell">
                                        <div className="product-thumb-container">
                                            {imageUrl ? (
                                                <img 
                                                    src={imageUrl} 
                                                    alt={p.name} 
                                                    className="product-thumb"
                                                    crossOrigin="anonymous" // Production Fix: Avoid Tracking Warning
                                                    loading={loadingType}   // Production Fix: Avoid Lazy Load Intervention
                                                    decoding="async"        // Performance: Non-blocking UI
                                                />
                                            ) : (
                                                <Package size={20} className="text-gray-400"/>
                                            )}
                                        </div>
                                        <div className="product-info">
                                            <div className="product-name">{p.name}</div>
                                            <span className="sku-text">{p.sku || 'N/A'}</span>
                                            <div className="text-xs text-gray-400 mt-1">{p.brand?.name} • {p.category?.name}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div className="font-medium text-gray-700">{p.price_range || p.price_formatted}</div>
                                    </td>
                                    <td>
                                        {p.has_variants 
                                            ? <div className="text-xs text-gray-600"><span className="font-bold">{totalStock}</span> variants</div>
                                            : <span className={totalStock === 0 ? 'text-red-500 font-bold' : 'text-gray-700'}>{totalStock} in stock</span>
                                        }
                                    </td>
                                    <td>
                                        <span className={`status-badge ${p.is_active ? 'active' : 'hidden'}`}>
                                            {p.is_active ? 'Active' : 'Hidden'}
                                        </span>
                                    </td>
                                    <td>
                                        <div className="action-buttons">
                                            <button 
                                                onClick={(e) => { e.stopPropagation(); navigate(`/admin/products/${p.uuid}/edit`); }} 
                                                className="btn-action" 
                                                title="Edit"
                                            >
                                                <Edit size={16}/>
                                            </button>
                                            <button 
                                                onClick={(e) => openDeleteModal(e, p)} 
                                                className="btn-action delete" 
                                                title="Delete"
                                            >
                                                <Trash2 size={16}/>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            );
                        }) : (
                            <tr><td colSpan="5" className="p-8 text-center text-gray-500 italic">No products found.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* --- PAGINATION --- */}
            {products.length > 0 && (
                <div className="pagination-bar">
                    <div className="pagination-info">Showing {products.length} of {meta.total} results</div>
                    <div className="pagination-controls">
                        <button onClick={() => handleLoadData(meta.current_page - 1)} disabled={meta.current_page <= 1} className="page-btn">
                            <ChevronLeft size={20}/>
                        </button>
                        <span className="page-current">Page {meta.current_page} / {meta.last_page}</span>
                        <button onClick={() => handleLoadData(meta.current_page + 1)} disabled={meta.current_page >= meta.last_page} className="page-btn">
                            <ChevronRight size={20}/>
                        </button>
                    </div>
                    <div className="pagination-spacer"></div>
                </div>
            )}

            {/* --- CONFIRM DIALOG --- */}
            <ConfirmDialog 
                isOpen={!!confirmDelete} 
                title="Delete Product" 
                message={`Are you sure you want to delete "${confirmDelete?.name}"? This action cannot be undone.`} 
                onConfirm={handleDelete} 
                onClose={() => setConfirmDelete(null)}
                isLoading={isDeleting}
                type="danger"
            />
        </div>
    );
};

export default ProductList;