import React, { useEffect, useState } from 'react';
import { Search, Edit, Trash2, X, Tag, RefreshCw, Layers, Plus } from 'lucide-react'; // Đã bỏ Plus ở header
import { useAttribute } from '@/hooks/admin/useAttribute';
import AttributeService from '@/services/admin/AttributeService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import LoadingSpinner from '@/components/admin/shared/LoadingSpinner';
import './AttributeList.css';

// Nhận props từ ProductManager
const AttributeList = ({ externalModalOpen, setExternalModalOpen }) => {
    const { attributes, loading, fetchAttributes } = useAttribute();
    const [searchTerm, setSearchTerm] = useState('');
    const [isRefreshing, setIsRefreshing] = useState(false);
    
    // Modal state
    // Lưu ý: isModalOpen nội bộ vẫn dùng để handle logic đóng/mở
    const [isInternalModalOpen, setIsInternalModalOpen] = useState(false);
    
    const [editingAttribute, setEditingAttribute] = useState(null);
    const [formData, setFormData] = useState({ name: '', type: 'text', values: [] });
    const [newValue, setNewValue] = useState('');
    const [deleteDialog, setDeleteDialog] = useState({ isOpen: false, item: null });

    useEffect(() => {
        fetchAttributes();
    }, [fetchAttributes]);

    // --- EFFECT: Lắng nghe lệnh mở từ ProductManager ---
    useEffect(() => {
        if (externalModalOpen) {
            handleOpenModal(); // Mở form tạo mới
        }
    }, [externalModalOpen]);

    // --- Handlers ---
    const handleCloseModal = () => {
        setIsInternalModalOpen(false);
        // Báo ngược lại cho Parent biết là đã đóng
        if (setExternalModalOpen) setExternalModalOpen(false);
    };

    const handleOpenModal = (attribute = null) => {
        if (attribute) {
            setEditingAttribute(attribute);
            setFormData({
                name: attribute.name,
                type: attribute.type,
                values: attribute.values ? attribute.values.map(v => v.value) : []
            });
        } else {
            setEditingAttribute(null);
            setFormData({ name: '', type: 'text', values: [] });
        }
        setIsInternalModalOpen(true);
    };

    const handleAddValue = (e) => {
        e?.preventDefault();
        if (newValue.trim() && !formData.values.includes(newValue.trim())) {
            setFormData(prev => ({ ...prev, values: [...prev.values, newValue.trim()] }));
            setNewValue('');
        }
    };

    const handleRemoveValue = (index) => {
        setFormData(prev => ({ ...prev, values: prev.values.filter((_, i) => i !== index) }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const payload = { ...formData };
            if (editingAttribute) {
                await AttributeService.update(editingAttribute.uuid, payload);
            } else {
                await AttributeService.create(payload);
            }
            fetchAttributes();
            handleCloseModal();
        } catch (error) {
            alert('Failed to save attribute');
        }
    };

    const handleDelete = async () => {
        if (!deleteDialog.item) return;
        try {
            await AttributeService.delete(deleteDialog.item.uuid);
            fetchAttributes();
            setDeleteDialog({ isOpen: false, item: null });
        } catch (error) {
            alert('Failed to delete attribute');
        }
    };

    const filteredAttributes = attributes.filter(attr => 
        attr.name.toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (loading && attributes.length === 0 && !isRefreshing) return <LoadingSpinner />;

    return (
        <div className="attribute_list-page">
            {/* Header đã xóa vì ProductManager quản lý rồi */}
            
            {/* Chỉ giữ lại Filter Bar */}
            <div className="filter-bar">
                <div className="search-group">
                    <Search className="search-icon" size={18} />
                    <input 
                        className="filter-input"
                        type="text" 
                        placeholder="Search attributes..." 
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
                {/* Nút refresh */}
                <button className="btn-refresh" onClick={() => fetchAttributes()}>
                    <RefreshCw size={18} />
                </button>
            </div>

            {/* Grid Content */}
            <div className="attributes-grid-container">
                {filteredAttributes.length === 0 ? (
                    <div className="empty-state">
                        <Tag size={48} />
                        <h3>No attributes found</h3>
                        <p>Click "Create Attribute" above to start.</p>
                    </div>
                ) : (
                    <div className="attributes-grid">
                        {filteredAttributes.map(attr => (
                            <div key={attr.uuid} className="attribute-card">
                                <div className="attr-card-header">
                                    <div className="attr-info">
                                        <div className="attr-icon"><Tag size={18}/></div>
                                        <div>
                                            <h3 className="attr-name">{attr.name}</h3>
                                            <span className="attr-slug">{attr.slug}</span>
                                        </div>
                                    </div>
                                    <div className="attr-actions">
                                        <button className="btn-icon-small" onClick={() => handleOpenModal(attr)}>
                                            <Edit size={16} />
                                        </button>
                                        <button className="btn-icon-small danger" onClick={() => setDeleteDialog({ isOpen: true, item: attr })}>
                                            <Trash2 size={16} />
                                        </button>
                                    </div>
                                </div>
                                <div className="attr-values-area">
                                    <span className="values-label">Values:</span>
                                    <div className="values-list">
                                        {attr.values?.slice(0, 5).map((val, idx) => (
                                            <span key={idx} className="value-chip">{val.value}</span>
                                        ))}
                                        {attr.values?.length > 5 && <span className="value-chip more">+{attr.values.length - 5}</span>}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Modal Form */}
            {isInternalModalOpen && (
                <div className="modal-overlay">
                    <div className="modal-container">
                        <div className="modal-header">
                            <h3>{editingAttribute ? 'Edit Attribute' : 'Create Attribute'}</h3>
                            <button className="btn-close" onClick={handleCloseModal}><X size={20}/></button>
                        </div>
                        <form onSubmit={handleSubmit} className="modal-body">
                            <div className="form-group">
                                <label className="form-label required">Attribute Name</label>
                                <input className="form-input" required value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} placeholder="e.g. Color"/>
                            </div>
                            
                            <div className="form-group">
                                <label className="form-label">Values</label>
                                <div className="input-with-button">
                                    <input className="form-input" value={newValue} onChange={(e) => setNewValue(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleAddValue(e)} placeholder="Type value & Enter"/>
                                    <button type="button" className="btn-add-inline" onClick={handleAddValue}><Plus size={18}/></button>
                                </div>
                                <div className="values-container-edit">
                                    {formData.values.map((val, idx) => (
                                        <div key={idx} className="value-chip-edit">
                                            <span>{val}</span>
                                            <button type="button" onClick={() => handleRemoveValue(idx)}><X size={12}/></button>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="modal-footer">
                                <button type="button" className="btn-secondary" onClick={handleCloseModal}>Cancel</button>
                                <button type="submit" className="btn-primary-gradient">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            <ConfirmDialog isOpen={deleteDialog.isOpen} onClose={() => setDeleteDialog({ isOpen: false, item: null })} onConfirm={handleDelete} title="Delete" message="Are you sure?" type="danger"/>
        </div>
    );
};

export default AttributeList;