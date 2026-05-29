<script setup lang="ts">
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import jaLocale from '@fullcalendar/core/locales/ja'
import type { Schedule } from '~/types'
import { SCHEDULE_COLORS } from '~/types'

const props = defineProps<{ schedules: Schedule[] }>()
const emit = defineEmits<{
  edit: [schedule: Schedule]
  dateClick: [isoStr: string]
}>()

const scheduleStore = useScheduleStore()

const colorHexMap = Object.fromEntries(SCHEDULE_COLORS.map((c) => [c.value, c.hex]))

const toLocalISO = (date: Date): string => {
  const p = (n: number) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${p(date.getMonth() + 1)}-${p(date.getDate())}T${p(date.getHours())}:${p(date.getMinutes())}:${p(date.getSeconds())}`
}

const handleEventClick = (arg: any) => {
  emit('edit', arg.event.extendedProps.schedule as Schedule)
}

const handleDateClick = (arg: any) => {
  emit('dateClick', toLocalISO(arg.date as Date))
}

const handleEventDrop = async (arg: any) => {
  const id = Number(arg.event.id)
  const start = arg.event.start as Date
  const end = (arg.event.end || new Date(start.getTime() + 60 * 60 * 1000)) as Date
  const schedule = props.schedules.find((s) => s.id === id)
  if (!schedule) { arg.revert(); return }
  try {
    await scheduleStore.updateSchedule(id, {
      title: schedule.title,
      description: schedule.description,
      start_at: toLocalISO(start),
      end_at: toLocalISO(end),
      color: schedule.color,
    })
  } catch {
    arg.revert()
  }
}

const handleEventResize = async (arg: any) => {
  const id = Number(arg.event.id)
  const start = arg.event.start as Date
  const end = arg.event.end as Date
  const schedule = props.schedules.find((s) => s.id === id)
  if (!schedule) { arg.revert(); return }
  try {
    await scheduleStore.updateSchedule(id, {
      title: schedule.title,
      description: schedule.description,
      start_at: toLocalISO(start),
      end_at: toLocalISO(end),
      color: schedule.color,
    })
  } catch {
    arg.revert()
  }
}

// カラーを直接DOM要素に強制適用（CSS変数の問題を回避）
const applyEventColor = (el: HTMLElement, hex: string) => {
  el.style.backgroundColor = hex
  el.style.borderColor = hex
  el.style.setProperty('--fc-event-bg-color', hex)
  el.style.setProperty('--fc-event-border-color', hex)
}

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: jaLocale,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay',
  },
  events: props.schedules.map((s) => ({
    id: String(s.id),
    title: s.title,
    start: s.start_at,
    end: s.end_at,
    backgroundColor: colorHexMap[s.color] || '#6366f1',
    borderColor: colorHexMap[s.color] || '#6366f1',
    textColor: '#ffffff',
    extendedProps: { schedule: s },
  })),
  editable: true,
  dateClick: handleDateClick,
  eventClick: handleEventClick,
  eventDrop: handleEventDrop,
  eventResize: handleEventResize,
  // DOMマウント後にインラインスタイルを強制適用
  eventDidMount: (arg: any) => {
    const hex = arg.event.backgroundColor || '#6366f1'
    applyEventColor(arg.el, hex)
    // daygrid のタイトル要素にも適用
    const main = arg.el.querySelector('.fc-event-main')
    if (main) {
      ;(main as HTMLElement).style.color = '#ffffff'
    }
  },
  height: 'auto',
  dayMaxEvents: 4,
  eventMinHeight: 22,
}))
</script>

<template>
  <div class="calendar-root">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<style scoped>
.calendar-root {
  /* スマホ週/日ビューで横スクロール可能に */
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
</style>
