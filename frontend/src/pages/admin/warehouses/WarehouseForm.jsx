import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Warehouse, ArrowLeft, Save, Loader2, MapPin, User, Settings } from 'lucide-react';
import WarehouseService from '@/services/admin/WarehouseService';
import UserService from '@/services/admin/UserService';
import './WarehouseForm.css';

const WarehouseForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEditMode = !!uuid;

    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [users, setUsers] = useState([]);
    const [formData, setFormData] = useState({
        name: '',
        location: '',
        manager_id: '',
        is_active: true
    });
    const [errors, setErrors] = useState({});

    useEffect(() => {
        const fetchData = async () => {
            try {
                // 1. Load Users for Manager Select
                const userRes = await UserService.getUsers();
                if (userRes.success && userRes.data) {
                    const userList = Array.isArray(userRes.data) ? userRes.data : userRes.data.data || [];
                    setUsers(userList);
                }

                // 2. Load Warehouse Data if Edit
                if (isEditMode) {
                    setLoading(true);
                    const whRes = await WarehouseService.getWarehouse(uuid);
                    if (whRes.success && whRes.data) {
                        setFormData({
                            name: whRes.data.name || '',
                            location: whRes.data.location || '',
                            manager_id: whRes.data.manager_id || '',
                            is_active: !!whRes.data.is_active
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading data:', error);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [uuid, isEditMode]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
        if (errors[name]) setErrors(prev => ({ ...prev, [name]: null }));
    };

    const validateForm = () => {
        const newErrors = {};
        if (!formData.name.trim()) newErrors.name = 'Warehouse name is required';
        if (!formData.location.trim()) newErrors.location = 'Location is required';
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!validateForm()) return;

        setSubmitting(true);
        try {
            const payload = {
                name: formData.name,
                location: formData.location,
                manager_id: formData.manager_id || null,
                is_active: formData.is_active
            };

            if (isEditMode) {
                await WarehouseService.updateWarehouse(uuid, payload);
            } else {
                await WarehouseService.createWarehouse(payload);
            }
            navigate('/admin/warehouses');
        } catch (error) {
            console.error('Error saving warehouse:', error);
            alert(error.message || 'An error occurred');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) return <div className="p-8 text-center text-gray-500">Loading form...</div>;

    return (
        <div className="warehouse-form-page">
            
            {/* Header */}
            <div className="form-header-section">
                <div className="header-left">
                    <button onClick={() => navigate('/admin/inventory-manager?tab=warehouses')} className="btn-back">
                        <ArrowLeft size={18}/> Back
                    </button>
                    <h1>{isEditMode ? 'Edit Warehouse' : 'Create New Warehouse'}</h1>
                </div>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="form-card">
                    <div className="card-header">
                        <h3 className="card-title"><Warehouse /> General Information</h3>
                    </div>
                    <div className="card-body">
                        <div className="form-grid">
                            {/* Name */}
                            <div className="form-group">
                                <label className="form-label required">Warehouse Name</label>
                                <input
                                    type="text"
                                    name="name"
                                    className={`form-input ${errors.name ? 'error' : ''}`}
                                    value={formData.name}
                                    onChange={handleChange}
                                    placeholder="e.g. Central Hub"
                                />
                                {errors.name && <span className="error-message">{errors.name}</span>}
                            </div>

                            {/* Location */}
                            <div className="form-group">
                                <label className="form-label required">Location</label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        name="location"
                                        className={`form-input ${errors.location ? 'error' : ''}`}
                                        value={formData.location}
                                        onChange={handleChange}
                                        placeholder="e.g. 123 Main St, New York"
                                    />
                                </div>
                                {errors.location && <span className="error-message">{errors.location}</span>}
                            </div>

                            {/* Manager Select */}
                            <div className="form-group">
                                <label className="form-label">Manager</label>
                                <select
                                    name="manager_id"
                                    className="form-select"
                                    value={formData.manager_id}
                                    onChange={handleChange}
                                >
                                    <option value="">-- Select Manager --</option>
                                    {users.map(user => (
                                        <option key={user.id} value={user.id}>
                                            {user.name} ({user.email})
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Status Card */}
                <div className="form-card">
                    <div className="card-header">
                        <h3 className="card-title"><Settings /> Configuration</h3>
                    </div>
                    <div className="card-body">
                        <div className="status-row">
                            <span className="status-label">Active Status</span>
                            <label className="toggle-switch">
                                <input 
                                    type="checkbox" 
                                    name="is_active" 
                                    checked={formData.is_active} 
                                    onChange={handleChange} 
                                />
                                <span className="slider"></span>
                            </label>
                        </div>
                        <p className="status-helper">
                            {formData.is_active 
                                ? 'This warehouse is currently active and can store stock.' 
                                : 'This warehouse is disabled. No stock movements allowed.'}
                        </p>
                    </div>
                </div>

                {/* Actions */}
                <div className="form-actions">
                    <button type="button" onClick={() => navigate('/admin/inventory-manager?tab=warehouses')} className="btn-secondary" disabled={submitting}>
                        Cancel
                    </button>
                    <button type="submit" className="btn-primary-gradient" disabled={submitting}>
                        {submitting ? <Loader2 className="animate-spin" size={18}/> : <Save size={18}/>}
                        {isEditMode ? 'Save Changes' : 'Create Warehouse'}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default WarehouseForm;