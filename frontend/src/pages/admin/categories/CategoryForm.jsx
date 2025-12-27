import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save, Loader2, AlertCircle, X, Image as ImageIcon, Layers } from 'lucide-react';
import CategoryService from '@/services/admin/CategoryService';
import CategoryTreeSelect from '@/components/admin/shared/CategoryTreeSelect';
import ImageUpload from '@/components/admin/shared/ImageUploader';
import './CategoryForm.css';

const CategoryForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;

    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState(null);
    const [treeData, setTreeData] = useState([]);
    
    const [formData, setFormData] = useState({
        id: null, name: '', slug: '', parent_id: '',
        description: '', is_active: true, image: null
    });

    useEffect(() => {
        const init = async () => {
            setLoading(true);
            try {
                const treeRes = await CategoryService.getCategoryTree();
                setTreeData(treeRes.data || []);
                if (isEdit) {
                    const res = await CategoryService.getCategory(uuid);
                    const cat = res.data;
                    setFormData({
                        id: cat.id, name: cat.name, slug: cat.slug,
                        parent_id: cat.parent_id || '', description: cat.description || '',
                        is_active: cat.is_active, image: cat.image
                    });
                }
            } catch (err) {
                setError(err.response?.data?.message || "Failed to load data");
            } finally {
                setLoading(false);
            }
        };
        init();
    }, [uuid, isEdit]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            const payload = new FormData();
            payload.append('name', formData.name);
            if (formData.slug) payload.append('slug', formData.slug);
            if (formData.parent_id) payload.append('parent_id', formData.parent_id);
            if (formData.description) payload.append('description', formData.description);
            payload.append('is_active', formData.is_active ? '1' : '0');
            if (formData.image instanceof File) payload.append('image', formData.image);

            if (isEdit) {
                payload.append('_method', 'PUT');
                await CategoryService.instance._request(`/admin/categories/${uuid}`, {
                    method: 'POST', body: payload, headers: { 'Content-Type': undefined }
                });
            } else {
                await CategoryService.createCategory(payload);
            }
            navigate('/admin/product-manager?tab=categories');
        } catch (err) {
            setError(err.response?.data?.message || "An error occurred.");
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) return <div className="loading-container"><Loader2 className="spinner-gold" /></div>;

    return (
        <div className="category-form-page">
            <div className="form-header-section">
                <button type="button" onClick={() => navigate('/admin/product-manager?tab=categories')} className="btn-back">
                    <ArrowLeft size={18} /> Back
                </button>
                <h1>{isEdit ? 'Update Category' : 'Create Category'}</h1>
            </div>

            {error && <div className="error-alert"><AlertCircle size={20} /><span>{error}</span></div>}

            <form onSubmit={handleSubmit} className="main-form">
                <div className="form-layout">
                    
                    {/* === CỘT TRÁI: Main Info === */}
                    <div className="form-column main">
                        <div className="form-card">
                            <div className="card-header">
                                <h3><Layers size={18} /> Basic Information</h3>
                            </div>
                            <div className="card-body">
                                {/* Name */}
                                <div className="form-group">
                                    <label className="form-label required">Category Name</label>
                                    <input 
                                        className="form-input" required
                                        value={formData.name}
                                        onChange={e => setFormData({...formData, name: e.target.value})}
                                        placeholder="e.g., Living Room"
                                    />
                                </div>

                                {/* Parent Category (Đã chuyển vào đây) */}
                                <CategoryTreeSelect 
                                    treeData={treeData}
                                    value={formData.parent_id}
                                    onChange={val => setFormData({...formData, parent_id: val})}
                                    currentId={formData.id}
                                />

                                {/* Slug */}
                                <div className="form-group">
                                    <label className="form-label">Slug</label>
                                    <input 
                                        className="form-input"
                                        value={formData.slug}
                                        onChange={e => setFormData({...formData, slug: e.target.value})}
                                        placeholder="Auto-generated if empty"
                                    />
                                </div>

                                {/* Description */}
                                <div className="form-group">
                                    <label className="form-label">Description</label>
                                    <textarea 
                                        className="form-textarea"
                                        value={formData.description}
                                        onChange={e => setFormData({...formData, description: e.target.value})}
                                        rows={6} placeholder="Enter description..."
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* === CỘT PHẢI: Settings & Media === */}
                    <div className="form-column sidebar">
                        
                        {/* 1. Status */}
                        <div className="form-card">
                            <div className="card-header">
                                <h3>Status</h3>
                            </div>
                            <div className="card-body">
                                <div className="status-toggle-row">
                                    <span className="status-label">Availability</span>
                                    <div 
                                        className={`toggle-switch ${formData.is_active ? 'on' : 'off'}`}
                                        onClick={() => setFormData({...formData, is_active: !formData.is_active})}
                                    >
                                        <div className="toggle-knob"></div>
                                    </div>
                                </div>
                                <p className={`status-helper ${formData.is_active ? 'active' : ''}`}>
                                    {formData.is_active ? 'Visible on store' : 'Hidden from store'}
                                </p>
                            </div>
                        </div>

                        {/* 2. Media */}
                        <div className="form-card">
                            <div className="card-header">
                                <h3><ImageIcon size={18} /> Media</h3>
                            </div>
                            <div className="card-body">
                                <ImageUpload 
                                    initialImage={formData.image}
                                    onChange={file => setFormData({...formData, image: file})}
                                />
                            </div>
                        </div>

                        {/* 3. Actions */}
                        <div className="sidebar-actions">
                            <button 
                                type="submit" 
                                className="btn-primary-gradient full-width" 
                                disabled={submitting}
                            >
                                {submitting ? <Loader2 className="animate-spin" size={18} /> : <Save size={18} />}
                                <span>{isEdit ? 'Save Changes' : 'Create Category'}</span>
                            </button>
                            
                            <button 
                                type="button" 
                                className="btn-secondary full-width" 
                                onClick={() => navigate('/admin/product-manager?tab=categories')}
                            >
                                <X size={18}/> Cancel
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    );
};

export default CategoryForm;