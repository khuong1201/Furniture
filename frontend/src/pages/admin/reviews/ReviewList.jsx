import React, { useEffect, useState } from 'react';
import {
    Star, Search, Trash2, Eye, AlertCircle, RefreshCw,
    ChevronLeft, ChevronRight, User, MessageSquare, ThumbsUp, ThumbsDown
} from 'lucide-react';
import ReviewService from '@/services/ReviewService';
import ConfirmDialog from '@/pages/admin/categories/ConfirmDialog';
import './ReviewManagement.css';

const ReviewList = () => {
    const [reviews, setReviews] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [filterRating, setFilterRating] = useState('');
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [deleteConfirm, setDeleteConfirm] = useState({ show: false, item: null });
    const [deleting, setDeleting] = useState(false);
    const [selectedReview, setSelectedReview] = useState(null);

    useEffect(() => {
        fetchReviews();
    }, [pagination.current_page, search, filterRating]);

    const fetchReviews = async () => {
        try {
            setLoading(true);
            const params = { page: pagination.current_page };
            if (search) params.search = search;
            if (filterRating) params.rating = filterRating;

            const response = await ReviewService.getAll(params);
            setReviews(response.data?.data || response.data || []);
            if (response.data?.meta) setPagination(prev => ({ ...prev, ...response.data.meta }));
        } catch (err) {
            setError('Không thể tải danh sách đánh giá');
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!deleteConfirm.item) return;
        setDeleting(true);
        try {
            await ReviewService.delete(deleteConfirm.item.uuid);
            setDeleteConfirm({ show: false, item: null });
            fetchReviews();
        } catch (err) {
            setError('Không thể xóa đánh giá');
        } finally {
            setDeleting(false);
        }
    };

    const renderStars = (rating) => (
        <div className="stars-display">
            {[1, 2, 3, 4, 5].map(i => (
                <Star key={i} size={14} className={i <= rating ? 'star-filled' : 'star-empty'} />
            ))}
        </div>
    );

    const formatDate = (dateString) => new Date(dateString).toLocaleDateString('vi-VN');

    return (
        <div className="review-management">
            {/* Header */}
            <div className="page-header">
                <div className="header-content">
                    <h1><Star size={28} /> Quản lý đánh giá</h1>
                    <p>{pagination.total} đánh giá từ khách hàng</p>
                </div>
            </div>

            {/* Filters */}
            <div className="filters-bar">
                <div className="search-box">
                    <Search size={18} />
                    <input
                        type="text"
                        placeholder="Tìm theo sản phẩm, người dùng..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                </div>
                <select
                    value={filterRating}
                    onChange={(e) => setFilterRating(e.target.value)}
                    className="filter-select"
                >
                    <option value="">Tất cả đánh giá</option>
                    <option value="5">5 sao</option>
                    <option value="4">4 sao</option>
                    <option value="3">3 sao</option>
                    <option value="2">2 sao</option>
                    <option value="1">1 sao</option>
                </select>
                <button onClick={fetchReviews} className="btn-refresh">
                    <RefreshCw size={18} />
                </button>
            </div>

            {error && <div className="alert alert-error"><AlertCircle size={20} />{error}</div>}

            {/* Reviews List */}
            <div className="reviews-container">
                {loading ? (
                    <div className="loading-state"><div className="spinner"></div><p>Đang tải...</p></div>
                ) : reviews.length === 0 ? (
                    <div className="empty-state">
                        <MessageSquare size={48} />
                        <h3>Chưa có đánh giá nào</h3>
                    </div>
                ) : (
                    <div className="reviews-list">
                        {reviews.map(review => (
                            <div key={review.uuid} className="review-card">
                                <div className="review-header">
                                    <div className="reviewer-info">
                                        <div className="avatar"><User size={18} /></div>
                                        <div>
                                            <strong>{review.user?.name || 'Ẩn danh'}</strong>
                                            <span>{formatDate(review.created_at)}</span>
                                        </div>
                                    </div>
                                    {renderStars(review.rating)}
                                </div>

                                <div className="review-product">
                                    <span>Sản phẩm:</span>
                                    <strong>{review.product?.name || 'N/A'}</strong>
                                </div>

                                <p className="review-content">{review.content || review.comment}</p>

                                <div className="review-footer">
                                    <div className="review-stats">
                                        <span><ThumbsUp size={14} /> {review.helpful_count || 0}</span>
                                    </div>
                                    <div className="review-actions">
                                        <button
                                            onClick={() => setSelectedReview(review)}
                                            className="btn-icon btn-view"
                                        >
                                            <Eye size={16} />
                                        </button>
                                        <button
                                            onClick={() => setDeleteConfirm({ show: true, item: review })}
                                            className="btn-icon btn-delete"
                                        >
                                            <Trash2 size={16} />
                                        </button>
                                    </div>
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

            {/* Review Detail Modal */}
            {selectedReview && (
                <div className="modal-overlay" onClick={() => setSelectedReview(null)}>
                    <div className="modal-content" onClick={e => e.stopPropagation()}>
                        <div className="modal-header">
                            <h3>Chi tiết đánh giá</h3>
                            <button onClick={() => setSelectedReview(null)}>&times;</button>
                        </div>
                        <div className="modal-body">
                            <div className="detail-row">
                                <label>Người đánh giá:</label>
                                <span>{selectedReview.user?.name}</span>
                            </div>
                            <div className="detail-row">
                                <label>Sản phẩm:</label>
                                <span>{selectedReview.product?.name}</span>
                            </div>
                            <div className="detail-row">
                                <label>Đánh giá:</label>
                                {renderStars(selectedReview.rating)}
                            </div>
                            <div className="detail-row">
                                <label>Nội dung:</label>
                                <p>{selectedReview.content || selectedReview.comment}</p>
                            </div>
                            <div className="detail-row">
                                <label>Ngày đăng:</label>
                                <span>{formatDate(selectedReview.created_at)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <ConfirmDialog
                isOpen={deleteConfirm.show}
                onClose={() => setDeleteConfirm({ show: false, item: null })}
                onConfirm={handleDelete}
                title="Xác nhận xóa"
                message="Bạn có chắc muốn xóa đánh giá này?"
                confirmText="Xóa"
                type="danger"
                loading={deleting}
            />
        </div>
    );
};

export default ReviewList;
