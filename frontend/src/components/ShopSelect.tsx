import { FormControl, InputLabel, MenuItem, Select } from '@mui/material'
import { useShop } from '../context/ShopContext'

export default function ShopSelect() {
  const { shops, activeShopId, setActiveShopId } = useShop()

  if (!shops.length) return null

  return (
    <FormControl size="small" sx={{ minWidth: 260 }}>
      <InputLabel id="shop-sel">Tienda activa</InputLabel>
      <Select
        labelId="shop-sel" label="Tienda activa"
        value={activeShopId ?? ''}
        onChange={(e) => setActiveShopId(Number(e.target.value))}
      >
        {shops.map(s => (
          <MenuItem key={s.id} value={s.id}>
            [{s.provider.toUpperCase()}] {s.domain}
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  )
}
