import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';

// --- IMPORT COMPONENTS CON ---
import AddressForm from '../address/AddressForm'; // Form địa chỉ
import OrderList from '../order/OrderList';       // List đơn hàng dạng Card

// --- IMPORT HOOKS ---
import { useAuth } from '@/hooks/AuthContext';
import { useUser } from '@/hooks/useUser';
import { useAddress } from '@/hooks/useAddress';

// Icons
import { 
  AiOutlineUser, 
  AiOutlineShopping, 
  AiOutlineEnvironment, 
  AiOutlineLock, 
  AiOutlineLogout, 
  AiOutlinePlus 
} from 'react-icons/ai';

import './ProfilePage.css'; // File CSS chứa style chung của Profile

const ProfilePage = () => {
  const [activeTab, setActiveTab] = useState('profile');
  const navigate = useNavigate();
  const location = useLocation();
  const { logout } = useAuth(); 
  const { profile: user, getProfile, loading: userLoading } = useUser();

  // Handle URL tabs
  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const tabParam = params.get('tab');
    if (tabParam && ['profile', 'addresses', 'orders', 'password'].includes(tabParam)) {
      setActiveTab(tabParam);
    }
  }, [location.search]);

  useEffect(() => { getProfile(); }, [getProfile]);

  const handleLogout = async () => {
    await logout(); 
    navigate('/login');
  };

  const renderContent = () => {
    switch (activeTab) {
      case 'profile': return <ProfileTab user={user} refreshUser={getProfile} />;
      case 'addresses': return <AddressTab />;
      
      // ✅ GỌI ORDER LIST TẠI ĐÂY (isEmbedded = true để ẩn Header to)
      case 'orders': 
        return (
          <div>
             <h3 className="section-title">My Order History</h3>
             <OrderList isEmbedded={true} />
          </div>
        );
        
      case 'password': return <PasswordTab />;
      default: return null;
    }
  };

  if (userLoading && !user) return <div style={{padding:'40px', textAlign:'center'}}>Loading profile...</div>;

  return (
    <div className="profile-container">
      {/* SIDEBAR */}
      <div className="profile-sidebar">
        <div className="user-brief">
          <div className="avatar-circle">
            {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
          </div>
          <div className="user-names">
            <h4>{user?.name || 'User'}</h4>
            <p>{user?.email}</p>
          </div>
        </div>
        
        <div className="sidebar-menu">
          <div className={`menu-item ${activeTab === 'profile' ? 'active' : ''}`} onClick={() => setActiveTab('profile')}>
            <AiOutlineUser size={20} /> My Profile
          </div>
          <div className={`menu-item ${activeTab === 'addresses' ? 'active' : ''}`} onClick={() => setActiveTab('addresses')}>
            <AiOutlineEnvironment size={20} /> Address Book
          </div>
          <div className={`menu-item ${activeTab === 'orders' ? 'active' : ''}`} onClick={() => setActiveTab('orders')}>
            <AiOutlineShopping size={20} /> My Orders
          </div>
          <div className={`menu-item ${activeTab === 'password' ? 'active' : ''}`} onClick={() => setActiveTab('password')}>
            <AiOutlineLock size={20} /> Change Password
          </div>
          <div className="menu-item logout" onClick={handleLogout}>
            <AiOutlineLogout size={20} /> Logout
          </div>
        </div>
      </div>

      {/* CONTENT */}
      <div className="profile-content">
        {renderContent()}
      </div>
    </div>
  );
};

// ================= SUB COMPONENTS =================

// 1. TAB THÔNG TIN CÁ NHÂN
const ProfileTab = ({ user, refreshUser }) => {
  const { updateProfile } = useUser();
  const [formData, setFormData] = useState({ name: '', phone: '' });

  useEffect(() => {
    if (user) setFormData({ name: user.name, phone: user.phone || '' });
  }, [user]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await updateProfile(formData);
      alert('Profile updated successfully!');
      refreshUser(); 
    } catch (error) {
      alert(error.message || 'Update failed');
    }
  };

  return (
    <div>
      <h3 className="section-title">My Profile</h3>
      <div className="form-container">
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label className="label">Full Name</label>
            <input className="input" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} required />
          </div>
          <div className="form-group">
            <label className="label">Phone Number</label>
            <input className="input" value={formData.phone} onChange={e => setFormData({...formData, phone: e.target.value})} />
          </div>
          <div className="form-group">
            <label className="label">Email Address</label>
            <input className="input" value={user?.email || ''} disabled style={{background:'#f9f9f9'}} />
          </div>
          <div style={{marginTop: '30px', textAlign: 'right'}}>
            <button type="submit" className="btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  );
};

