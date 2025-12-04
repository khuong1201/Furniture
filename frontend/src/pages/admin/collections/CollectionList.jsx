import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Layers, Plus, Edit, Trash2, Search, Image, Package,
    AlertCircle, RefreshCw, ChevronLeft, ChevronRight
} from 'lucide-react';
import CollectionService from '@/services/CollectionService';
import ConfirmDialog from '@/pages/admin/categories/ConfirmDialog';
import './CollectionManagement.css';

const CollectionList = () => {
    const navigate = useNavigate();
    const [collections, setCollections] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [deleteConfirm, setDeleteConfirm] = useState({ show: false, item: null });
    const [deleting, setDeleting] = useState(false);

    useEffect(() => {
        fetchCollections();
    }, [pagination.current_page, search]);

    const fetchCollections = async () => {
        try {
            setLoading(true);
            const params = { page: pagination.current_page };
            if (search) params.search = search;

            const response = await CollectionService.getAll(params);
            setCollections(response.data?.data || response.data || []);
            if (response.data?.meta) setPagination(prev => ({ ...prev, ...response.data.meta }));
        } catch (err) {
            setError('Không thể tải danh sách bộ sưu tập');
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!deleteConfirm.item) return;
        setDeleting(true);
        try {
            await CollectionService.delete(deleteConfirm.item.uuid);
            setDeleteConfirm({ show: false, item: null });
            fetchCollections();
        } catch (err) {
            setError('Không thể xóa bộ sưu tập');
        } finally {
            setDeleting(false);
        }
    };

    return (
        <div className="collection-management">
            {/* Header */}
            <div className="page-header">
                <div className="header-content">
                    <h1><Layers size={28} /> Bộ sưu tập</h1>
                    <p>{pagination.total} bộ sưu tập</p>
                </div>
                <button onClick={() => navigate('/admin/collections/create')} className="btn btn-primary">
                    <Plus size={20} /> Thêm mới
                </button>
            </div>

            {/* Search */}
            <div className="search-bar">
                <Search size={18} />
                <input
                    type="text"
                    placeholder="Tìm kiếm bộ sưu tập..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
                <button onClick={fetchCollections} className="btn-refresh"><RefreshCw size={18} /></button>
            </div>

            {error && <div className="alert alert-error"><AlertCircle size={20} />{error}</div>}

            {/* Collections Grid */}
            <div className="collections-container">
                {loading ? (
                    <div className="loading-state"><div className="spinner"></div><p>Đang tải...</p></div>
                ) : collections.length === 0 ? (
                    <div className="empty-state">
                        <Layers size={48} />
                        <h3>Chưa có bộ sưu tập nào</h3>
                        <button onClick={() => navigate('/admin/collections/create')} className="btn btn-primary">
                            <Plus size={16} /> Tạo bộ sưu tập
                        </button>
                    </div>
                ) : (
                    <div className="collections-grid">
                        {collections.map(collection => (
                            <div key={collection.uuid} className="collection-card">
                                <div className="collection-image">
                                    {collection.image ? (
                                        <img src={collection.image} alt={collection.name} />
                                    ) : (
                                        <div className="placeholder"><Image size={32} /></div>
                                    )}
                                </div>
                                <div className="collection-info">
                                    <h3>{collection.name}</h3>
                                    <p>{collection.description || 'Không có mô tả'}</p>
                                    <div className="collection-meta">
                                        <span><Package size={14} /> {collection.products_count || 0} sản phẩm</span>
                                        <span className={`status ${collection.is_active ? 'active' : 'inactive'}`}>
                                            {collection.is_active ? 'Hiển thị' : 'Ẩn'}
                                        </span>
                                    </div>
                                </div>
                                <div className="collection-actions">
                                    <button
                                        onClick={() => navigate(`/admin/collections/${collection.uuid}/edit`)}
                                        className="btn-icon btn-edit"
                                    >
                                        <Edit size={16} />
                                    </button>
                                    <button
                                        onClick={() => setDeleteConfirm({ show: true, item: collection })}
                                        className="btn-icon btn-delete"
                                    >
                                        <Trash2 size={16} />
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Pagination */}
            {pagination.last_page > 1 && (
                <div className="pagination">
                    <button
                        onClick={() => setPagination(prev => ({ ...prev, current_page: prev.current_page - 1 }))}
                        disabled={pagination.current_page === 1}
                        className="btn-page"
                    >
                        <ChevronLeft size={18} />
                    </button>
                    <span>Trang {pagination.current_page} / {pagination.last_page}</span>
                    <button
                        onClick={() => setPagination(prev => ({ ...prev, current_page: prev.current_page + 1 }))}
                        disabled={pagination.current_page === pagination.last_page}
                        className="btn-page"
                    >
                        <ChevronRight size={18} />
                    </button>
                </div>
            )}

            <ConfirmDialog
                isOpen={deleteConfirm.show}
                onClose={() => setDeleteConfirm({ show: false, item: null })}
                onConfirm={handleDelete}
                title="Xác nhận xóa"
                message={`Bạn có chắc muốn xóa bộ sưu tập "${deleteConfirm.item?.name}"?`}
                confirmText="Xóa"
                type="danger"
                loading={deleting}
            />
        </div>
    );
};

export default CollectionList;
