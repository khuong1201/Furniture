import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Edit, Trash2, Layers, RefreshCw, ChevronLeft, ChevronRight, Search } from 'lucide-react';
import CategoryService from '@/services/admin/CategoryService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import LoadingSpinner from '@/components/admin/shared/LoadingSpinner';
import './CategoryManagement.css'; 

const CategoryList = () => {
    const navigate = useNavigate();
    const [categories, setCategories] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [loading, setLoading] = useState(true);
    const [confirmDialog, setConfirmDialog] = useState({ isOpen: false, item: null });
    
    const [searchTerm, setSearchTerm] = useState('');
    const [isRefreshing, setIsRefreshing] = useState(false);

    useEffect(() => {
        loadCategories(1);
    }, []);

    const loadCategories = async (page = 1) => {
        setLoading(true);
        try {
            const params = { page, per_page: 7 };
            if (searchTerm) params.search = searchTerm;

            const response = await CategoryService.getCategories(params);
            
            setCategories(response.data || []);
            if (response.meta) {
                setPagination(response.meta);
            }
        } catch (err) {
            console.error(err);
            setCategories([]);
        } finally {
            setLoading(false);
            setIsRefreshing(false);
        }
    };

    const handleRefresh = () => {
        setIsRefreshing(true);
        setSearchTerm('');
        loadCategories(1);
    };

    const handleSearch = (e) => {
        e.preventDefault();
        loadCategories(1);
    };

    const handleDelete = async () => {
        if (!confirmDialog.item) return;
        try {
            await CategoryService.deleteCategory(confirmDialog.item.uuid);
            loadCategories(pagination.current_page);
            setConfirmDialog({ isOpen: false, item: null });
        } catch (err) {
            alert('Error deleting category');
        }
    };

    if (loading && !isRefreshing && categories.length === 0) return <LoadingSpinner />;

    return (
        <div className="category_management">
            
            {/* Filter Bar */}
            <div className="filter-bar">
                <form onSubmit={handleSearch} className="search-group">
                    <Search className="search-icon" size={18} />
                    <input 
                        className="filter-input"
                        type="text" 
                        placeholder="Search categories..." 
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </form>
                <button 
                    className="btn-refresh" 
                    onClick={handleRefresh}
                    disabled={loading || isRefreshing}
                >
                    <RefreshCw size={18} className={isRefreshing ? 'animate-spin' : ''} />
                </button>
            </div>

            {/* Content Wrapper */}
            <div className="category_content">
                
                {/* 1. Header (Đứng im) */}
                <div className="category_list-header">
                    <div>Img</div>
                    <div>Category Name</div>
                    <div>Slug</div>
                    <div>Status</div>
                    <div style={{ textAlign: 'right' }}>Actions</div>
                </div>

                {/* 2. Body (Trượt dọc) */}
                <div className="category_list_body">
                    {categories.length > 0 ? categories.map(cat => (
                        <div key={cat.uuid} className="category_item">
                            <div>
                                <div className="category-img-box">
                                    {cat.image ? <img src={cat.image} alt="" /> : <Layers size={20} className="text-gray-400"/>}
                                </div>
                            </div>
                            
                            <div>
                                <div className="category-name">{cat.name}</div>
                                <div className="category-desc">{cat.description || 'No description'}</div>
                            </div>

                            <div className="category-slug">{cat.slug}</div>

                            <div>
                                <span className={`status-badge ${cat.is_active ? 'active' : 'hidden'}`}>
                                    {cat.is_active ? 'Active' : 'Hidden'}
                                </span>
                            </div>

                            <div className="category_actions">
                                <button className="category_action-btn" onClick={() => navigate(`/admin/categories/${cat.uuid}/edit`)}>
                                    <Edit size={16} />
                                </button>
                                <button className="category_action-btn delete" onClick={() => setConfirmDialog({ isOpen: true, item: cat })}>
                                    <Trash2 size={16} />
                                </button>
                            </div>
                        </div>
                    )) : (
                        <div className="category_empty-state">No categories found.</div>
                    )}
                </div>

                {/* 3. Pagination (Đứng im) */}
                {pagination.total > 0 && (
                    <div className="pagination-bar">
                        <div className="pagination-info">
                            Showing <span className="font-bold text-gray-900">{categories.length}</span> of <span className="font-bold text-gray-900">{pagination.total}</span> categories
                        </div>
                        
                        <div className="pagination-controls">
                            <button 
                                className="page-btn" 
                                disabled={pagination.current_page === 1} 
                                onClick={() => loadCategories(pagination.current_page - 1)}
                            >
                                <ChevronLeft size={18} />
                            </button>
                            
                            <span className="page-current">Page {pagination.current_page} / {pagination.last_page}</span>

                            <button 
                                className="page-btn" 
                                disabled={pagination.current_page === pagination.last_page} 
                                onClick={() => loadCategories(pagination.current_page + 1)}
                            >
                                <ChevronRight size={18} />
                            </button>
                        </div>
                        
                        <div className="pagination-spacer"></div>
                    </div>
                )}
            </div>

            <ConfirmDialog 
                isOpen={confirmDialog.isOpen}
                onClose={() => setConfirmDialog({ isOpen: false, item: null })}
                onConfirm={handleDelete}
                title="Delete Category"
                message={`Are you sure you want to delete "${confirmDialog.item?.name}"?`}
                type="danger"
            />
        </div>
    );
};

export default CategoryList;