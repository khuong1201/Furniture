import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import { AlertTriangle, Loader2, X, Info, CheckCircle } from 'lucide-react';
import './ConfirmDialog.css'; // Tạo file css riêng cho gọn

const ConfirmDialog = ({ 
    isOpen, 
    title, 
    message, 
    onConfirm, 
    onClose, 
    type = 'danger', 
    isLoading = false 
}) => {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (isOpen) {
            setVisible(true);
            document.body.style.overflow = 'hidden'; // Chặn cuộn body
        } else {
            const timer = setTimeout(() => setVisible(false), 200); // Đợi animation đóng
            document.body.style.overflow = 'unset';
            return () => clearTimeout(timer);
        }

        const handleEsc = (e) => {
            if (e.key === 'Escape' && !isLoading) onClose();
        };
        window.addEventListener('keydown', handleEsc);
        return () => {
            window.removeEventListener('keydown', handleEsc);
            document.body.style.overflow = 'unset';
        };
    }, [isOpen, onClose, isLoading]);

    if (!isOpen && !visible) return null;

    // Cấu hình Icon/Màu sắc
    const config = {
        danger: { icon: <AlertTriangle size={28} />, className: 'type-danger' },
        info:   { icon: <Info size={28} />, className: 'type-info' },
        success:{ icon: <CheckCircle size={28} />, className: 'type-success' }
    };
    const currentType = config[type] || config.danger;

    const modalContent = (
        <div className={`confirm-overlay ${isOpen ? 'open' : 'closing'}`} onClick={() => !isLoading && onClose()}>
            <div className="confirm-box" onClick={(e) => e.stopPropagation()}>
                {/* Nút đóng */}
                {!isLoading && (
                    <button className="btn-close-absolute" onClick={onClose}>
                        <X size={20} />
                    </button>
                )}

                <div className="confirm-body">
                    {/* Icon */}
                    <div className={`confirm-icon ${currentType.className}`}>
                        {currentType.icon}
                    </div>

                    {/* Content */}
                    <div className="confirm-text">
                        <h3>{title}</h3>
                        <p>{message}</p>
                    </div>
                </div>

                {/* Actions */}
                <div className="confirm-actions">
                    <button 
                        className="btn-cancel" 
                        onClick={onClose} 
                        disabled={isLoading}
                    >
                        Cancel
                    </button>
                    <button 
                        className={`btn-confirm ${currentType.className}`} 
                        onClick={onConfirm} 
                        disabled={isLoading}
                    >
                        {isLoading && <Loader2 size={16} className="animate-spin" />}
                        {isLoading ? 'Processing...' : 'Confirm'}
                    </button>
                </div>
            </div>
        </div>
    );

    return ReactDOM.createPortal(modalContent, document.body);
};

export default ConfirmDialog;