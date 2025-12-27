import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Plus, Search, Edit, Trash2, Tag, Calendar, Percent,
    AlertCircle, RefreshCw, ChevronLeft, ChevronRight, Layers, DollarSign
} from 'lucide-react';
import { usePromotion } from '@/hooks/admin/usePromotion';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './PromotionList.css';

const PromotionList = () => {
    const navigate = useNavigate();
    const { 
        promotions, loading, error, pagination, 
        fetchPromotions, deletePromotion 
    } = usePromotion();

    const [search, setSearch] = useState('');
    const [deleteConfirm, setDeleteConfirm] = useState({ show: false, item: null });
    const [deleting, setDeleting] = useState(false);

    useEffect(() => {
        // Load data on mount
        loadData(1);
    }, []);

    const loadData = (page) => {
        fetchPromotions({
            page: page,
            per_page: pagination.per_page,
            search: search || undefined
        });
    };

    // Handle Search Enter
    const handleSearch = () => loadData(1);

    const handleDelete = async () => {
        if (!deleteConfirm.item) return;
        setDeleting(true);
        try {
            await deletePromotion(deleteConfirm.item.uuid);
            setDeleteConfirm({ show: false, item: null });
        } catch (err) {
            // Error logged in hook
        } finally {
            setDeleting(false);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '--';
        return new Date(dateString).toLocaleDateString('vi-VN', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    };

    const getStatus = (promo) => {
        const now = new Date();
        const start = new Date(promo.start_date);
        const end = new Date(promo.end_date);

        if (!promo.is_active) return { label: 'Tạm dừng', color: 'inactive' };
        if (now < start) return { label: 'Sắp diễn ra', color: 'upcoming' };
        if (now > end) return { label: 'Đã kết thúc', color: 'expired' };
        return { label: 'Đang chạy', color: 'active' };
    };

    return (
        <div className="promotion_management">
            {/* Header */}
            <div className="page-header">
                <div className="header-content">
                    <h1><Tag size={28} /> Quản lý khuyến mãi</h1>
                    <p>{pagination.total} chương trình khuyến mãi</p>
                </div>
                <button onClick={() => navigate('/admin/promotions/create')} className="btn-primary-promotion">
                    <Plus size={20} /> Thêm mới
                </button>
            </div>

            {/* Search */}
            <div className="search-bar">
                <Search size={20} />
                <input
                    type="text"
                    placeholder="Tìm kiếm khuyến mãi..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                />
                <button onClick={handleSearch} className="btn-refresh">
                    <RefreshCw size={18} />
                </button>
            </div>

            {error && <div className="alert alert-error"><AlertCircle size={20} /> {error}</div>}

            {/* Table */}
            <div className="table-container">
                {loading ? (
                    <div className="loading-state"><div className="spinner"></div><p>Đang tải dữ liệu...</p></div>
                ) : promotions.length === 0 ? (
                    <div className="empty-state">
                        <Tag size={48} />
                        <h3>Chưa có dữ liệu</h3>
                        <button onClick={() => navigate('/admin/promotions/create')} className="btn btn-primary mt-4"><Plus size={16} /> Tạo khuyến mãi đầu tiên</button>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Chương trình</th>
                                <th>Mức giảm</th>
                                <th>Thời gian</th>
                                <th>Số lượng</th>
                                <th>Trạng thái</th>
                                <th className="text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {promotions.map(promo => {
                                const status = getStatus(promo);
                                return (
                                    <tr key={promo.uuid}>
                                        <td>
                                            <div className="promo-name">
                                                <strong>{promo.name}</strong>
                                                {promo.description && <span className="line-clamp-1">{promo.description}</span>}
                                            </div>
                                        </td>
                                        <td>
                                            <div className="flex flex-col gap-1">
                                                <span className="promo-value">
                                                    {promo.type === 'percentage' 
                                                        ? <><Percent size={14} /> {promo.value}%</> 
                                                        : <><DollarSign size={14} /> {parseInt(promo.value).toLocaleString()}đ</>
                                                    }
                                                </span>
                                                <span className="text-xs text-gray-500">
                                                    {promo.min_order_value > 0 ? `Đơn > ${parseInt(promo.min_order_value).toLocaleString()}đ` : 'Không GT tối thiểu'}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div className="promo-dates text-xs flex-col gap-1">
                                                <span>{formatDate(promo.start_date)}</span>
                                                <span>{formatDate(promo.end_date)}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div className="flex items-center gap-1 text-sm text-gray-600">
                                                <Layers size={14}/> 
                                                {promo.quantity === 0 ? '∞' : `${promo.used_count || 0} / ${promo.quantity}`}
                                            </div>
                                        </td>
                                        <td>
                                            <span className={`status-badge status-${status.color}`}>{status.label}</span>
                                        </td>
                                        <td>
                                            <div className="action-buttons justify-end">
                                                <button onClick={() => navigate(`/admin/promotions/${promo.uuid}/edit`)} className="btn-icon btn-edit" title="Chỉnh sửa">
                                                    <Edit size={16} />
                                                </button>
                                                <button onClick={() => setDeleteConfirm({ show: true, item: promo })} className="btn-icon btn-delete" title="Xóa">
                                                    <Trash2 size={16} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Pagination */}
            {pagination.last_page > 1 && (
                <div className="pagination">
                    <button 
                        onClick={() => loadData(pagination.current_page - 1)} 
                        disabled={pagination.current_page === 1} 
                        className="btn-page"
                    >
                        <ChevronLeft size={18} />
                    </button>
                    <span className="page-info">Trang {pagination.current_page} / {pagination.last_page}</span>
                    <button 
                        onClick={() => loadData(pagination.current_page + 1)} 
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
                message={`Bạn có chắc muốn xóa khuyến mãi "${deleteConfirm.item?.name}" không? Hành động này không thể hoàn tác.`}
                confirmText="Xóa bỏ"
                type="danger"
                loading={deleting}
            />
        </div>
    );
};

export default PromotionList;