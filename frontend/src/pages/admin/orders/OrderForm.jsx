import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft, Plus, Trash2, Save, Search, User, MapPin, Package } from 'lucide-react';
import OrderService from '../../../services/admin/OrderService';
import UserService from '../../../services/admin/UserService';
import AddressService from '../../../services/admin/AddressService';
import ProductService from '../../../services/admin/ProductService';
import './OrderForm.css';

const OrderForm = () => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Data states
    const [users, setUsers] = useState([]);
    const [addresses, setAddresses] = useState([]);
    const [products, setProducts] = useState([]);

    // Search states
    const [userSearch, setUserSearch] = useState('');
    const [productSearch, setProductSearch] = useState('');
    const [showUserDropdown, setShowUserDropdown] = useState(false);
    const [showProductDropdown, setShowProductDropdown] = useState(false);

    // Form states
    const [formData, setFormData] = useState({
        user_id: null,
        address_id: '',
        notes: '',
        items: []
    });

    const [selectedUser, setSelectedUser] = useState(null);

    // Fetch users for autocomplete
    useEffect(() => {
        const fetchUsers = async () => {
            try {
                const response = await UserService.getUsers({ q: userSearch, per_page: 5 });
                if (response.success) {
                    setUsers(response.data?.data || []);
                }
            } catch (err) {
                console.error('Error fetching users:', err);
            }
        };

        const timeoutId = setTimeout(() => {
            fetchUsers();
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [userSearch]);

    // Fetch products for autocomplete
    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await ProductService.getProducts({ q: productSearch, per_page: 5 });
                if (response.success) {
                    setProducts(response.data?.data || []);
                }
            } catch (err) {
                console.error('Error fetching products:', err);
            }
        };

        const timeoutId = setTimeout(() => {
            if (productSearch) fetchProducts();
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [productSearch]);

    // Fetch addresses when user is selected
    useEffect(() => {
        if (formData.user_id) {
            const fetchAddresses = async () => {
                try {
                    const response = await AddressService.getAddresses({ user_id: formData.user_id });
                    if (response.success) {
                        setAddresses(response.data);
                        // Auto select default address
                        const defaultAddr = response.data.find(a => a.is_default);
                        if (defaultAddr) {
                            setFormData(prev => ({ ...prev, address_id: defaultAddr.id }));
                        }
                    }
                } catch (err) {
                    console.error('Error fetching addresses:', err);
                }
            };
            fetchAddresses();
        } else {
            setAddresses([]);
            setFormData(prev => ({ ...prev, address_id: '' }));
        }
    }, [formData.user_id]);

    const handleSelectUser = (user) => {
        setFormData(prev => ({ ...prev, user_id: user.id }));
        setSelectedUser(user);
        setUserSearch(user.name);
        setShowUserDropdown(false);
    };

    const handleAddProduct = (product, variant) => {
        setFormData(prev => {
            const existingItemIndex = prev.items.findIndex(item => item.variant_uuid === variant.uuid);

            if (existingItemIndex >= 0) {
                const newItems = [...prev.items];
                newItems[existingItemIndex].quantity += 1;
                return { ...prev, items: newItems };
            }

            return {
                ...prev,
                items: [...prev.items, {
                    variant_uuid: variant.uuid,
                    product_name: product.name,
                    variant_name: variant.sku, // Or construct a better name
                    price: variant.price,
                    quantity: 1,
                    image: product.thumbnail
                }]
            };
        });
        setShowProductDropdown(false);
        setProductSearch('');
    };

    const handleUpdateQuantity = (index, newQuantity) => {
        if (newQuantity < 1) return;
        setFormData(prev => {
            const newItems = [...prev.items];
            newItems[index].quantity = newQuantity;
            return { ...prev, items: newItems };
        });
    };

    const handleRemoveItem = (index) => {
        setFormData(prev => {
            const newItems = [...prev.items];
            newItems.splice(index, 1);
            return { ...prev, items: newItems };
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            if (!formData.user_id) throw new Error('Vui lòng chọn khách hàng');
            if (!formData.address_id) throw new Error('Vui lòng chọn địa chỉ giao hàng');
            if (formData.items.length === 0) throw new Error('Vui lòng thêm sản phẩm vào đơn hàng');

            await OrderService.createOrder({
                user_id: formData.user_id,
                address_id: formData.address_id,
                notes: formData.notes,
                items: formData.items.map(item => ({
                    variant_uuid: item.variant_uuid,
                    quantity: item.quantity
                }))
            });

            navigate('/admin/orders');
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    const calculateTotal = () => {
        return formData.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    };

    // Quick User Creation State
    const [showUserModal, setShowUserModal] = useState(false);
    const [newUser, setNewUser] = useState({ name: '', email: '', phone: '', password: '' });
    const [creatingUser, setCreatingUser] = useState(false);

    const handleCreateUser = async (e) => {
        e.preventDefault();
        setCreatingUser(true);
        try {
            const response = await UserService.createUser({
                ...newUser,
                is_active: true,
                password: newUser.password || '12345678' // Default password if not provided (optional UX choice)
            });

            if (response.success || response.data) {
                const createdUser = response.data;
                setUsers([createdUser]); // Set as the only option or add to list
                handleSelectUser(createdUser); // Auto select
                setShowUserModal(false);
                setNewUser({ name: '', email: '', phone: '', password: '' });
            }
        } catch (err) {
            alert(err.message || 'Không thể tạo khách hàng');
        } finally {
            setCreatingUser(false);
        }
    };

    return (
        <div className="admin-app-container">
            <div className="header-content">
                <button onClick={() => navigate('/admin/orders')} className="btn-back">
                    <ArrowLeft size={20} />
                </button>
                <div className="header-title">
                    <h1>Tạo đơn hàng mới</h1>
                </div>
            </div>

            <div className="order-form-container">
                {error && <div className="alert alert-error">{error}</div>}

                <form onSubmit={handleSubmit} className="order-form">
                    <div className="form-section">
                        <div className="flex justify-between items-center mb-4">
                            <h3><User size={20} /> Thông tin khách hàng</h3>
                            <button
                                type="button"
                                onClick={() => setShowUserModal(true)}
                                className="add-customer"
                            >
                                <Plus size={16} /> Thêm khách hàng nhanh
                            </button>
                        </div>

                        <div className="form-group relative">
                            <label>Khách hàng</label>
                            <div className="search-input-wrapper">
                                <Search size={18} className="search-icon" />
                                <input
                                    type="text"
                                    placeholder="Tìm kiếm khách hàng..."
                                    value={userSearch}
                                    onChange={(e) => {
                                        setUserSearch(e.target.value);
                                        setShowUserDropdown(true);
                                    }}
                                    onFocus={() => setShowUserDropdown(true)}
                                    className="form-control"
                                />
                            </div>

                            {showUserDropdown && users.length > 0 && (
                                <div className="dropdown-results">
                                    {users.map(user => (
                                        <div
                                            key={user.id}
                                            className="dropdown-item"
                                            onClick={() => handleSelectUser(user)}
                                        >
                                            <div className="user-info">
                                                <span className="user-name">{user.name}</span>
                                                <span className="user-email">{user.email}</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                            {showUserDropdown && users.length === 0 && userSearch && (
                                <div className="dropdown-results">
                                    <div className="p-3 text-center text-gray-500">
                                        Không tìm thấy khách hàng.
                                        <button type="button" onClick={() => setShowUserModal(true)} className="text-blue-600 ml-1 hover:underline">
                                            Tạo mới?
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {selectedUser && (
                            <div className="form-group">
                                <label><MapPin size={16} /> Địa chỉ giao hàng</label>
                                <select
                                    className="form-control"
                                    value={formData.address_id}
                                    onChange={(e) => setFormData({ ...formData, address_id: e.target.value })}
                                >
                                    <option value="">-- Chọn địa chỉ --</option>
                                    {addresses.map(addr => (
                                        <option key={addr.id} value={addr.id}>
                                            {addr.full_name} - {addr.phone} - {addr.street}, {addr.ward}, {addr.district}, {addr.province}
                                        </option>
                                    ))}
                                </select>
                                {addresses.length === 0 && (
                                    <div className="mt-2 text-sm text-orange-600 flex items-center gap-2">
                                        <AlertCircle size={14} />
                                        Khách hàng này chưa có địa chỉ.
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="form-group">
                            <label>Ghi chú</label>
                            <textarea
                                className="form-control"
                                rows="3"
                                value={formData.notes}
                                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                                placeholder="Ghi chú cho đơn hàng..."
                            ></textarea>
                        </div>
                    </div>

                    <div className="form-section">
                        <h3><Package size={20} /> Sản phẩm</h3>

                        <div className="form-group relative">
                            <div className="search-input-wrapper">
                                <Search size={18} className="search-icon" />
                                <input
                                    type="text"
                                    placeholder="Tìm kiếm sản phẩm để thêm..."
                                    value={productSearch}
                                    onChange={(e) => {
                                        setProductSearch(e.target.value);
                                        setShowProductDropdown(true);
                                    }}
                                    onFocus={() => setShowProductDropdown(true)}
                                    className="form-control"
                                />
                            </div>

                            {showProductDropdown && products.length > 0 && (
                                <div className="dropdown-results products-dropdown">
                                    {products.map(product => (
                                        <div key={product.id} className="product-item-group">
                                            <div className="product-header">
                                                <img src={product.thumbnail} alt="" className="w-8 h-8 object-cover rounded" />
                                                <span>{product.name}</span>
                                            </div>
                                            <div className="variants-list">
                                                {product.variants?.map(variant => (
                                                    <div
                                                        key={variant.uuid}
                                                        className="variant-item"
                                                        onClick={() => handleAddProduct(product, variant)}
                                                    >
                                                        <span>{variant.sku}</span>
                                                        <span>{new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(variant.price)}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="order-items-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Đơn giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {formData.items.map((item, index) => (
                                        <tr key={`${item.variant_uuid}-${index}`}>
                                            <td>
                                                <div className="item-info">
                                                    <img src={item.image} alt="" />
                                                    <div>
                                                        <div className="item-name">{item.product_name}</div>
                                                        <div className="item-variant">{item.variant_name}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price)}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    value={item.quantity}
                                                    onChange={(e) => handleUpdateQuantity(index, parseInt(e.target.value))}
                                                    className="qty-input"
                                                />
                                            </td>
                                            <td>{new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price * item.quantity)}</td>
                                            <td>
                                                <button type="button" onClick={() => handleRemoveItem(index)} className="btn-icon delete">
                                                    <Trash2 size={18} />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {formData.items.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="text-center py-4 text-gray-500">Chưa có sản phẩm nào</td>
                                        </tr>
                                    )}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colSpan="3" className="text-right font-bold">Tổng cộng:</td>
                                        <td className="font-bold text-lg text-primary">
                                            {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(calculateTotal())}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div className="form-actions">
                        <button type="button" onClick={() => navigate('/admin/orders')} className="btn btn-secondary">
                            Hủy bỏ
                        </button>
                        <button type="submit" className="btn btn-primary" disabled={loading}>
                            {loading ? 'Đang xử lý...' : <><Save size={20} /> Tạo đơn hàng</>}
                        </button>
                    </div>
                </form>

                {/* Quick User Modal */}
                {showUserModal && (
                    <div className="modal-overlay">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h3>Thêm khách hàng nhanh</h3>
                            </div>
                            <form onSubmit={handleCreateUser}>
                                <div className="modal-body">
                                    <div className="form-group">
                                        <label>Họ tên *</label>
                                        <input
                                            type="text"
                                            required
                                            value={newUser.name}
                                            onChange={e => setNewUser({ ...newUser, name: e.target.value })}
                                            placeholder="Nhập họ tên"
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label>Email *</label>
                                        <input
                                            type="email"
                                            required
                                            value={newUser.email}
                                            onChange={e => setNewUser({ ...newUser, email: e.target.value })}
                                            placeholder="email@example.com"
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label>Số điện thoại</label>
                                        <input
                                            type="text"
                                            value={newUser.phone}
                                            onChange={e => setNewUser({ ...newUser, phone: e.target.value })}
                                            placeholder="0912..."
                                        />
                                    </div>
                                    <div className="form-group">
                                        <label>Mật khẩu (Mặc định: 12345678)</label>
                                        <input
                                            type="text"
                                            value={newUser.password}
                                            onChange={e => setNewUser({ ...newUser, password: e.target.value })}
                                            placeholder="12345678"
                                        />
                                    </div>
                                </div>
                                <div className="modal-actions">
                                    <button
                                        type="button"
                                        onClick={() => setShowUserModal(false)}
                                        className="btn btn-secondary"
                                    >
                                        Hủy
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={creatingUser}
                                        className="btn btn-primary"
                                    >
                                        {creatingUser ? 'Đang tạo...' : 'Tạo & Chọn'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default OrderForm;