// 2. TAB ĐỔI MẬT KHẨU
const PasswordTab = () => {
  const { changePassword } = useUser();
  const [pass, setPass] = useState({ current_password: '', new_password: '', new_password_confirmation: '' });

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await changePassword(pass);
      alert('Password changed successfully!');
      setPass({ current_password: '', new_password: '', new_password_confirmation: '' });
    } catch (error) {
      alert(error.message || 'Failed to change password');
    }
  };

  return (
    <div>
      <h3 className="section-title">Change Password</h3>
      <div className="form-container">
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label className="label">Current Password</label>
            <input type="password" className="input" value={pass.current_password} onChange={e => setPass({...pass, current_password: e.target.value})} required />
          </div>
          <div className="form-group">
            <label className="label">New Password</label>
            <input type="password" className="input" value={pass.new_password} onChange={e => setPass({...pass, new_password: e.target.value})} required />
          </div>
          <div className="form-group">
            <label className="label">Confirm New Password</label>
            <input type="password" className="input" value={pass.new_password_confirmation} onChange={e => setPass({...pass, new_password_confirmation: e.target.value})} required />
          </div>
          <div style={{marginTop: '30px', textAlign: 'right'}}>
            <button type="submit" className="btn-primary">Update Password</button>
          </div>
        </form>
      </div>
    </div>
  );
};

// 3. TAB SỔ ĐỊA CHỈ (Dùng AddressForm Component)
const AddressTab = () => {
  const { addresses, fetchAddresses, deleteAddress } = useAddress();
  const [view, setView] = useState('list');
  const [editingData, setEditingData] = useState(null);

  useEffect(() => { fetchAddresses(); }, [fetchAddresses]);

  const handleCreate = () => {
    setEditingData(null);
    setView('form');
  };

  const handleEdit = (addr) => {
    setEditingData(addr);
    setView('form');
  };

  const handleDelete = async (uuid) => {
    if(window.confirm('Are you sure you want to delete this address?')) {
      try { await deleteAddress(uuid); } catch(e) { alert(e.message || 'Failed to delete'); }
    }
  };

  const handleFormSuccess = () => {
    setView('list');
    fetchAddresses();
  };

  // Render Component AddressForm khi ở chế độ 'form'
  if (view === 'form') {
    return <AddressForm initialData={editingData} onSuccess={handleFormSuccess} onCancel={() => setView('list')} />;
  }

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:'20px'}}>
         <h3 className="section-title" style={{margin:0, border:'none', padding:0}}>Address Book</h3>
         <button className="btn-primary" onClick={handleCreate} style={{padding:'8px 15px', display:'flex', alignItems:'center', gap:'5px'}}>
           <AiOutlinePlus /> Add New
         </button>
      </div>

      <div className="address-grid" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '20px' }}>
        {addresses.map(addr => (
          <div key={addr.uuid} className={`address-card ${addr.is_default ? 'default' : ''}`} style={{ border: addr.is_default ? '1px solid #c4a48c' : '1px solid #eee', padding:'20px', borderRadius:'4px', position:'relative', background:'#fff' }}>
            {addr.is_default && (
                <span style={{position:'absolute', top:'15px', right:'15px', background:'#c4a48c', color:'#fff', padding:'2px 8px', borderRadius:'10px', fontSize:'11px'}}>Default</span>
            )}
            <h4 style={{marginTop:0, marginBottom:'5px', fontWeight:'700'}}>{addr.full_name}</h4>
            <p style={{margin:'5px 0', color:'#555', fontSize:'13px'}}>{addr.phone}</p>
            <div style={{margin:'10px 0'}}>
               <span style={{background:'#f0f0f0', padding:'2px 6px', borderRadius:'2px', fontSize:'11px', textTransform:'uppercase', color:'#666'}}>{addr.type}</span>
            </div>
            <p style={{margin:'5px 0', fontSize:'13px', color:'#666', lineHeight:'1.4'}}>
              {addr.street}, {addr.ward}, {addr.district}, {addr.province}
            </p>
            <div className="addr-actions" style={{marginTop:'15px', paddingTop:'10px', borderTop:'1px dashed #eee', display:'flex', justifyContent:'flex-end', gap:'10px'}}>
              <button className="btn-text" onClick={() => handleEdit(addr)}>Edit</button>
              {!addr.is_default && <button className="btn-text" style={{color:'#d9534f', borderColor:'#d9534f'}} onClick={() => handleDelete(addr.uuid)}>Delete</button>}
            </div>
          </div>
        ))}
        {addresses.length === 0 && <p style={{color:'#999'}}>No addresses found.</p>}
      </div>
    </div>
  );
};

export default ProfilePage;