import { Box, Pagination, Typography } from '@mui/material'

type Props = { page: number; perPage: number; total: number; onChange: (page: number) => void }
export default function PaginationBar({ page, perPage, total, onChange }: Props) {
  const totalPages = Math.max(1, Math.ceil(total / perPage))
  return (
    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mt: 2 }}>
      <Typography variant="body2">{total} resultados</Typography>
      <Pagination page={page} count={totalPages} onChange={(_, p) => onChange(p)} />
    </Box>
  )
}
