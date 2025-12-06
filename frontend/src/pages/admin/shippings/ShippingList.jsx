import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Truck, AlertCircle, Search, Eye, Trash2
} from 'lucide-react';
import ShippingService from '@/services/admin/ShippingService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
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
            setError('Không thể tải danh sách vận đơn');
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
            setError('Không thể xóa vận đơn');
        } finally {
            setDeleting(false);
        }
    };

    return (
        <div className="shipping_management">
            <div className="page-header">
                <div className="header-content">
                    <h1><Truck size={28} /> Quản lý Vận đơn</h1>
                    <p>{shippings.length} vận đơn</p>
                </div>
            </div>

            {error && <div className="alert alert-error"><AlertCircle size={20} />{error}</div>}

            <div className="shippings-container">
                {loading ? (
                    <div className="loading-state"><div className="spinner"></div><p>Đang tải...</p></div>
                ) : shippings.length === 0 ? (
                    <div className="empty-state">
                        <Truck size={48} />
                        <h3>Chưa có vận đơn nào</h3>
                    </div>
                ) : (
                    <div className="table-container">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Mã vận đơn</th>
                                    <th>Đơn vị vận chuyển</th>
                                    <th>Mã đơn hàng</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                {shippings.map(shipping => (
                                    <tr key={shipping.uuid}>
                                        <td className="font-medium">{shipping.tracking_number}</td>
                                        <td>{shipping.provider}</td>
                                        <td>
                                            <span className="text-sm text-gray-500">{shipping.order_uuid}</span>
                                        </td>
                                        <td>
                                            <span className={`status-badge status-${shipping.status}`}>
                                                {shipping.status}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="action-buttons">
                                                <button
                                                    onClick={() => navigate(`/admin/orders/${shipping.order_uuid}`)}
                                                    className="btn-icon"
                                                    title="Xem đơn hàng"
                                                >
                                                    <Eye size={18} />
                                                </button>
                                                <button
                                                    onClick={() => setDeleteConfirm({ show: true, item: shipping })}
                                                    className="btn-icon btn-delete"
                                                    title="Xóa vận đơn"
                                                >
                                                    <Trash2 size={18} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            <ConfirmDialog
                isOpen={deleteConfirm.show}
                onClose={() => setDeleteConfirm({ show: false, item: null })}
                onConfirm={handleDelete}
                title="Xác nhận xóa"
                message={`Bạn có chắc muốn xóa vận đơn "${deleteConfirm.item?.tracking_number}"?`}
                confirmText="Xóa"
                type="danger"
                loading={deleting}
            />
        </div>
    );
};

export default ShippingList;
