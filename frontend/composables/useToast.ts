export interface ToastItem {
  id: number
  message: string
  type: 'success' | 'error' | 'info'
}

const _toasts = ref<ToastItem[]>([])
let _id = 0

export const useToast = () => {
  const add = (message: string, type: ToastItem['type'] = 'success', ms = 3200) => {
    if (import.meta.server) return
    const id = ++_id
    _toasts.value.push({ id, message, type })
    setTimeout(() => {
      _toasts.value = _toasts.value.filter((t) => t.id !== id)
    }, ms)
  }

  return {
    toasts: _toasts,
    success: (msg: string) => add(msg, 'success'),
    error: (msg: string) => add(msg, 'error'),
    info: (msg: string) => add(msg, 'info'),
  }
}
