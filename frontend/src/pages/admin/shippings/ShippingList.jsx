import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Truck, Plus, Edit, Trash2, AlertCircle, RefreshCw,
    MapPin, DollarSign, Clock
} from 'lucide-react';
import ShippingService from '@/services/ShippingService';
import ConfirmDialog from '@/pages/admin/categories/ConfirmDialog';
import './ShippingManagement.css';

const ShippingList = () => {
    const navigate = useNavigate();
    const [shippings, setShippings] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [deleteConfirm, setDeleteConfirm] = useState({ show: false, item: null });
    const [deleting, setDeleting] = useState(false);

    useEffect(() => { fetchShippings(); }, []);

    const fetchShippings = async () => {
        try {
            setLoading(true);
            const response = await ShippingService.getAll();
            setShippings(response.data?.data || response.data || []);
        } catch (err) {
            setError('Không thể tải danh sách vận chuyển');
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!deleteConfirm.item) return;
        setDeleting(true);
        try {
            await ShippingService.delete(deleteConfirm.item.uuid);
            setDeleteConfirm({ show: false, item: null });
            fetchShippings();
        } catch (err) {
            setError('Không thể xóa');
        } finally {
            setDeleting(false);
        }
    };

    const formatPrice = (price) => parseInt(price || 0).toLocaleString('vi-VN') + ' đ';

    return (
        <div className="shipping_management">
            <div className="page-header">
                <div className="header-content">
                    <h1><Truck size={28} /> Phương thức vận chuyển</h1>
                    <p>{shippings.length} phương thức</p>
                </div>
                <button onClick={() => navigate('/admin/shippings/create')} className="btn btn-primary">
                    <Plus size={20} /> Thêm mới
                </button>
            </div>

            {error && <div className="alert alert-error"><AlertCircle size={20} />{error}</div>}

            <div className="shippings-container">
                {loading ? (
                    <div className="loading-state"><div className="spinner"></div><p>Đang tải...</p></div>
                ) : shippings.length === 0 ? (
                    <div className="empty-state">
                        <Truck size={48} />
                        <h3>Chưa có phương thức vận chuyển</h3>
                        <button onClick={() => navigate('/admin/shippings/create')} className="btn btn-primary">
                            <Plus size={16} /> Thêm phương thức
                        </button>
                    </div>
                ) : (
                    <div className="shippings-grid">
                        {shippings.map(shipping => (
                            <div key={shipping.uuid} className="shipping_card">
                                <div className="shipping_icon">
                                    <Truck size={24} />
                                </div>
                                <div className="shipping_info">
                                    <h3>{shipping.name}</h3>
                                    <p>{shipping.description || 'Không có mô tả'}</p>

                                    <div className="shipping_details">
                                        <span><DollarSign size={14} /> {formatPrice(shipping.base_cost)}</span>
                                        <span><Clock size={14} /> {shipping.estimated_days || '2-5'} ngày</span>
                                        <span><MapPin size={14} /> {shipping.zones?.length || 'Toàn quốc'}</span>
                                    </div>

                                    <div className="shipping-status">
                                        <span className={`status ${shipping.is_active ? 'active' : 'inactive'}`}>
                                            {shipping.is_active ? 'Đang hoạt động' : 'Tạm dừng'}
                                        </span>
                                    </div>
                                </div>

                                <div className="shipping-actions">
                                    <button
                                        onClick={() => navigate(`/admin/shippings/${shipping.uuid}/edit`)}
                                        className="btn-icon btn-edit"
                                    >
                                        <Edit size={16} />
                                    </button>
                                    <button
                                        onClick={() => setDeleteConfirm({ show: true, item: shipping })}
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

            <ConfirmDialog
                isOpen={deleteConfirm.show}
                onClose={() => setDeleteConfirm({ show: false, item: null })}
                onConfirm={handleDelete}
                title="Xác nhận xóa"
                message={`Bạn có chắc muốn xóa phương thức "${deleteConfirm.item?.name}"?`}
                confirmText="Xóa"
                type="danger"
                loading={deleting}
            />
        </div>
    );
};

export default ShippingList;
