import { defineStore } from 'pinia'
import type { User, AuthResponse } from '~/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = useCookie<string | null>('auth_token', {
    maxAge: 60 * 60 * 24 * 7,
    sameSite: 'lax',
  })

  const isAuthenticated = computed(() => !!token.value)

  const { apiFetch } = useApi()
  const config = useRuntimeConfig()

  const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
    const data = await apiFetch<AuthResponse>('/auth/register', {
      method: 'POST',
      body: { name, email, password, password_confirmation: passwordConfirmation },
    })
    token.value = data.token
    user.value = data.user
    return data
  }

  const login = async (email: string, password: string) => {
    const data = await apiFetch<AuthResponse>('/auth/login', {
      method: 'POST',
      body: { email, password },
    })
    token.value = data.token
    user.value = data.user
    return data
  }

  const logout = async () => {
    await apiFetch('/auth/logout', { method: 'POST' })
    token.value = null
    user.value = null
  }

  const fetchMe = async () => {
    if (!token.value) return
    try {
      const data = await apiFetch<User>('/auth/me')
      user.value = data
    } catch {
      token.value = null
      user.value = null
    }
  }

  const updateProfile = async (name: string, email: string) => {
    const data = await apiFetch<User>('/profile', {
      method: 'PUT',
      body: { name, email },
    })
    user.value = data
    return data
  }

  const uploadAvatar = async (file: File) => {
    const formData = new FormData()
    formData.append('avatar', file)
    const data = await $fetch<User>(`${config.public.apiBase}/profile/avatar`, {
      method: 'POST',
      body: formData,
      headers: { Authorization: `Bearer ${token.value}` },
    })
    user.value = data
    return data
  }

  const changePassword = async (currentPassword: string, newPassword: string, newPasswordConfirmation: string) => {
    await apiFetch('/profile/password', {
      method: 'PUT',
      body: {
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: newPasswordConfirmation,
      },
    })
  }

  const deleteAccount = async (password: string) => {
    await apiFetch('/profile', {
      method: 'DELETE',
      body: { password },
    })
    token.value = null
    user.value = null
  }

  return {
    user,
    token,
    isAuthenticated,
    register,
    login,
    logout,
    fetchMe,
    updateProfile,
    uploadAvatar,
    changePassword,
    deleteAccount,
  }
})
