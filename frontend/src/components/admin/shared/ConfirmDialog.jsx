import React from 'react';
import Modal from './Modal';
import { AlertTriangle, Info, CheckCircle, XCircle } from 'lucide-react';

const ConfirmDialog = ({
    isOpen,
    onClose,
    onConfirm,
    title = 'Xác nhận',
    message,
    confirmText = 'Xác nhận',
    cancelText = 'Hủy',
    type = 'danger', // danger, warning, info, success
    isLoading = false
}) => {
    const getIcon = () => {
        switch (type) {
            case 'danger':
                return <AlertTriangle size={48} className="text-danger" />;
            case 'warning':
                return <AlertTriangle size={48} className="text-warning" />;
            case 'success':
                return <CheckCircle size={48} className="text-success" />;
            case 'info':
            default:
                return <Info size={48} className="text-info" />;
        }
    };

    const getConfirmButtonClass = () => {
        switch (type) {
            case 'danger': return 'btn-danger';
            case 'warning': return 'btn-warning';
            case 'success': return 'btn-success';
            default: return 'btn-primary';
        }
    };

    return (
        <Modal
            isOpen={isOpen}
            onClose={onClose}
            title=""
            size="sm"
            closeOnOverlayClick={!isLoading}
        >
            <div className="confirm-dialog-content">
                <div className="confirm-icon">
                    {getIcon()}
                </div>
                <h3 className="confirm-title">{title}</h3>
                <p className="confirm-message">{message}</p>

                <div className="confirm-actions">
                    <button
                        className="btn btn-secondary"
                        onClick={onClose}
                        disabled={isLoading}
                    >
                        {cancelText}
                    </button>
                    <button
                        className={`btn ${getConfirmButtonClass()}`}
                        onClick={onConfirm}
                        disabled={isLoading}
                    >
                        {isLoading ? 'Đang xử lý...' : confirmText}
                    </button>
                </div>
            </div>
        </Modal>
    );
};

export default ConfirmDialog;
