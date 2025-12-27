import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save, Loader2, UploadCloud, X, Box, Layers, Settings, Image as ImageIcon } from 'lucide-react';
import BrandService from '@/services/admin/BrandService';
import { useBrand } from '@/hooks/admin/useBrand';
import LoadingSpinner from '@/components/admin/shared/LoadingSpinner';
import './BrandForm.css';

const BrandForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;
    const { getBrandDetail } = useBrand();

    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [preview, setPreview] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        slug: '',
        description: '',
        sort_order: 0, // Mặc định là 0, không hiện UI
        is_active: true,
        logo: null 
    });

    useEffect(() => {
        if (isEdit) {
            const fetchDetail = async () => {
                setLoading(true);
                try {
                    const data = await getBrandDetail(uuid);
                    if (data) {
                        setFormData({
                            name: data.name,
                            slug: data.slug,
                            description: data.description || '',
                            sort_order: data.sort_order || 0,
                            is_active: !!data.is_active,
                            logo: null
                        });
                        setPreview(data.logo_url);
                    }
                } catch (e) { console.error(e); } 
                finally { setLoading(false); }
            };
            fetchDetail();
        }
    }, [uuid]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setFormData(prev => ({ ...prev, logo: file }));
            setPreview(URL.createObjectURL(file));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        
        try {
            const payload = new FormData();
            payload.append('name', formData.name);
            if(formData.slug) payload.append('slug', formData.slug);
            if(formData.description) payload.append('description', formData.description);
            
            // Vẫn gửi sort_order mặc định để API không lỗi
            payload.append('sort_order', formData.sort_order); 
            payload.append('is_active', formData.is_active ? '1' : '0');
            
            if (formData.logo instanceof File) {
                payload.append('logo', formData.logo);
            }

            if (isEdit) {
                await BrandService.instance.updateBrand(uuid, payload);
            } else {
                await BrandService.instance.createBrand(payload);
            }
            
            navigate('/admin/product-manager?tab=brands');
        } catch (err) {
            alert(err.message || 'An error occurred');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) return <LoadingSpinner/>;

    return (
        <div className="brand-form-page">
            {/* Header */}
            <div className="form-header-section">
                <div className="header-left">
                    <button onClick={() => navigate('/admin/product-manager?tab=brands')} className="btn-back">
                        <ArrowLeft size={18} /> Back
                    </button>
                    <h1>{isEdit ? 'Edit Brand' : 'Create Brand'}</h1>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="form-layout">
                
                {/* --- Left Column (Main Info) --- */}
                <div className="form-column">
                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><Box /> General Information</h3></div>
                        <div className="card-body">
                            <div className="form-group">
                                <label className="form-label required">Brand Name</label>
                                <input 
                                    className="form-input" 
                                    name="name"
                                    required
                                    value={formData.name}
                                    onChange={handleChange}
                                    placeholder="e.g. Nike, Adidas"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Slug</label>
                                <input 
                                    className="form-input" 
                                    name="slug"
                                    value={formData.slug}
                                    onChange={handleChange}
                                    placeholder="Auto-generated if empty"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Description</label>
                                <textarea 
                                    className="form-textarea"
                                    name="description"
                                    rows={4}
                                    value={formData.description}
                                    onChange={handleChange}
                                    placeholder="Brand description..."
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* --- Right Column (Sidebar) --- */}
                <div className="form-column sidebar">
                    
                    {/* Logo Upload */}
                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><ImageIcon /> Logo</h3></div>
                        <div className="card-body">
                            <div className="logo-upload-wrapper">
                                {preview ? (
                                    <div className="logo-preview-container">
                                        <img src={preview} alt="Logo Preview" />
                                        <button 
                                            type="button" 
                                            className="btn-remove-logo"
                                            onClick={() => { setPreview(null); setFormData(p => ({...p, logo: null})) }}
                                        >
                                            <X size={14}/>
                                        </button>
                                    </div>
                                ) : (
                                    <div className="file-upload-box small">
                                        <input type="file" accept="image/*" className="file-input-hidden" onChange={handleFileChange} />
                                        <div className="upload-content">
                                            <UploadCloud className="upload-icon" />
                                            <div className="upload-text-main">Upload Logo</div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Settings - Đã ẩn Sort Order */}
                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><Settings /> Configuration</h3></div>
                        <div className="card-body">
                            <div className="status-row">
                                <span className="status-label">Active</span>
                                <label className="toggle-switch">
                                    <input 
                                        type="checkbox" 
                                        checked={formData.is_active} 
                                        onChange={e => setFormData(p => ({...p, is_active: e.target.checked}))} 
                                    />
                                    <span className="slider"></span>
                                </label>
                            </div>
                            <p className="status-helper">{formData.is_active ? 'Brand is visible.' : 'Brand is hidden.'}</p>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="sidebar-actions sticky bottom-4">
                        <button type="submit" className="btn-primary-gradient" disabled={submitting}>
                            {submitting ? <Loader2 className="animate-spin" size={18}/> : <Save size={18}/>}
                            {isEdit ? 'Save Changes' : 'Create Brand'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
};

export default BrandForm;