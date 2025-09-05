import { TextField } from '@mui/material'
import { useEffect, useState } from 'react'

type Props = { value: string; onChange: (v: string) => void; placeholder?: string }
export default function SearchInput({ value, onChange, placeholder }: Props) {
  const [local, setLocal] = useState(value)
  useEffect(() => {
    const id = setTimeout(() => onChange(local), 400);
    return () => clearTimeout(id);
  }, [local, onChange])
  useEffect(() => setLocal(value), [value])
  return (
    <TextField
      size="small"
      fullWidth
      placeholder={placeholder || 'Buscar...'}
      value={local}
      onChange={e => setLocal(e.target.value)}
      InputProps={{ sx: { borderRadius: 2 } }}
    />
  )
}
