import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.jsx' 

import '@fontsource/poppins'; //400
import '@fontsource/poppins/500.css';
import '@fontsource/poppins/600.css';
import '@fontsource/poppins/700.css';

import { BrowserRouter } from 'react-router-dom'
import { AuthProvider } from './hooks/AuthContext';
createRoot(document.getElementById('root')).render(
  // <StrictMode>
  //   <BrowserRouter>
  //     <AuthProvider>
  //       <App />
  //     </AuthProvider>
  //   </BrowserRouter>
  // </StrictMode>,
  <BrowserRouter>
    <AuthProvider>
      <App />
    </AuthProvider>
  </BrowserRouter>
)
