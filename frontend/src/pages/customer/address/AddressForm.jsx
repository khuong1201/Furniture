import React, { useState, useEffect } from 'react';
import { useAddress } from '@/hooks/useAddress';
import styles from './AddressForm.module.css';

const AddressForm = ({ initialData = null, onSuccess, onCancel }) => {
  const { createAddress, updateAddress, loading } = useAddress();

  // State lưu dữ liệu form
  const [formData, setFormData] = useState({
    full_name: '',
    phone: '',
    province: '',
    district: '',
    ward: '',
    street: '',
    is_default: false,
    type: 'home' // Mặc định là nhà riêng
  });

  const [errors, setErrors] = useState({});

  // Nếu có initialData (trường hợp Edit), fill dữ liệu vào form
  useEffect(() => {
    if (initialData) {
      setFormData({
        full_name: initialData.full_name || '',
        phone: initialData.phone || '',
        province: initialData.province || '',
        district: initialData.district || '',
        ward: initialData.ward || '',
        street: initialData.street || '',
        is_default: initialData.is_default || false,
        type: initialData.type || 'home'
      });
    }
  }, [initialData]);

  // Xử lý thay đổi input text
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    // Xóa lỗi khi người dùng gõ
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  // Xử lý thay đổi checkbox / radio
  const handleCheckChange = (e) => {
    const { name, checked, type, value } = e.target;
    const val = type === 'checkbox' ? checked : value;
    setFormData(prev => ({ ...prev, [name]: val }));
  };

  // Hàm validate đơn giản
  const validate = () => {
    const newErrors = {};
    const phoneRegex = /^[0-9]{10,11}$/;

    if (!formData.full_name.trim()) newErrors.full_name = 'Vui lòng nhập họ tên';
    if (!formData.phone.trim()) {
      newErrors.phone = 'Vui lòng nhập số điện thoại';
    } else if (!phoneRegex.test(formData.phone)) {
      newErrors.phone = 'Số điện thoại không hợp lệ';
    }
    
    if (!formData.province.trim()) newErrors.province = 'Vui lòng nhập Tỉnh/Thành';
    if (!formData.district.trim()) newErrors.district = 'Vui lòng nhập Quận/Huyện';
    if (!formData.ward.trim()) newErrors.ward = 'Vui lòng nhập Phường/Xã';
    if (!formData.street.trim()) newErrors.street = 'Vui lòng nhập tên đường/số nhà';

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) return;

    try {
      if (initialData?.uuid) {
        // Trường hợp UPDATE
        await updateAddress(initialData.uuid, formData);
        alert('Cập nhật địa chỉ thành công!');
      } else {
        // Trường hợp CREATE
        await createAddress(formData);
        alert('Thêm địa chỉ mới thành công!');
      }
      
      // Callback để đóng modal hoặc reload list bên ngoài
      if (onSuccess) onSuccess();

    } catch (err) {
      alert(err.message || 'Có lỗi xảy ra');
    }
  };

  return (
    <div className={styles['form-container']}>
      <h3 className={styles['form-title']}>
        {initialData ? 'Cập Nhật Địa Chỉ' : 'Thêm Địa Chỉ Mới'}
      </h3>
      
      <form onSubmit={handleSubmit}>
        {/* HỌ TÊN & SỐ ĐIỆN THOẠI */}
        <div className={styles['form-row']}>
          <div className={styles['form-col']}>
            <label className={styles['label']}>Họ và tên</label>
            <input 
              type="text" 
              name="full_name" 
              value={formData.full_name} 
              onChange={handleChange}
              className={styles['input']}
              placeholder="Nguyễn Văn A"
            />
            {errors.full_name && <span className={styles['error-text']}>{errors.full_name}</span>}
          </div>
          <div className={styles['form-col']}>
            <label className={styles['label']}>Số điện thoại</label>
            <input 
              type="text" 
              name="phone" 
              value={formData.phone} 
              onChange={handleChange}
              className={styles['input']}
              placeholder="0901234567"
            />
            {errors.phone && <span className={styles['error-text']}>{errors.phone}</span>}
          </div>
        </div>

        {/* TỈNH - HUYỆN - XÃ (Lưu ý: Thực tế nên dùng Select Dropdown API) */}
        <div className={styles['form-row']}>
          <div className={styles['form-col']}>
            <label className={styles['label']}>Tỉnh/Thành phố</label>
            <input 
              type="text" 
              name="province" 
              value={formData.province} 
              onChange={handleChange}
              className={styles['input']}
              placeholder="TP. Hồ Chí Minh"
            />
            {errors.province && <span className={styles['error-text']}>{errors.province}</span>}
          </div>
          <div className={styles['form-col']}>
            <label className={styles['label']}>Quận/Huyện</label>
            <input 
              type="text" 
              name="district" 
              value={formData.district} 
              onChange={handleChange}
              className={styles['input']}
              placeholder="Quận 1"
            />
            {errors.district && <span className={styles['error-text']}>{errors.district}</span>}
          </div>
        </div>

        <div className={styles['form-row']}>
          <div className={styles['form-col']}>
            <label className={styles['label']}>Phường/Xã</label>
            <input 
              type="text" 
              name="ward" 
              value={formData.ward} 
              onChange={handleChange}
              className={styles['input']}
              placeholder="Phường Bến Nghé"
            />
            {errors.ward && <span className={styles['error-text']}>{errors.ward}</span>}
          </div>
          <div className={styles['form-col']}>
            <label className={styles['label']}>Địa chỉ chi tiết</label>
            <input 
              type="text" 
              name="street" 
              value={formData.street} 
              onChange={handleChange}
              className={styles['input']}
              placeholder="Số 123, Đường Lê Lợi"
            />
            {errors.street && <span className={styles['error-text']}>{errors.street}</span>}
          </div>
        </div>

        {/* LOẠI ĐỊA CHỈ */}
        <div className={styles['form-group']}>
          <label className={styles['label']}>Loại địa chỉ</label>
          <div className={styles['radio-group']}>
            <label className={styles['radio-label']}>
              <input 
                type="radio" 
                name="type" 
                value="home" 
                checked={formData.type === 'home'} 
                onChange={handleCheckChange} 
              />
              Nhà riêng
            </label>
            <label className={styles['radio-label']}>
              <input 
                type="radio" 
                name="type" 
                value="office" 
                checked={formData.type === 'office'} 
                onChange={handleCheckChange} 
              />
              Văn phòng
            </label>
          </div>
        </div>

        {/* ĐẶT LÀM MẶC ĐỊNH */}
        <div className={styles['checkbox-wrapper']}>
          <input 
            type="checkbox" 
            name="is_default" 
            id="is_default"
            checked={formData.is_default} 
            onChange={handleCheckChange} 
          />
          <label htmlFor="is_default" style={{cursor: 'pointer', fontSize: '14px'}}>Đặt làm địa chỉ mặc định</label>
        </div>

        {/* BUTTONS */}
        <div className={styles['btn-group']}>
          <button 
            type="button" 
            className={`${styles['btn']} ${styles['btn-cancel']}`}
            onClick={onCancel}
            disabled={loading}
          >
            Hủy
          </button>
          <button 
            type="submit" 
            className={`${styles['btn']} ${styles['btn-submit']}`}
            disabled={loading}
          >
            {loading ? 'Đang lưu...' : 'Hoàn thành'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default AddressForm;