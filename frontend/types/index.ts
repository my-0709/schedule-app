export interface User {
  id: number
  name: string
  email: string
  avatar: string | null
}

export interface Schedule {
  id: number
  user_id: number
  title: string
  description: string | null
  start_at: string
  end_at: string
  color: string
  created_at: string | null
  updated_at: string | null
}

export interface AuthResponse {
  user: User
  token: string
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

export const SCHEDULE_COLORS = [
  { value: 'indigo',  hex: '#6366f1', label: 'インディゴ' },
  { value: 'violet',  hex: '#8b5cf6', label: 'バイオレット' },
  { value: 'sky',     hex: '#0ea5e9', label: 'スカイ' },
  { value: 'emerald', hex: '#10b981', label: 'エメラルド' },
  { value: 'amber',   hex: '#f59e0b', label: 'アンバー' },
  { value: 'rose',    hex: '#f43f5e', label: 'ローズ' },
] as const
