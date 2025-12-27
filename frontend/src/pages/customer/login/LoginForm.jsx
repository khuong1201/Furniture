import React, { useState } from 'react';
import { useAuth } from '@/hooks/AuthContext';
import { Link, useNavigate } from 'react-router-dom';
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-react';

// Sử dụng chung file style với RegisterForm
import styles from '../register/RegisterForm.module.css';

function LoginForm() {
  const [showPassword, setShowPassword] = useState(false);
  const navigate = useNavigate();

  const [localError, setLocalError] = useState('');
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    device_name: 'web'
  });

  const { login, loading, error } = useAuth();

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData({
      ...formData,
      [name]: type === 'checkbox' ? checked : value,
    });
    if (localError) setLocalError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    setLocalError('');

    if (!formData.email || !formData.password) {
      setLocalError("Please enter both email and password.");
      return;
    }

    const result = await login(formData.email, formData.password, formData.device_name);

    if (result.success) {
        navigate('/'); 
    } else {
        setLocalError(result.message || 'Đăng nhập thất bại'); 
    }
  };

  return (
    <div className={styles['signup-wrapper']}>
      <div className={styles['signup-card']}>
        <h2 className={styles['signup-title']}>LOG IN</h2>
        
        {localError && (
          <div className={styles['error-message']}>
            {localError}
          </div>
        )}

        <form onSubmit={handleSubmit}>

          {/* Email Field */}
          <div className={styles['form-group']}>
            <label className={styles['form-label']}>Email</label>
            <div className={styles['input-wrapper']}>
              <Mail className={styles['input-icon']} />
              <input
                type="email"
                name="email"
                placeholder="Enter your Email"
                className={styles['form-input']}
                value={formData.email}
                onChange={handleChange}
              />
            </div>
          </div>

          {/* Password Field */}
          <div className={styles['form-group']}>
            <label className={styles['form-label']}>Password</label>
            <div className={styles['input-wrapper']}>
              <Lock className={styles['input-icon']} />
              <input
                type={showPassword ? "text" : "password"}
                name="password"
                placeholder="Create a password"
                className={styles['form-input']}
                style={{ paddingRight: '40px' }}
                value={formData.password}
                onChange={handleChange}
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className={styles['toggle-password']}
              >
                {showPassword ? <Eye size={20} /> : <EyeOff size={20} />}
              </button>
            </div>
          </div>

          {/* Submit Button */}
          <button 
            type="submit" 
            className={styles['btn-primary']} 
            disabled={loading}
            style={{ opacity: loading ? 0.7 : 1, cursor: loading ? 'not-allowed' : 'pointer' }}
          >
            {loading ? 'Processing...' : 'LOG IN'}
          </button>
        </form>

        {/* Divider OR */}
        <div className={styles['divider']}>
          <span>OR</span>
        </div>

        {/* Google Button */}
        <button className={styles['btn-google']}>
          <img 
            src="https://www.svgrepo.com/show/475656/google-color.svg" 
            alt="Google Logo" 
            width="24" height="24"
          />
          Sign up with Google
        </button>

        {/* Footer Login Link */}
        <p className={styles['footer-text']}>
          Don't have an account?{' '}
          <Link to='/register' className={styles['link-highlight']}>
            Sign up
          </Link>
        </p>
      </div>
    </div>
  );
};

export default LoginForm;