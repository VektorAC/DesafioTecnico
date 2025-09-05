import { useQuery } from '@tanstack/react-query'
import api from '../lib/axios'
import { Box, Button, MenuItem, Paper, Stack, Table, TableBody, TableCell, TableHead, TableRow, TextField, Typography } from '@mui/material'
import { useMemo, useState } from 'react'
import PaginationBar from '../components/PaginationBar'
import SearchInput from '../components/SearchInput'
import { downloadFile } from '../lib/download'
import { useShop } from '../context/ShopContext'

type Product = {
  id: string
  title: string
  sku: string
  price: number | string
  currency: string
  image?: string
  created_at?: string
}

type FetchProductsParams = {
  page: number,
  per_page: number,
  search?: string,
  shop_id?: string | number,
}

async function fetchProducts(params: FetchProductsParams) {
  const { data } = await api.get('/api/products', { params })
  return data as { data: Product[]; total: number }
}

export default function ProductsPage() {
  const { activeShopId } = useShop()
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState('')

  const q = useQuery({
    queryKey: ['products', { page, perPage, search, activeShopId }],
    queryFn: () => fetchProducts({ page, per_page: perPage, search, shop_id: activeShopId ?? undefined }),
    placeholderData: (prev) => prev,
  })

  const rows = q.data?.data ?? []
  const total = q.data?.total ?? 0
  const currency = rows[0]?.currency ?? 'CLP'

  const displayRows: (Product | undefined)[] = q.isLoading
    ? Array.from({ length: 5 }).map(() => undefined)
    : rows;

  const columns = useMemo(() => ([
    { key: 'title', label: 'Nombre' },
    { key: 'sku', label: 'SKU' },
    { key: 'price', label: 'Precio' },
    { key: 'created_at', label: 'Creado' },
  ]), [])

  return (
    <Stack spacing={2}>
      <Typography variant="h5" sx={{ fontWeight: 700 }}>Productos</Typography>

      <Paper sx={{ p: 2, borderRadius: 3 }}>
        <Stack direction={{ xs:'column', sm:'row' }} spacing={2}>
          <Box sx={{ flex: 1 }}><SearchInput value={search} onChange={setSearch} placeholder="Buscar por nombre o SKU" /></Box>
          <TextField
            select size="small" label="Por página" value={perPage}
            onChange={e=>{ setPerPage(Number(e.target.value)); setPage(1) }}
            sx={{ width: 140 }}
          >
            {[10,25,50].map(v => <MenuItem key={v} value={v}>{v}</MenuItem>)}
          </TextField>
          <Button
            onClick={() => downloadFile('/api/export/products.csv', search ? { search } : undefined)}
            variant="outlined"
          >
            Exportar CSV
          </Button>
          <Button
            onClick={() => downloadFile('/api/export/products.xlsx', search ? { search } : undefined)}
            variant="contained"
          >
            Exportar Excel
          </Button>
        </Stack>
      </Paper>

      <Paper sx={{ borderRadius: 3, overflow: 'hidden' }}>
        <Table size="small">
          <TableHead>
            <TableRow>
              {columns.map(c => <TableCell key={c.key} sx={{ fontWeight: 600 }}>{c.label}</TableCell>)}
            </TableRow>
          </TableHead>
          <TableBody>
            {displayRows.map((r, idx) => (
              <TableRow key={r?.id ?? idx}>
                <TableCell>{r?.title ?? '—'}</TableCell>
                <TableCell>{r?.sku ?? '—'}</TableCell>
                <TableCell>{typeof r?.price === 'number' ? r.price.toLocaleString() : r?.price} {currency}</TableCell>
                <TableCell>{r?.created_at?.slice(0,10) ?? '—'}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </Paper>

      <PaginationBar page={page} perPage={perPage} total={total} onChange={setPage} />
    </Stack>
  )
}
