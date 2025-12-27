import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Check, X, Truck, PackageCheck, Eye, Loader2 } from 'lucide-react';
import OrderService from '@/services/admin/OrderService';

const OrderActions = ({ order, onUpdate, showDetailBtn = true }) => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);

    const formatStatus = (status) => {
        return status.charAt(0).toUpperCase() + status.slice(1);
    };

    const handleUpdateStatus = async (newStatus) => {
        const statusLabel = formatStatus(newStatus);
        
        const confirmMsg = newStatus === 'cancelled' 
            ? 'Are you sure you want to CANCEL this order?' 
            : `Update order status to "${statusLabel}"?`;

        if (!window.confirm(confirmMsg)) return;

        setLoading(true);
        try {
            let res;
            if (newStatus === 'cancelled') {
                res = await OrderService.instance.cancel(order.uuid);
            } else {
                res = await OrderService.instance.updateStatus(order.uuid, newStatus);
            }

            if (res) {
                if (onUpdate) onUpdate();
            }
        } catch (error) {
            console.error(error);
            alert(error.message || 'Action failed. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="action-row">
                <div className="btn-tool bg-gray-50 border-gray-200 cursor-not-allowed">
                    <Loader2 size={16} className="animate-spin text-gray-400" />
                </div>
            </div>
        );
    }

    return (
        <div className="action-row">
            {/* PENDING STATUS */}
            {order.status === 'pending' && (
                <>
                    <button 
                        onClick={() => handleUpdateStatus('processing')} 
                        className="btn-tool btn-approve" 
                        title="Approve Order"
                    >
                        <Check size={16} />
                    </button>
                    <button 
                        onClick={() => handleUpdateStatus('cancelled')} 
                        className="btn-tool btn-reject" 
                        title="Reject Order"
                    >
                        <X size={16} />
                    </button>
                </>
            )}

            {/* PROCESSING STATUS */}
            {order.status === 'processing' && (
                <button 
                    onClick={() => handleUpdateStatus('shipping')} 
                    className="btn-tool btn-ship" 
                    title="Ship Order"
                >
                    <Truck size={16} />
                </button>
            )}

            {/* SHIPPING STATUS */}
            {order.status === 'shipping' && (
                <button 
                    onClick={() => handleUpdateStatus('delivered')} 
                    className="btn-tool btn-complete" 
                    title="Mark as Delivered"
                >
                    <PackageCheck size={16} />
                </button>
            )}

            {/* VIEW DETAILS BUTTON */}
            {showDetailBtn && (
                <button 
                    className="btn-tool btn-detail" 
                    onClick={() => navigate(`/admin/orders/${order.uuid}`)}
                    title="View Details"
                >
                    <Eye size={16} />
                </button>
            )}
        </div>
    );
};

export default OrderActions;