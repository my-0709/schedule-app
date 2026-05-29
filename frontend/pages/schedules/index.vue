<script setup lang="ts">
import type { Schedule } from '~/types'
import { SCHEDULE_COLORS } from '~/types'

definePageMeta({ middleware: 'auth' })

const authStore = useAuthStore()
const scheduleStore = useScheduleStore()
const router = useRouter()
const toast = useToast()

const view = ref<'calendar' | 'list'>('calendar')
const showModal = ref(false)
const editingSchedule = ref<Schedule | null>(null)
const deleteTarget = ref<Schedule | null>(null)
const loading = ref(false)
const deleteLoading = ref(false)

const colorHexMap = Object.fromEntries(SCHEDULE_COLORS.map((c) => [c.value, c.hex]))

const form = reactive({
  title: '',
  description: '',
  start_at: '',
  end_at: '',
  color: 'indigo',
})
const formErrors = ref<Record<string, string[]>>({})
const formServerError = ref('')

onMounted(async () => {
  await authStore.fetchMe()
  await scheduleStore.fetchSchedules()
})

const openCreateModal = () => {
  editingSchedule.value = null
  form.title = ''
  form.description = ''
  form.start_at = ''
  form.end_at = ''
  form.color = 'indigo'
  formErrors.value = {}
  formServerError.value = ''
  showModal.value = true
}

const openEditModal = (schedule: Schedule) => {
  editingSchedule.value = schedule
  form.title = schedule.title
  form.description = schedule.description || ''
  form.start_at = schedule.start_at.slice(0, 16)
  form.end_at = schedule.end_at.slice(0, 16)
  form.color = schedule.color || 'indigo'
  formErrors.value = {}
  formServerError.value = ''
  showModal.value = true
}

