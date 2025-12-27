import React, { useState } from 'react';
import { Check, X, RotateCcw, AlertCircle, Loader2, Eye } from 'lucide-react';
import PaymentService from '@/services/admin/PaymentService';

const PaymentActions = ({ payment, onUpdate, onViewDetail, showDetailBtn = true }) => {
    const [loading, setLoading] = useState(false);

    const handleUpdate = async (newStatus) => {
        const label = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        if (!window.confirm(`Change payment status to "${label}"?`)) return;

        setLoading(true);
        try {
            await PaymentService.instance.updateStatus(payment.uuid, newStatus);
            if (onUpdate) onUpdate();
        } catch (error) {
            alert(error.message || 'Action failed');
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
            {/* PENDING / UNPAID */}
            {['pending', 'unpaid'].includes(payment.status) && (
                <>
                    <button onClick={() => handleUpdate('paid')} className="btn-tool btn-approve" title="Mark as Paid">
                        <Check size={16} />
                    </button>
                    <button onClick={() => handleUpdate('failed')} className="btn-tool btn-reject" title="Mark as Failed">
                        <X size={16} />
                    </button>
                </>
            )}
            
            {/* PAID */}
            {payment.status === 'paid' && (
                <button onClick={() => handleUpdate('refunded')} className="btn-tool text-blue-600 border-blue-200 hover:bg-blue-50" title="Refund">
                    <RotateCcw size={16} />
                </button>
            )}

            {/* DETAIL */}
            {showDetailBtn && (
                <button className="btn-tool btn-detail" onClick={onViewDetail} title="View Details">
                    <Eye size={16} />
                </button>
            )}
        </div>
    );
};

export default PaymentActions;