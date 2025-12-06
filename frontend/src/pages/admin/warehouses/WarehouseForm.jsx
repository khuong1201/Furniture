import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Warehouse } from 'lucide-react';
import WarehouseService from '@/services/admin/WarehouseService';
import UserService from '@/services/admin/UserService';
import './WarehouseForm.css';

const WarehouseForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEditMode = !!uuid;

    const [loading, setLoading] = useState(false);
    const [users, setUsers] = useState([]);
    const [formData, setFormData] = useState({
        name: '',
        location: '',
        manager_id: ''
    });
    const [errors, setErrors] = useState({});

    useEffect(() => {
        fetchUsers();
        if (isEditMode) {
            fetchWarehouse();
        }
    }, [uuid]);

    const fetchUsers = async () => {
        try {
            const response = await UserService.getUsers();
            if (response.success && response.data) {
                const userList = Array.isArray(response.data) ? response.data : response.data.data || [];
                setUsers(userList);
            }
        } catch (error) {
            console.error('Error fetching users:', error);
        }
    };

    const fetchWarehouse = async () => {
        try {
            setLoading(true);
            const response = await WarehouseService.getWarehouse(uuid);
            if (response.success && response.data) {
                const warehouse = response.data;
                setFormData({
                    name: warehouse.name || '',
                    location: warehouse.location || '',
                    manager_id: warehouse.manager_id || ''
                });
            }
        } catch (error) {
            console.error('Error fetching warehouse:', error);
            alert('Không thể tải thông tin kho hàng');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        if (errors[name]) {
            setErrors(prev => ({ ...prev, [name]: null }));
        }
    };

    const validateForm = () => {
        const newErrors = {};

        if (!formData.name.trim()) {
            newErrors.name = 'Tên kho không được để trống';
        }

        if (!formData.location.trim()) {
            newErrors.location = 'Địa điểm không được để trống';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        try {
            setLoading(true);

            const payload = {
                name: formData.name,
                location: formData.location,
                manager_id: formData.manager_id || null
            };

            if (isEditMode) {
                await WarehouseService.updateWarehouse(uuid, payload);
                alert('Cập nhật kho hàng thành công!');
            } else {
                await WarehouseService.createWarehouse(payload);
                alert('Tạo kho hàng thành công!');
            }

            navigate('/admin/warehouses');
        } catch (error) {
            console.error('Error saving warehouse:', error);
            alert(error.message || 'Có lỗi xảy ra khi lưu kho hàng');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="warehouse_form">
            <div className="form-header">
                <h1><Warehouse size={24} /> {isEditMode ? 'Chỉnh sửa kho hàng' : 'Thêm kho hàng mới'}</h1>
                <button
                    type="button"
                    onClick={() => navigate('/admin/warehouses')}
                    className="btn-primary-warehouse"
                >
                    Quay lại
                </button>
            </div>

            <form onSubmit={handleSubmit} className="admin-form">
                <div className="form-section">
                    <h2>Thông tin kho hàng</h2>

                    <div className="form-group">
                        <label htmlFor="name">Tên kho <span className="required">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value={formData.name}
                            onChange={handleChange}
                            className={errors.name ? 'error' : ''}
                            placeholder="VD: Kho Hà Nội"
                        />
                        {errors.name && <span className="error-message">{errors.name}</span>}
                    </div>

                    <div className="form-group">
                        <label htmlFor="location">Địa điểm <span className="required">*</span></label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            value={formData.location}
                            onChange={handleChange}
                            className={errors.location ? 'error' : ''}
                            placeholder="VD: 123 Đường ABC, Quận XYZ, Hà Nội"
                        />
                        {errors.location && <span className="error-message">{errors.location}</span>}
                    </div>

                    <div className="form-group">
                        <label htmlFor="manager_id">Người quản lý</label>
                        <select
                            id="manager_id"
                            name="manager_id"
                            value={formData.manager_id}
                            onChange={handleChange}
                        >
                            <option value="">-- Chọn người quản lý --</option>
                            {users.map(user => (
                                <option key={user.id} value={user.id}>
                                    {user.name} ({user.email})
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="form-actions">
                    <button
                        type="button"
                        onClick={() => navigate('/admin/warehouses')}
                        className="btn btn-secondary"
                        disabled={loading}
                    >
                        Hủy
                    </button>
                    <button
                        type="submit"
                        className="btn btn-primary"
                        disabled={loading}
                    >
                        {loading ? 'Đang lưu...' : (isEditMode ? 'Cập nhật' : 'Tạo mới')}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default WarehouseForm;