const handleDateClick = (isoStr: string) => {
  editingSchedule.value = null
  form.title = ''
  form.description = ''
  form.color = 'indigo'
  formErrors.value = {}
  formServerError.value = ''
  const start = new Date(isoStr)
  const end = new Date(start.getTime() + 60 * 60 * 1000)
  const fmt = (d: Date) => {
    const p = (n: number) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`
  }
  form.start_at = fmt(start)
  form.end_at = fmt(end)
  showModal.value = true
}

const handleSubmit = async () => {
  formErrors.value = {}
  formServerError.value = ''
  loading.value = true
  try {
    const payload = {
      title: form.title,
      description: form.description || null,
      start_at: form.start_at,
      end_at: form.end_at,
      color: form.color,
    }
    if (editingSchedule.value) {
      await scheduleStore.updateSchedule(editingSchedule.value.id, payload)
      toast.success('スケジュールを更新しました')
    } else {
      await scheduleStore.createSchedule(payload)
      toast.success('スケジュールを作成しました')
    }
    showModal.value = false
  } catch (err: any) {
    if (err.data?.errors) formErrors.value = err.data.errors
    else formServerError.value = err.data?.message || '保存に失敗しました。'
  } finally {
    loading.value = false
  }
}

const handleDeleteFromModal = () => {
  if (!editingSchedule.value) return
  deleteTarget.value = editingSchedule.value
  showModal.value = false
}

const handleDelete = async () => {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await scheduleStore.deleteSchedule(deleteTarget.value.id)
    toast.success('スケジュールを削除しました')
  } catch {
    toast.error('削除に失敗しました')
  } finally {
    deleteTarget.value = null
    deleteLoading.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  await router.push('/login')
}

const formatDateTime = (dt: string) =>
  new Date(dt).toLocaleString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })

const avatarInitial = computed(() => authStore.user?.name?.charAt(0).toUpperCase() || '?')
</script>

<template>
  <div class="app">
    <!-- ヘッダー -->
    <header class="header">
      <div class="header-inner">
        <div class="header-brand">
          <span class="material-symbols-rounded brand-icon">calendar_month</span>
          <span class="header-title">Schedule</span>
        </div>
        <div class="header-right">
          <NuxtLink to="/profile" class="user-chip">
            <img
              v-if="authStore.user?.avatar"
              :src="authStore.user.avatar"
              class="avatar-img"
              alt="avatar"
            />
            <span v-else class="avatar-initial">{{ avatarInitial }}</span>
            <span class="user-name">{{ authStore.user?.name }}</span>
          </NuxtLink>
          <button class="btn-ghost" @click="handleLogout">ログアウト</button>
        </div>
      </div>
    </header>

    <main class="main">
      <!-- ツールバー -->
      <div class="toolbar">
        <div class="toolbar-left">
          <div class="view-tabs">
            <button :class="['tab', { active: view === 'calendar' }]" @click="view = 'calendar'">
              <span class="material-symbols-rounded">grid_view</span>
              <span class="tab-label">カレンダー</span>
            </button>
            <button :class="['tab', { active: view === 'list' }]" @click="view = 'list'">
              <span class="material-symbols-rounded">view_list</span>
              <span class="tab-label">リスト</span>
            </button>
          </div>
          <span class="schedule-count">{{ scheduleStore.schedules.length }} 件</span>
        </div>
        <button class="btn-create" @click="openCreateModal">
          <span class="material-symbols-rounded">add</span>
          <span class="btn-create-label">新規作成</span>
        </button>
      </div>

      <!-- カレンダービュー -->
      <div v-if="view === 'calendar'" class="panel">
        <AppCalendar
          :schedules="scheduleStore.schedules"
          @edit="openEditModal"
          @date-click="handleDateClick"
        />
      </div>

      <!-- リストビュー（開始日時昇順） -->
      <div v-else class="panel">
        <div v-if="scheduleStore.sortedSchedules.length === 0" class="empty">
          <span class="material-symbols-rounded empty-illustration">event_busy</span>
          <p class="empty-title">スケジュールがありません</p>
          <p class="empty-sub">右上の「新規作成」から追加しましょう</p>
          <button class="btn-create empty-btn" @click="openCreateModal">
            <span class="material-symbols-rounded">add</span> 最初のスケジュールを作成
          </button>
        </div>
        <div v-else class="list">
          <article
            v-for="schedule in scheduleStore.sortedSchedules"
            :key="schedule.id"
            class="card"
            :style="{ '--card-color': colorHexMap[schedule.color] || '#6366f1' }"
          >
            <div class="card-accent" />
            <div class="card-body">
              <div class="card-header-row">
                <h3 class="card-title">{{ schedule.title }}</h3>
                <span class="color-badge" :style="{ background: colorHexMap[schedule.color] || '#6366f1' }" />
              </div>
              <p v-if="schedule.description" class="card-desc">{{ schedule.description }}</p>
              <div class="card-meta">
                <span class="material-symbols-rounded meta-icon">schedule</span>
                <span class="meta-item">{{ formatDateTime(schedule.start_at) }}</span>
                <span class="material-symbols-rounded meta-arrow">arrow_right_alt</span>
                <span class="meta-item">{{ formatDateTime(schedule.end_at) }}</span>
              </div>
            </div>
            <div class="card-actions">
              <button class="btn-icon edit" title="編集" @click="openEditModal(schedule)">
                <span class="material-symbols-rounded">edit</span>
              </button>
              <button class="btn-icon del" title="削除" @click="deleteTarget = schedule">
                <span class="material-symbols-rounded">delete</span>
              </button>
            </div>
          </article>
        </div>
      </div>
    </main>

    <!-- 登録・編集モーダル -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="overlay" @click.self="showModal = false">
          <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-header">
              <h3>{{ editingSchedule ? 'スケジュール編集' : 'スケジュール作成' }}</h3>
              <button class="modal-close" @click="showModal = false">
                <span class="material-symbols-rounded">close</span>
              </button>
            </div>

            <form @submit.prevent="handleSubmit" class="modal-form">
              <div v-if="formServerError" class="alert-error">{{ formServerError }}</div>

              <div class="field">
                <label>タイトル <span class="req">*</span></label>
                <input
                  v-model="form.title" type="text"
                  :class="['input', { error: formErrors.title }]"
                  placeholder="スケジュールのタイトルを入力" required
                />
                <p v-if="formErrors.title" class="field-error">{{ formErrors.title[0] }}</p>
              </div>

              <div class="field">
                <label>説明 <span class="opt">（任意）</span></label>
                <textarea v-model="form.description" class="input textarea"
                  placeholder="詳細・メモ..." rows="3" />
              </div>

              <div class="field-row">
                <div class="field">
                  <label>開始日時 <span class="req">*</span></label>
                  <input v-model="form.start_at" type="datetime-local"
                    :class="['input', { error: formErrors.start_at }]" required />
                  <p v-if="formErrors.start_at" class="field-error">{{ formErrors.start_at[0] }}</p>
                </div>
                <div class="field">
                  <label>終了日時 <span class="req">*</span></label>
                  <input v-model="form.end_at" type="datetime-local"
                    :class="['input', { error: formErrors.end_at }]" required />
                  <p v-if="formErrors.end_at" class="field-error">{{ formErrors.end_at[0] }}</p>
                </div>
              </div>

              <div class="field">
                <label>カラーテーマ</label>
                <div class="color-picker">
                  <button
                    v-for="c in SCHEDULE_COLORS"
                    :key="c.value"
                    type="button"
                    :class="['color-dot', { selected: form.color === c.value }]"
                    :style="{ background: c.hex }"
                    :title="c.label"
                    @click="form.color = c.value"
                  >
                    <span v-if="form.color === c.value" class="material-symbols-rounded dot-check">check</span>
                  </button>
                </div>
              </div>

              <div class="modal-footer">
                <button
                  v-if="editingSchedule"
                  type="button"
                  class="btn-modal-delete"
                  @click="handleDeleteFromModal"
                >
                  <span class="material-symbols-rounded">delete_outline</span> 削除
                </button>
                <div class="modal-footer-right">
                  <button type="button" class="btn-cancel" @click="showModal = false">キャンセル</button>
                  <button type="submit" class="btn-save" :disabled="loading">
                    <span v-if="loading" class="spinner" />
                    {{ loading ? '保存中...' : '保存する' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- 削除確認モーダル -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteTarget" class="overlay" @click.self="deleteTarget = null">
          <div class="modal modal-sm" role="dialog" aria-modal="true">
            <div class="modal-header">
              <h3>スケジュールの削除</h3>
              <button class="modal-close" @click="deleteTarget = null">
                <span class="material-symbols-rounded">close</span>
              </button>
            </div>
            <p class="delete-desc">
              「<strong>{{ deleteTarget.title }}</strong>」を削除しますか？<br />
              この操作は取り消せません。
            </p>
            <div class="modal-footer">
              <div class="modal-footer-right">
                <button class="btn-cancel" @click="deleteTarget = null">キャンセル</button>
                <button class="btn-delete" :disabled="deleteLoading" @click="handleDelete">
                  <span v-if="deleteLoading" class="spinner" />
                  削除する
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.app { min-height: 100vh; background: #f8fafc; display: flex; flex-direction: column; }

/* ヘッダー */
.header {
  position: sticky; top: 0; z-index: 50;
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4f46e5 100%);
  box-shadow: 0 1px 0 rgba(255,255,255,0.06), 0 4px 16px rgba(0,0,0,0.2);
}
.header-inner {
  max-width: 1440px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 1.25rem; height: 64px;
}
@media (min-width: 1024px) { .header-inner { padding: 0 2.5rem; height: 68px; } }
.header-brand { display: flex; align-items: center; gap: 0.5rem; }
.brand-icon { font-size: 1.625rem !important; color: #a5b4fc; vertical-align: middle; }
.header-title { font-size: 1.2rem; font-weight: 800; color: #fff; letter-spacing: -0.01em; }
.header-right { display: flex; align-items: center; gap: 0.625rem; }
.user-chip {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.35rem 0.75rem 0.35rem 0.35rem;
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12);
  border-radius: 999px; text-decoration: none; color: #fff; font-size: 0.875rem;
  transition: background 0.2s;
}
.user-chip:hover { background: rgba(255,255,255,0.18); }
.avatar-img {
  width: 30px; height: 30px; border-radius: 50%;
  object-fit: cover; border: 2px solid rgba(255,255,255,0.4); flex-shrink: 0;
}
.avatar-initial {
  width: 30px; height: 30px; border-radius: 50%;
  background: rgba(165,180,252,0.25);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.875rem; font-weight: 700; flex-shrink: 0;
  border: 2px solid rgba(255,255,255,0.3);
}
.user-name { font-weight: 500; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
@media (max-width: 480px) { .user-name { display: none; } }
.btn-ghost {
  padding: 0.38rem 0.75rem;
  background: transparent; color: rgba(255,255,255,0.75);
  border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;
  font-size: 0.82rem; cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.btn-ghost:hover { background: rgba(255,255,255,0.1); color: #fff; }

/* メイン */
.main { flex: 1; max-width: 1440px; width: 100%; margin: 0 auto; padding: 1.25rem 1rem 2.5rem; }
@media (min-width: 768px) { .main { padding: 1.5rem 1.5rem 3rem; } }
@media (min-width: 1024px) { .main { padding: 2rem 2.5rem 3.5rem; } }

/* ツールバー */
.toolbar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1.25rem; gap: 0.75rem; flex-wrap: wrap;
}
.toolbar-left { display: flex; align-items: center; gap: 0.75rem; }
.view-tabs {
  display: flex; background: #fff; border: 1px solid #e2e8f0;
  border-radius: 10px; overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.tab {
  display: flex; align-items: center; gap: 0.375rem;
  padding: 0.55rem 1rem; border: none; background: transparent;
  font-size: 0.875rem; font-weight: 500; color: #64748b;
  cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.tab .material-symbols-rounded { font-size: 1.1rem !important; vertical-align: -0.15em; }
.tab.active { background: #6366f1; color: #fff; font-weight: 600; }
@media (max-width: 400px) { .tab-label { display: none; } .tab { padding: 0.55rem 0.75rem; } }
.schedule-count {
  font-size: 0.8rem; color: #94a3b8; font-weight: 500;
  background: #f1f5f9; padding: 0.25rem 0.625rem; border-radius: 999px; white-space: nowrap;
}
.btn-create {
  display: flex; align-items: center; gap: 0.3rem;
  padding: 0.65rem 1.25rem;
  background: #6366f1; color: #fff; border: none; border-radius: 10px;
  font-size: 0.9rem; font-weight: 700; cursor: pointer;
  box-shadow: 0 4px 12px rgba(99,102,241,0.3); transition: all 0.2s; white-space: nowrap;
}
.btn-create .material-symbols-rounded { font-size: 1.15rem !important; vertical-align: -0.15em; }
.btn-create:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,0.38); }
@media (max-width: 400px) { .btn-create-label { display: none; } }

/* パネル */
.panel {
  background: #fff; border-radius: 16px; padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(99,102,241,0.06);
  border: 1px solid #e2e8f0;
}
@media (min-width: 1024px) { .panel { padding: 2rem; } }

/* 空状態 */
.empty {
  text-align: center; padding: 4rem 1rem;
  display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
}
.empty-illustration {
  font-size: 3.5rem !important;
  color: #c7d2fe;
  vertical-align: middle !important;
}
.empty-title { font-size: 1.1rem; font-weight: 700; color: #374151; }
.empty-sub { font-size: 0.875rem; color: #94a3b8; }
.empty-btn { margin-top: 1rem; }

/* カードリスト */
.list { display: flex; flex-direction: column; gap: 0.875rem; }
.card {
  display: flex; align-items: stretch;
  background: #fff; border: 1px solid #f1f5f9; border-radius: 12px;
  overflow: hidden; transition: box-shadow 0.2s, transform 0.2s;
}
.card:hover { box-shadow: 0 4px 20px rgba(99,102,241,0.1); transform: translateY(-1px); }
.card-accent { width: 4px; flex-shrink: 0; background: var(--card-color, #6366f1); }
.card-body { flex: 1; padding: 1rem 1.25rem; min-width: 0; }
.card-header-row { display: flex; align-items: center; gap: 0.625rem; margin-bottom: 0.375rem; }
.card-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
.color-badge { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.card-desc { font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem; white-space: pre-wrap; line-height: 1.5; }
.card-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 0.375rem; font-size: 0.82rem; color: #94a3b8; }
.meta-icon { font-size: 1rem !important; color: #94a3b8; vertical-align: -0.18em; }
.meta-arrow { font-size: 1rem !important; color: #cbd5e1; vertical-align: -0.18em; }
.meta-item { display: inline; }
.card-actions {
  display: flex; flex-direction: column; gap: 0.375rem;
  justify-content: center; padding: 0.75rem; flex-shrink: 0;
}
.btn-icon {
  width: 34px; height: 34px; border: none; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background 0.2s;
}
.btn-icon .material-symbols-rounded { font-size: 1.1rem !important; }
.btn-icon.edit { background: #eef2ff; color: #6366f1; }
.btn-icon.edit:hover { background: #e0e7ff; }
.btn-icon.del { background: #fef2f2; color: #ef4444; }
.btn-icon.del:hover { background: #fee2e2; }

/* モーダル共通 */
.overlay {
  position: fixed; inset: 0; background: rgba(15,23,42,0.55);
  display: flex; align-items: center; justify-content: center;
  z-index: 100; padding: 1rem; backdrop-filter: blur(2px);
}
.modal {
  background: #fff; border-radius: 20px; width: 100%; max-width: 600px;
  box-shadow: 0 24px 64px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;
}
.modal-sm { max-width: 420px; }
@media (max-width: 640px) {
  .overlay { padding: 0.5rem; align-items: flex-end; }
  .modal { border-radius: 16px 16px 0 0; max-height: 95vh; }
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.5rem 1.75rem 0;
}
.modal-header h3 { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
.modal-close {
  width: 32px; height: 32px; border: none; background: #f1f5f9;
  border-radius: 8px; cursor: pointer; color: #64748b; transition: background 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.modal-close .material-symbols-rounded { font-size: 1.1rem !important; }
.modal-close:hover { background: #e2e8f0; }
.modal-form { padding: 1.375rem 1.75rem; display: flex; flex-direction: column; gap: 1.125rem; }
.delete-desc { padding: 1rem 1.75rem; color: #374151; font-size: 0.95rem; line-height: 1.7; }
.modal-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 1.75rem 1.5rem; gap: 0.75rem;
}
.modal-footer-right { display: flex; gap: 0.75rem; margin-left: auto; }

/* フォーム */
.field { display: flex; flex-direction: column; gap: 0.5rem; }
.field label { font-size: 0.875rem; font-weight: 600; color: #374151; }
.req { color: #6366f1; }
.opt { font-weight: 400; color: #94a3b8; font-size: 0.8rem; }
.input {
  padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 10px;
  font-size: 0.95rem; color: #0f172a; background: #f8fafc; outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.input:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.input.error { border-color: #ef4444; }
.textarea { resize: vertical; font-family: inherit; min-height: 84px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem; }
@media (max-width: 560px) { .field-row { grid-template-columns: 1fr; } }
.field-error { font-size: 0.78rem; color: #ef4444; }
.alert-error {
  padding: 0.875rem 1rem; background: #fef2f2; border: 1px solid #fecaca;
  border-radius: 10px; color: #ef4444; font-size: 0.875rem;
}

/* カラーピッカー */
.color-picker { display: flex; gap: 0.625rem; flex-wrap: wrap; padding: 0.25rem 0; }
.color-dot {
  width: 32px; height: 32px; border-radius: 50%;
  border: 2.5px solid transparent; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: transform 0.15s, box-shadow 0.15s;
}
.color-dot:hover { transform: scale(1.12); }
.color-dot.selected {
  border-color: #0f172a; box-shadow: 0 0 0 3px rgba(15,23,42,0.15); transform: scale(1.08);
}
.dot-check { font-size: 0.85rem !important; color: #fff !important; font-weight: 800 !important; text-shadow: 0 1px 2px rgba(0,0,0,0.4); }

/* ボタン */
.btn-cancel {
  padding: 0.65rem 1.25rem; background: transparent; color: #64748b;
  border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; cursor: pointer; transition: background 0.2s;
}
.btn-cancel:hover { background: #f8fafc; }
.btn-save {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.65rem 1.5rem; background: #6366f1; color: #fff;
  border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer;
  box-shadow: 0 4px 12px rgba(99,102,241,0.3); transition: all 0.2s;
}
.btn-save:hover:not(:disabled) { background: #4f46e5; }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-delete {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.65rem 1.25rem; background: #ef4444; color: #fff;
  border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer;
}
.btn-delete:hover:not(:disabled) { background: #dc2626; }
.btn-delete:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-modal-delete {
  display: flex; align-items: center; gap: 0.375rem;
  padding: 0.6rem 1rem; background: #fef2f2; color: #ef4444;
  border: 1.5px solid #fecaca; border-radius: 10px; font-size: 0.875rem; font-weight: 600; cursor: pointer;
  transition: background 0.2s; white-space: nowrap;
}
.btn-modal-delete .material-symbols-rounded { font-size: 1rem !important; }
.btn-modal-delete:hover { background: #fee2e2; }
.spinner {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-active .modal, .modal-leave-active .modal { transition: transform 0.22s, opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal, .modal-leave-to .modal { transform: scale(0.96) translateY(10px); opacity: 0; }
@media (max-width: 640px) {
  .modal-enter-from .modal { transform: translateY(100%); }
  .modal-leave-to .modal { transform: translateY(100%); }
}
</style>
