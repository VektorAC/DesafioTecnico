import { useQuery } from '@tanstack/react-query'
import api from '../lib/axios'
import { Box, Button, MenuItem, Paper, Stack, Table, TableBody, TableCell, TableHead, TableRow, TextField, Typography } from '@mui/material'
import { useState } from 'react'
import PaginationBar from '../components/PaginationBar'
import { downloadFile } from '../lib/download'

type Order = {
  id: string
  order_number: string
  date: string
  customer: string
  total: number
  currency: string
  status: string
}

type FetchOrdersParams = {
  page: number;
  per_page: number;
  from: string;
  to: string;
};

async function fetchOrders(params: FetchOrdersParams) {
  const { data } = await api.get('/api/orders', { params })
  return data as { data: Order[]; total: number }
}

const todayISO = () => new Date().toISOString().slice(0,10)
const monthAgoISO = () => new Date(Date.now() - 30*24*3600*1000).toISOString().slice(0,10)

export default function OrdersPage() {
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [from, setFrom] = useState(monthAgoISO())
  const [to, setTo] = useState(todayISO())

  const q = useQuery({
    queryKey: ['orders', { page, perPage, from, to }],
    queryFn: () => fetchOrders({ page, per_page: perPage, from, to }),
    placeholderData: (prev) => prev,
  })

  const rows = q.data?.data ?? []
  const total = q.data?.total ?? 0
  const currency = rows[0]?.currency ?? 'CLP'
  const displayRows: (Order | undefined)[] = q.isLoading
    ? Array.from({ length: 5 }).map(() => undefined)
    : rows;

  return (
    <Stack spacing={2}>
      <Typography variant="h5" sx={{ fontWeight: 700 }}>Pedidos</Typography>

      <Paper sx={{ p: 2, borderRadius: 3 }}>
        <Stack direction={{ xs:'column', md:'row' }} spacing={2}>
          <TextField
            label="Desde" type="date" size="small" value={from}
            onChange={e=>{ setFrom(e.target.value); setPage(1) }}
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            label="Hasta" type="date" size="small" value={to}
            onChange={e=>{ setTo(e.target.value); setPage(1) }}
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            select size="small" label="Por página" value={perPage}
            onChange={e=>{ setPerPage(Number(e.target.value)); setPage(1) }}
            sx={{ width: 140 }}
          >
            {[10,25,50].map(v => <MenuItem key={v} value={v}>{v}</MenuItem>)}
          </TextField>
          <Box sx={{ flex: 1 }} />
          <Button
            onClick={() => downloadFile('/api/export/orders.csv', { from, to })}
            variant="outlined"
          >
            Exportar CSV
          </Button>
          <Button
            onClick={() => downloadFile('/api/export/orders.xlsx', { from, to })}
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
              <TableCell sx={{ fontWeight: 600 }}>Orden</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Fecha</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Cliente</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Total</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Estado</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {displayRows.map((r, idx) => (
              <TableRow key={r?.id ?? idx}>
                <TableCell>{r?.order_number ?? '—'}</TableCell>
                <TableCell>{r?.date?.replace('T',' ').slice(0,16) ?? '—'}</TableCell>
                <TableCell>{r?.customer ?? '—'}</TableCell>
                <TableCell>{r?.total?.toLocaleString?.() ?? r?.total} {currency}</TableCell>
                <TableCell>{r?.status ?? '—'}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </Paper>

      <PaginationBar page={page} perPage={perPage} total={total} onChange={setPage} />
    </Stack>
  )
}
