import React from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import { QueryClientProvider } from '@tanstack/react-query'
import { CssBaseline, ThemeProvider, createTheme } from '@mui/material'
import App from './App'
import { queryClient } from './lib/queryClient'
import { AuthProvider } from './context/authContext'
import { ShopProvider } from './context/ShopContext'
import 'antd/dist/reset.css';
import './styles.css'

const theme = createTheme({ palette: { mode: 'light' } })

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <QueryClientProvider client={queryClient}>
        <BrowserRouter>
          <AuthProvider>
            <ShopProvider>
              <App />
            </ShopProvider>
          </AuthProvider>
        </BrowserRouter>
      </QueryClientProvider>
    </ThemeProvider>
  </React.StrictMode>
)
