import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save, Loader2, Plus, X, Layers, List } from 'lucide-react';
import AttributeService from '@/services/admin/AttributeService';
import { useAttribute } from '@/hooks/admin/useAttribute';
import LoadingSpinner from '@/components/admin/shared/LoadingSpinner';
import './AttributeForm.css'; // Tạo file css riêng hoặc dùng chung ProductForm.css

const AttributeForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;
    const { getAttributeDetail } = useAttribute();

    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    // Form Data
    const [formData, setFormData] = useState({
        name: '',
        slug: '',
        type: 'select', // Mặc định
        values: [] // Mảng chứa các giá trị (Red, Blue...)
    });

    // State cho ô input thêm value
    const [newValue, setNewValue] = useState('');

    useEffect(() => {
        if (isEdit) {
            const fetchDetail = async () => {
                setLoading(true);
                const data = await getAttributeDetail(uuid);
                if (data) {
                    setFormData({
                        name: data.name,
                        slug: data.slug,
                        type: data.type || 'select',
                        // Giả sử API trả về values dạng mảng object [{id, value}, ...] hoặc mảng string
                        values: Array.isArray(data.values) 
                            ? data.values.map(v => (typeof v === 'object' ? v.value : v)) 
                            : []
                    });
                }
                setLoading(false);
            };
            fetchDetail();
        }
    }, [uuid]);

    // --- Handlers for Values ---
    const handleAddValue = (e) => {
        e?.preventDefault();
        const val = newValue.trim();
        if (val && !formData.values.includes(val)) {
            setFormData(prev => ({ ...prev, values: [...prev.values, val] }));
            setNewValue('');
        }
    };

    const handleRemoveValue = (index) => {
        setFormData(prev => ({
            ...prev,
            values: prev.values.filter((_, i) => i !== index)
        }));
    };

    // --- Submit ---
    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            const payload = { ...formData };
            
            if (isEdit) {
                await AttributeService.update(uuid, payload);
            } else {
                await AttributeService.create(payload);
            }
            navigate('/admin/attributes'); // Quay về trang list
        } catch (error) {
            alert("Error saving attribute");
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) return <LoadingSpinner />;

    return (
        <div className="attribute-form-page product-form-page"> {/* Reuse class layout */}
            
            {/* Header */}
            <div className="form-header-section">
                <div className="header-left">
                    <button onClick={() => navigate('/admin/attributes')} className="btn-back">
                        <ArrowLeft size={18} /> Back
                    </button>
                    <h1>{isEdit ? 'Edit Attribute' : 'Create Attribute'}</h1>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="form-layout">
                {/* Cột trái: Thông tin chính */}
                <div className="form-column">
                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><Layers size={18}/> Basic Information</h3></div>
                        <div className="card-body">
                            <div className="form-group">
                                <label className="form-label required">Attribute Name</label>
                                <input 
                                    className="form-input" 
                                    required
                                    value={formData.name}
                                    onChange={e => setFormData({...formData, name: e.target.value})}
                                    placeholder="e.g. Color, Size, Material"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Slug</label>
                                <input 
                                    className="form-input" 
                                    value={formData.slug}
                                    onChange={e => setFormData({...formData, slug: e.target.value})}
                                    placeholder="Auto-generated if empty"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label required">Type</label>
                                <select 
                                    className="form-select"
                                    value={formData.type}
                                    onChange={e => setFormData({...formData, type: e.target.value})}
                                >
                                    <option value="select">Select (Dropdown)</option>
                                    <option value="color">Color Swatch</option>
                                    <option value="button">Button/Label</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Cột phải: Quản lý Values */}
                <div className="form-column sidebar">
                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><List size={18}/> Attribute Values</h3></div>
                        <div className="card-body">
                            <div className="form-group">
                                <label className="form-label">Add Value</label>
                                <div style={{ display: 'flex', gap: '8px' }}>
                                    <input 
                                        className="form-input"
                                        value={newValue}
                                        onChange={e => setNewValue(e.target.value)}
                                        onKeyDown={e => e.key === 'Enter' && handleAddValue(e)}
                                        placeholder="Type & Press Enter"
                                    />
                                    <button 
                                        type="button" 
                                        onClick={handleAddValue}
                                        style={{ background: '#1e2532', color: '#c5a47e', border: 'none', borderRadius: '6px', width: '42px', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                    >
                                        <Plus size={18}/>
                                    </button>
                                </div>
                            </div>

                            {/* Danh sách Values */}
                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '12px', background: '#f9fafb', padding: '12px', borderRadius: '8px', border: '1px dashed #d1d5db', minHeight: '60px' }}>
                                {formData.values.length > 0 ? formData.values.map((val, idx) => (
                                    <span key={idx} style={{ background: 'white', border: '1px solid #e5e7eb', padding: '4px 10px', borderRadius: '20px', fontSize: '13px', display: 'flex', alignItems: 'center', gap: '6px', color: '#374151' }}>
                                        {val}
                                        <button type="button" onClick={() => handleRemoveValue(idx)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#9ca3af', display: 'flex' }}>
                                            <X size={14}/>
                                        </button>
                                    </span>
                                )) : (
                                    <span style={{ fontSize: '13px', color: '#9ca3af', fontStyle: 'italic' }}>No values added yet.</span>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="sidebar-actions sticky bottom-4">
                        <button type="submit" className="btn-primary-gradient" disabled={submitting}>
                            {submitting ? <Loader2 className="animate-spin" size={18}/> : <Save size={18}/>}
                            {isEdit ? 'Save Changes' : 'Create Attribute'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
};

export default AttributeForm;