import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Plus, Search, Edit, Trash2, Tag, Calendar, Percent,
    AlertCircle, RefreshCw, ChevronLeft, ChevronRight
} from 'lucide-react';
import PromotionService from '@/services/PromotionService';
import ConfirmDialog from '@/pages/admin/categories/ConfirmDialog';
import './PromotionManagement.css';

const PromotionList = () => {
    const navigate = useNavigate();
    const [promotions, setPromotions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0
    });
    const [deleteConfirm, setDeleteConfirm] = useState({ show: false, item: null });
    const [deleting, setDeleting] = useState(false);

    useEffect(() => {
        fetchPromotions();
    }, [pagination.current_page, search]);

    const fetchPromotions = async () => {
        try {
            setLoading(true);
            const response = await PromotionService.getAll({
                page: pagination.current_page,
                per_page: pagination.per_page,
                search: search || undefined
            });
            setPromotions(response.data?.data || response.data || []);
            if (response.data?.meta) {
                setPagination(prev => ({
                    ...prev,
                    ...response.data.meta
                }));
            }
        } catch (err) {
            setError('Không thể tải danh sách khuyến mãi');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!deleteConfirm.item) return;

        setDeleting(true);
        try {
            await PromotionService.delete(deleteConfirm.item.uuid);
            setDeleteConfirm({ show: false, item: null });
            fetchPromotions();
        } catch (err) {
            setError('Không thể xóa khuyến mãi');
        } finally {
            setDeleting(false);
        }
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const getStatus = (promo) => {
        const now = new Date();
        const start = new Date(promo.start_date);
        const end = new Date(promo.end_date);

        if (!promo.status) return { label: 'Không hoạt động', color: 'inactive' };
        if (now < start) return { label: 'Sắp diễn ra', color: 'upcoming' };
        if (now > end) return { label: 'Đã kết thúc', color: 'expired' };
        return { label: 'Đang hoạt động', color: 'active' };
    };

    return (
        <div className="promotion_management">
            {/* Header */}
            <div className="page-header">
                <div className="header-content">
                    <h1>
                        <Tag size={28} />
                        Quản lý khuyến mãi
                    </h1>
                    <p>{pagination.total} chương trình khuyến mãi</p>
                </div>
                <button onClick={() => navigate('/admin/promotions/create')} className="btn btn-primary">
                    <Plus size={20} />
                    Thêm khuyến mãi
                </button>
            </div>

            {/* Search */}
            <div className="search-bar">
                <Search size={20} />
                <input
                    type="text"
                    placeholder="Tìm kiếm theo tên khuyến mãi..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
                <button onClick={fetchPromotions} className="btn-refresh">
                    <RefreshCw size={18} />
                </button>
            </div>

            {error && (
                <div className="alert alert-error">
                    <AlertCircle size={20} />
                    {error}
                </div>
            )}

            {/* Table */}
            <div className="table-container">
                {loading ? (
                    <div className="loading-state">
                        <div className="spinner"></div>
                        <p>Đang tải...</p>
                    </div>
                ) : promotions.length === 0 ? (
                    <div className="empty-state">
                        <Tag size={48} />
                        <h3>Chưa có khuyến mãi nào</h3>
                        <p>Tạo khuyến mãi đầu tiên để bắt đầu</p>
                        <button onClick={() => navigate('/admin/promotions/create')} className="btn btn-primary">
                            <Plus size={16} />
                            Tạo khuyến mãi
                        </button>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Tên chương trình</th>
                                <th>Loại</th>
                                <th>Giá trị</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
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
                                                {promo.description && (
                                                    <span>{promo.description}</span>
                                                )}
                                            </div>
                                        </td>
                                        <td>
                                            <span className="promo-type">
                                                {promo.type === 'percentage' ? 'Phần trăm' : 'Cố định'}
                                            </span>
                                        </td>
                                        <td>
                                            <span className="promo-value">
                                                {promo.type === 'percentage' ? (
                                                    <><Percent size={14} /> {promo.value}%</>
                                                ) : (
                                                    <>{parseInt(promo.value).toLocaleString('vi-VN')} đ</>
                                                )}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="promo-dates">
                                                <Calendar size={14} />
                                                <span>{formatDate(promo.start_date)} - {formatDate(promo.end_date)}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span className={`status-badge status-${status.color}`}>
                                                {status.label}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="action-buttons">
                                                <button
                                                    onClick={() => navigate(`/admin/promotions/${promo.uuid}/edit`)}
                                                    className="btn-icon btn-edit"
                                                    title="Sửa"
                                                >
                                                    <Edit size={16} />
                                                </button>
                                                <button
                                                    onClick={() => setDeleteConfirm({ show: true, item: promo })}
                                                    className="btn-icon btn-delete"
                                                    title="Xóa"
                                                >
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
                        onClick={() => setPagination(prev => ({ ...prev, current_page: prev.current_page - 1 }))}
                        disabled={pagination.current_page === 1}
                        className="btn-page"
                    >
                        <ChevronLeft size={18} />
                    </button>
                    <span className="page-info">
                        Trang {pagination.current_page} / {pagination.last_page}
                    </span>
                    <button
                        onClick={() => setPagination(prev => ({ ...prev, current_page: prev.current_page + 1 }))}
                        disabled={pagination.current_page === pagination.last_page}
                        className="btn-page"
                    >
                        <ChevronRight size={18} />
                    </button>
                </div>
            )}

            {/* Delete Confirmation */}
            <ConfirmDialog
                isOpen={deleteConfirm.show}
                onClose={() => setDeleteConfirm({ show: false, item: null })}
                onConfirm={handleDelete}
                title="Xác nhận xóa"
                message={`Bạn có chắc muốn xóa khuyến mãi "${deleteConfirm.item?.name}"?`}
                confirmText="Xóa"
                type="danger"
                loading={deleting}
            />
        </div>
    );
};

export default PromotionList;
