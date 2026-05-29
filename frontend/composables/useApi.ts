export const useApi = () => {
  const config = useRuntimeConfig()
  const token = useCookie('auth_token')

  const apiFetch = async <T>(
    path: string,
    options: Parameters<typeof $fetch>[1] = {}
  ): Promise<T> => {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    }

    if (token.value) {
      headers['Authorization'] = `Bearer ${token.value}`
    }

    return $fetch<T>(`${config.public.apiBase}${path}`, {
      ...options,
      headers: {
        ...headers,
        ...(options.headers as Record<string, string> | undefined),
      },
    })
  }

  return { apiFetch }
}
