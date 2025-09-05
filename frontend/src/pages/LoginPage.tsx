// src/pages/LoginPage.tsx
import { useEffect, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { Box, Paper, Stack, TextField, Button, Typography, Alert } from '@mui/material'
import { useAuth } from '../context/authContext'

export default function LoginPage() {
  const { login, authed, loading: authLoading } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  type NavState = { from?: string } | null
  const next = (location.state as NavState)?.from ?? '/products'

  const [username, setUsername] = useState('test')
  const [password, setPassword] = useState('prueba123')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (authed) navigate(next, { replace: true })
  }, [authed, navigate, next])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setSubmitting(true)
    setError(null)
    try {
      await login(username, password)
      navigate(next, { replace: true })
    } catch (err) {
      const e = err as { response?: { data?: { message?: string } } }
      setError(e?.response?.data?.message ?? 'Error de autenticación')
    } finally {
      setSubmitting(false)
    }
  }

  const isBusy = submitting || authLoading

  return (
    <Box sx={{ display: 'grid', placeItems: 'center', minHeight: '60vh', px: 2 }}>
      <Paper sx={{ p: 4, width: 420, maxWidth: '100%', borderRadius: 3 }}>
        <Typography variant="h6" sx={{ mb: 2, fontWeight: 700 }}>
          Ingresar
        </Typography>

        <form onSubmit={handleSubmit} noValidate>
          <Stack spacing={2}>
            <TextField
              label="Usuario"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              autoComplete="username"
              autoFocus
              fullWidth
            />
            <TextField
              label="Password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="current-password"
              fullWidth
            />

            {error && <Alert severity="error">{error}</Alert>}

            <Button type="submit" variant="contained" disabled={isBusy}>
              {isBusy ? 'Ingresando…' : 'Ingresar'}
            </Button>

            <Typography variant="caption" color="text.secondary">
              Demo: usuario <b>test</b> — contraseña <b>prueba123</b>
            </Typography>
          </Stack>
        </form>
      </Paper>
    </Box>
  )
}
