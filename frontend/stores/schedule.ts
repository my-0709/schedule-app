import { defineStore } from 'pinia'
import type { Schedule } from '~/types'

export const useScheduleStore = defineStore('schedule', () => {
  const schedules = ref<Schedule[]>([])
  const { apiFetch } = useApi()

  const fetchSchedules = async () => {
    const data = await apiFetch<Schedule[]>('/schedules')
    schedules.value = data
  }

  const createSchedule = async (payload: {
    title: string
    description: string | null
    start_at: string
    end_at: string
    color?: string
  }) => {
    const data = await apiFetch<Schedule>('/schedules', {
      method: 'POST',
      body: { ...payload, color: payload.color || 'indigo' },
    })
    schedules.value.push(data)
    return data
  }

  const updateSchedule = async (
    id: number,
    payload: {
      title: string
      description: string | null
      start_at: string
      end_at: string
      color?: string
    }
  ) => {
    const data = await apiFetch<Schedule>(`/schedules/${id}`, {
      method: 'PUT',
      body: { ...payload, color: payload.color || 'indigo' },
    })
    const index = schedules.value.findIndex((s) => s.id === id)
    if (index !== -1) schedules.value[index] = data
    return data
  }

  const deleteSchedule = async (id: number) => {
    await apiFetch(`/schedules/${id}`, { method: 'DELETE' })
    schedules.value = schedules.value.filter((s) => s.id !== id)
  }

  const sortedSchedules = computed(() =>
    [...schedules.value].sort(
      (a, b) => new Date(a.start_at).getTime() - new Date(b.start_at).getTime()
    )
  )

  return { schedules, sortedSchedules, fetchSchedules, createSchedule, updateSchedule, deleteSchedule }
})
