import React, { useState } from 'react';
import {useAuth} from '@/hooks/AuthContext'
import {Link, useNavigate } from 'react-router-dom';
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-react';
import '../register/RegisterForm';

function LoginForm () {
  const [showPassword, setShowPassword] = useState(false);
  const navigate = useNavigate();

  const [localError, setLocalError] = useState('');
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    device_name:'web'
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

    const result = await login (formData.email, formData.password, formData.device_name);

   if (result.success) {
        navigate('/customer'); 
    } else {
        setLocalError(result.message || 'Đăng nhập thất bại'); 
    }
  };

  
  return (
    <div className="signup-wrapper">
      <div className="signup-card">
        <h2 className="signup-title">LOG IN</h2>
        {localError && (
          <div className="error-message" style={{ 
              color: 'red', 
              backgroundColor: '#ffe6e6', 
              padding: '10px', 
              borderRadius: '5px', 
              marginBottom: '15px',
              textAlign: 'center',
              fontSize: '14px'
          }}>
            {localError}
          </div>
        )}
        <form onSubmit={handleSubmit}>

          {/* Email Field */}
          <div className="form-group">
            <label className="form-label">Email</label>
            <div className="input-wrapper">
              <Mail className="input-icon" />
              <input
                type="email"
                name="email"
                placeholder="Enter your Email"
                className="form-input"
                value={formData.email}
                onChange={handleChange}
              />
            </div>
          </div>

          {/* Password Field */}
          <div className="form-group">
            <label className="form-label">Password</label>
            <div className="input-wrapper">
              <Lock className="input-icon" />
              <input
                type={showPassword ? "text" : "password"}
                name="password"
                placeholder="Create a password"
                className="form-input"
                style={{ paddingRight: '40px' }}
                value={formData.password}
                onChange={handleChange}
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="toggle-password"
              >
                {showPassword ? <Eye size={20} /> : <EyeOff size={20} />}
              </button>
            </div>
          </div>


          {/* Submit Button */}
          <button 
            type="submit" 
            className="btn-primary" 
            disabled={loading}
            style={{ opacity: loading ? 0.7 : 1, cursor: loading ? 'not-allowed' : 'pointer' }}
          >
            {loading ? 'Processing...' : 'LOG IN'}
          </button>
        </form>

        {/* Divider OR */}
        <div className="divider">
          <span>OR</span>
        </div>

        {/* Google Button */}
        <button className="btn-google">
          <img 
            src="https://www.svgrepo.com/show/475656/google-color.svg" 
            alt="Google Logo" 
            width="24" height="24"
          />
          Sign up with Google
        </button>

        {/* Footer Login Link */}
        <p className="footer-text">
          Don't have an account?{' '}
          <Link to='/customer/register' className='link-highlight'>
            Sign up
          </Link>
        </p>
      </div>
    </div>
  );
};

export default LoginForm;