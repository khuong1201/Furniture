import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
    Plus,
    Search,
    Edit2,
    Trash2,
    Tag,
    Loader,
    AlertCircle
} from 'lucide-react';
import AttributeService from '@/services/admin/AttributeService';
import Modal from '@/components/admin/shared/Modal';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './AttributeList.css';

const AttributeList = () => {
    const [attributes, setAttributes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [searchTerm, setSearchTerm] = useState('');

    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedAttribute, setSelectedAttribute] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        type: 'text',
        values: []
    });
    const [valueInput, setValueInput] = useState('');
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        fetchAttributes();
    }, []);

    const fetchAttributes = async () => {
        try {
            setLoading(true);
            const response = await AttributeService.getAll();
            setAttributes(response.data || []);
        } catch (err) {
            setError('Không thể tải danh sách thuộc tính');
        } finally {
            setLoading(false);
        }
    };

    const handleOpenCreate = () => {
        setSelectedAttribute(null);
        setFormData({ name: '', type: 'text', values: [] });
        setValueInput('');
        setIsModalOpen(true);
    };

    const handleOpenEdit = (attr) => {
        setSelectedAttribute(attr);
        setFormData({
            name: attr.name || '',
            type: attr.type || 'text',
            values: attr.values || []
        });
        setIsModalOpen(true);
    };

    const handleOpenDelete = (attr) => {
        setSelectedAttribute(attr);
        setIsDeleteOpen(true);
    };

    const handleAddValue = () => {
        if (valueInput.trim() && !formData.values.includes(valueInput.trim())) {
            setFormData(prev => ({
                ...prev,
                values: [...prev.values, valueInput.trim()]
            }));
            setValueInput('');
        }
    };

    const handleRemoveValue = (value) => {
        setFormData(prev => ({
            ...prev,
            values: prev.values.filter(v => v !== value)
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (selectedAttribute) {
                await AttributeService.update(selectedAttribute.uuid, formData);
            } else {
                await AttributeService.create(formData);
            }
            setIsModalOpen(false);
            fetchAttributes();
        } catch (err) {
            setError(err.message || 'Có lỗi xảy ra');
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async () => {
        try {
            await AttributeService.delete(selectedAttribute.uuid);
            setIsDeleteOpen(false);
            fetchAttributes();
        } catch (err) {
            setError(err.message || 'Không thể xóa thuộc tính');
        }
    };

    const filteredAttributes = attributes.filter(attr =>
        attr.name?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (loading) {
        return (
            <div className="loading-state">
                <Loader className="spinner" size={40} />
                <p>Đang tải...</p>
            </div>
        );
    }

    return (
        <div className="attribute_list-page">
            <div className="page-header">
                <div className="page-title">
                    <h1>Quản lý Thuộc tính</h1>
                    <p className="page-subtitle">Quản lý các thuộc tính sản phẩm (màu sắc, kích thước, ...)</p>
                </div>
                <button className="btn btn-add" onClick={handleOpenCreate}>
                    <Plus size={20} />
                    Thêm thuộc tính
                </button>
            </div>

            {error && (
                <div className="error-alert">
                    <AlertCircle size={20} />
                    <span>{error}</span>
                    <button onClick={() => setError('')}>×</button>
                </div>
            )}

            <div className="search-filters">
                <div className="search-box">
                    <Search size={20} className="search-icon" />
                    <input
                        type="text"
                        placeholder="Tìm kiếm thuộc tính..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            <div className="attributes-grid">
                {filteredAttributes.length === 0 ? (
                    <div className="empty-state">
                        <Tag size={64} />
                        <h3>Chưa có thuộc tính nào</h3>
                        <p>Bắt đầu bằng cách thêm thuộc tính mới</p>
                    </div>
                ) : (
                    filteredAttributes.map((attr) => (
                        <div key={attr.uuid} className="attribute_card">
                            <div className="attribute_header">
                                <div className="attribute-icon">
                                    <Tag size={20} />
                                </div>
                                <div className="attribute_info">
                                    <h3>{attr.name}</h3>
                                    <span className="attribute-type">{attr.type}</span>
                                </div>
                                <div className="attribute_actions">
                                    <button className="action-btn btn-edit" onClick={() => handleOpenEdit(attr)}>
                                        <Edit2 size={16} />
                                    </button>
                                    <button className="action-btn btn-delete" onClick={() => handleOpenDelete(attr)}>
                                        <Trash2 size={16} />
                                    </button>
                                </div>
                            </div>
                            {attr.values && attr.values.length > 0 && (
                                <div className="attribute_values">
                                    {attr.values.map((value, i) => (
                                        <span key={i} className="value-tag">{value}</span>
                                    ))}
                                </div>
                            )}
                        </div>
                    ))
                )}
            </div>

            <Modal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                title={selectedAttribute ? 'Sửa thuộc tính' : 'Thêm thuộc tính mới'}
                size="sm"
            >
                <form onSubmit={handleSubmit}>
                    <div className="form-group">
                        <label>Tên thuộc tính *</label>
                        <input
                            type="text"
                            value={formData.name}
                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                            placeholder="vd: Màu sắc, Kích thước"
                            required
                        />
                    </div>
                    <div className="form-group">
                        <label>Loại</label>
                        <select
                            value={formData.type}
                            onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                        >
                            <option value="text">Text</option>
                            <option value="select">Select</option>
                            <option value="color">Color</option>
                        </select>
                    </div>
                    <div className="form-group">
                        <label>Giá trị</label>
                        <div className="value-input-group">
                            <input
                                type="text"
                                value={valueInput}
                                onChange={(e) => setValueInput(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), handleAddValue())}
                                placeholder="Nhập giá trị và Enter"
                            />
                            <button type="button" className="btn-add-value" onClick={handleAddValue}>
                                <Plus size={18} />
                            </button>
                        </div>
                        <div className="value-tags">
                            {formData.values.map((value, i) => (
                                <span key={i} className="value-tag">
                                    {value}
                                    <button type="button" onClick={() => handleRemoveValue(value)}>×</button>
                                </span>
                            ))}
                        </div>
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="modal-btn modal-btn-secondary" onClick={() => setIsModalOpen(false)}>
                            Hủy
                        </button>
                        <button type="submit" className="modal-btn modal-btn-primary" disabled={saving}>
                            {saving ? 'Đang lưu...' : (selectedAttribute ? 'Cập nhật' : 'Tạo mới')}
                        </button>
                    </div>
                </form>
            </Modal>

            <ConfirmDialog
                isOpen={isDeleteOpen}
                onClose={() => setIsDeleteOpen(false)}
                onConfirm={handleDelete}
                title="Xác nhận xóa"
                message={`Bạn có chắc muốn xóa thuộc tính "${selectedAttribute?.name}"?`}
                type="danger"
            />
        </div>
    );
};

export default AttributeList;
