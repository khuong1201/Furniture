import React, { useState } from 'react';
import { useAuth } from '../../../hooks/AuthContext';
import { Link, useNavigate } from 'react-router-dom';
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-react';

import styles from './RegisterForm.module.css';

const RegisterForm = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const navigate = useNavigate();
  
  const [localError, setLocalError] = useState('');

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    device_name: 'web',
    agreeTerms: false 
  });

  const { register, loading, error: apiError } = useAuth();

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

    if (!formData.name || !formData.email || !formData.password) {
      setLocalError("Please fill in all required fields.");
      return;
    }

    if (formData.password !== formData.password_confirmation) {
      setLocalError("Authentication passwords do not match!");
      return;
    }

    if (formData.password.length < 6) {
        setLocalError("Password must be at least 6 characters.");
        return;
    }
    
    const hasLetter = /[a-zA-Z]/.test(formData.password);
    if (!hasLetter) {
        setLocalError("Password must contain at least one letter.");
        return;
    }

    if (!formData.agreeTerms) {
      setLocalError("You must agree to the Terms of Use.");
      return;
    }
    const payload = {
      name: formData.name,
      email: formData.email,
      password: formData.password,
      password_confirmation: formData.password_confirmation,
      device_name: 'web_browser'
    };
    
    const isSuccess = await register(payload);

    if (isSuccess) {
        alert("Create account success");
        navigate('/login'); 
    }
  };

  return (
    // Sử dụng styles['class-name'] cho tất cả các class
    <div className={styles['signup-wrapper']}>
      <div className={styles['signup-card']}>
        <h2 className={styles['signup-title']}>SIGN UP</h2>

        {(localError || apiError) && (
          <div className={styles['error-message']}>
              {localError || apiError}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          
          {/* Name Field */}
          <div className={styles['form-group']}>
            <label className={styles['form-label']}>Name</label>
            <div className={styles['input-wrapper']}>
              <User className={styles['input-icon']} />
              <input
                type="text"
                name="name"
                placeholder="Enter your Name"
                className={styles['form-input']}
                value={formData.name}
                onChange={handleChange}
              />
            </div>
          </div>

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

          {/* Confirm Password Field */}
          <div className={styles['form-group']}>
            <label className={styles['form-label']}>Confirm Password</label>
            <div className={styles['input-wrapper']}>
              <Lock className={styles['input-icon']} />
              <input
                type={showConfirmPassword ? "text" : "password"}
                name="password_confirmation"
                placeholder="Confirm your password"
                className={styles['form-input']}
                style={{ paddingRight: '40px' }}
                value={formData.password_confirmation}
                onChange={handleChange}
              />
              <button
                type="button"
                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                className={styles['toggle-password']}
              >
                {showConfirmPassword ? <Eye size={20} /> : <EyeOff size={20} />}
              </button>
            </div>
          </div>

          {/* Checkbox Terms */}
          <div className={styles['terms-wrapper']}>
            <input
              type="checkbox"
              name="agreeTerms"
              id="agreeTerms"
              className={styles['checkbox']}
              checked={formData.agreeTerms}
              onChange={handleChange}
            />
            <label htmlFor="agreeTerms" className={styles['terms-text']}>
              I agree to the <a href="#" className={styles['link-highlight']}>Terms & Conditions</a> and <a href="#" className={styles['link-highlight']}>Privacy Policy</a>
            </label>
          </div>

          {/* Submit Button */}
          <button 
            type="submit" 
            className={styles['btn-primary']} 
            disabled={loading}
            style={{ opacity: loading ? 0.7 : 1, cursor: loading ? 'not-allowed' : 'pointer' }}
          >
            {loading ? 'Processing...' : 'Create Account'}
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
          Already have an account?{' '}
          <Link to='/login' className={styles['link-highlight']}>
            Login   
          </Link>
        </p>
      </div>
    </div>
  );
};

export default RegisterForm;