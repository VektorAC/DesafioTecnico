import { AppBar, Box, Container, IconButton, Toolbar, Typography, Button } from '@mui/material'
import MenuIcon from '@mui/icons-material/Menu'
import { Link, NavLink, Outlet, useLocation } from 'react-router-dom'
import ShopSelect from './ShopSelect'
import { useAuth } from '../context/authContext'

export default function Layout() {
    const { pathname } = useLocation()
    const isActive = (p: string) => pathname.startsWith(p)
    const { authed, signout } = useAuth()

  return (
    <Box sx={{ minHeight: '100vh', bgcolor: 'background.default' }}>
      <AppBar position="static" elevation={0} color="transparent" sx={{ borderBottom: 1, borderColor: 'divider' }}>
        <Toolbar>
          <IconButton edge="start" sx={{ mr: 2 }}>
            <MenuIcon />
          </IconButton>
          <Typography variant="h6" sx={{ flexGrow: 1, fontWeight: 600 }}>
            <Link to="/">Test Shop Admin</Link>
          </Typography>
          
          <Button component={NavLink} to="/products" variant={isActive('/products') ? 'contained' : 'text'}>
            Productos
          </Button>
          <Button component={NavLink} to="/orders" variant={isActive('/orders') ? 'contained' : 'text'}>
            Pedidos
          </Button>
          <Button component={NavLink} to="/metrics" variant={isActive('/metrics') ? 'contained' : 'text'}>
            Métricas
          </Button>
          <Button component={NavLink} to="/connect" variant={isActive('/connect') ? 'contained' : 'text'}>
            Conectar
          </Button>

          
          {authed && <Box sx={{ ml: 2 }}><ShopSelect /></Box>}
          {authed && (
            <Button onClick={() => signout()} sx={{ ml: 1 }} variant="outlined">
              Salir
            </Button>
          )}
        </Toolbar>
      </AppBar>

      <Container maxWidth="lg" sx={{ py: 3 }}>
        <Outlet />
      </Container>
    </Box>
  )
}
