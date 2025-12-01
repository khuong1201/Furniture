import React, { useState } from 'react';
import {useAuth} from '../../hooks/AuthContext'
import {Link,  } from 'react-router-dom';
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-react';
import './RegisterForm.css';

const RegisterForm = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const navigate = useNavigate();
  
  const [localError, setLocalError] = useState('');

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    agreeTerms: false,
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

    if (formData.password !== formData.confirmPassword) {
      setLocalError("Authentication passwords do not match!");
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
      password_confirmation: formData.confirmPassword,
      device_name: 'web_browser'
    };
    
    const isSuccess = await register(payload);

    if (isSuccess) {
        alert("Create account success");
        navigate('/login'); 
    }
  };

  return (
    <div className="signup-wrapper">
      <div className="signup-card">
        <h2 className="signup-title">SIGN UP</h2>

        {(localError || apiError) && (
            <div style={{ 
                backgroundColor: '#ffebee', 
                color: '#c62828', 
                padding: '10px', 
                borderRadius: '4px', 
                marginBottom: '15px', 
                fontSize: '14px',
                textAlign: 'center',
                border: '1px solid #ffcdd2'
            }}>
                {localError || apiError}
            </div>
        )}

        <form onSubmit={handleSubmit}>
          
          {/* Name Field */}
          <div className="form-group">
            <label className="form-label">Name</label>
            <div className="input-wrapper">
              <User className="input-icon" />
              <input
                type="text"
                name="name"
                placeholder="Enter your Name"
                className="form-input"
                value={formData.name}
                onChange={handleChange}
              />
            </div>
          </div>

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
                style={{ paddingRight: '40px' }} // Thêm padding phải để tránh đè icon mắt
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

          {/* Confirm Password Field */}
          <div className="form-group">
            <label className="form-label">Confirm Password</label>
            <div className="input-wrapper">
              <Lock className="input-icon" />
              <input
                type={showConfirmPassword ? "text" : "password"}
                name="confirmPassword"
                placeholder="Confirm your password"
                className="form-input"
                style={{ paddingRight: '40px' }}
                value={formData.confirmPassword}
                onChange={handleChange}
              />
              <button
                type="button"
                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                className="toggle-password"
              >
                {showConfirmPassword ? <Eye size={20} /> : <EyeOff size={20} />}
              </button>
            </div>
          </div>

          {/* Checkbox Terms */}
          <div className="terms-wrapper">
            <input
              type="checkbox"
              name="agreeTerms"
              id="agreeTerms"
              className="checkbox"
              checked={formData.agreeTerms}
              onChange={handleChange}
            />
            <label htmlFor="agreeTerms" className="terms-text">
              I agree to the <a href="#" className="link-highlight">Terms & Conditions</a> and <a href="#" className="link-highlight">Privacy Policy</a>
            </label>
          </div>

          {/* Submit Button */}
          <button 
            type="submit" 
            className="btn-primary" 
            disabled={loading}
            style={{ opacity: loading ? 0.7 : 1, cursor: loading ? 'not-allowed' : 'pointer' }}
          >
            {loading ? 'Processing...' : 'Create Account'}
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
          Already have an account?{' '}
          <Link to='/login' className='link-highlight'>
            Login   
          </Link>
        </p>
      </div>
    </div>
  );
};

export default RegisterForm;