import { useState } from 'react'
import { Box, Paper, Stack, Tabs, Tab, TextField, Button, Typography, Alert } from '@mui/material'
import { connectShopify, connectWoo } from '../api/shops'
import { useShop } from '../context/ShopContext'

export default function ConnectPage() {
  const [tab, setTab] = useState(0)
  const [shopDomain, setShopDomain] = useState('mi-tienda.myshopify.com')
  const [wooDomain, setWooDomain] = useState('https://miwp.com')
  const [ck, setCk] = useState('')
  const [cs, setCs] = useState('')
  const [msg, setMsg] = useState<string | null>(null)
  const { refresh } = useShop()

  const onConnectShopify = () => {
    if (!shopDomain.endsWith('.myshopify.com')) {
      setMsg('Ingresa el dominio completo: tienda.myshopify.com')
      return
    }
    connectShopify(shopDomain) // redirige al backend (OAuth)
  }

  const onConnectWoo = async () => {
    setMsg(null)
    try {
      await connectWoo({ domain: wooDomain.trim(), ck: ck.trim(), cs: cs.trim() })
      await refresh()
      setMsg('WooCommerce conectado ✅')
    } catch (e: unknown) {
      if (e && typeof e === 'object' && 'response' in e && e.response && typeof e.response === 'object' && 'data' in e.response && e.response.data && typeof e.response.data === 'object' && 'message' in e.response.data) {
        // @ts-expect-error: dynamic error shape from axios or fetch
        setMsg(e.response.data.message || 'Error al conectar WooCommerce')
      } else {
        setMsg('Error al conectar WooCommerce')
      }
    }
  }

  return (
    <Box sx={{ display: 'grid', placeItems: 'start', gap: 2 }}>
      <Typography variant="h5" sx={{ fontWeight: 700 }}>Conectar tienda</Typography>
      <Paper sx={{ p: 2, borderRadius: 3, width: 600, maxWidth: '100%' }}>
        <Tabs value={tab} onChange={(_, v) => setTab(v)} sx={{ mb: 2 }}>
          <Tab label="Shopify" />
          <Tab label="WooCommerce" />
        </Tabs>

        {tab === 0 && (
          <Stack spacing={2}>
            <TextField
              label="Dominio de Shopify" placeholder="mi-tienda.myshopify.com"
              value={shopDomain} onChange={e => setShopDomain(e.target.value)}
            />
            <Button variant="contained" onClick={onConnectShopify}>
              Conectar con Shopify
            </Button>
          </Stack>
        )}

        {tab === 1 && (
          <Stack spacing={2}>
            <TextField label="URL del sitio" placeholder="https://miwp.com"
              value={wooDomain} onChange={e => setWooDomain(e.target.value)} />
            <TextField label="Consumer Key" value={ck} onChange={e => setCk(e.target.value)} />
            <TextField label="Consumer Secret" value={cs} onChange={e => setCs(e.target.value)} />
            <Button variant="contained" onClick={onConnectWoo}>
              Guardar credenciales Woo
            </Button>
          </Stack>
        )}
      </Paper>

      {msg && <Alert severity="info">{msg}</Alert>}
    </Box>
  )
}
