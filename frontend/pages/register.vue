<script setup lang="ts">
definePageMeta({ middleware: 'guest' })

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({ name: '', email: '', password: '', passwordConfirmation: '' })
const errors = ref<Record<string, string[]>>({})
const serverError = ref('')
const loading = ref(false)

const handleRegister = async () => {
  errors.value = {}
  serverError.value = ''
  loading.value = true
  try {
    await authStore.register(form.name, form.email, form.password, form.passwordConfirmation)
    await router.push('/schedules')
  } catch (err: any) {
    if (err.data?.errors) errors.value = err.data.errors
    else serverError.value = err.data?.message || '登録に失敗しました。'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <!-- スマホ用トップバナー（デスクトップでは非表示） -->
    <div class="mobile-top">
      <span class="material-symbols-rounded mobile-logo">calendar_month</span>
      <h1 class="mobile-brand">Schedule</h1>
      <p class="mobile-tagline">無料で始めて、スケジュールを<br />スマートに管理しよう</p>
      <div class="mobile-chips">
        <span class="chip">
          <span class="material-symbols-rounded">calendar_view_month</span>カレンダー
        </span>
        <span class="chip">
          <span class="material-symbols-rounded">palette</span>カラー分類
        </span>
        <span class="chip">
          <span class="material-symbols-rounded">drag_indicator</span>ドラッグ操作
        </span>
      </div>
    </div>

    <!-- デスクトップ用左パネル -->
    <div class="left-panel">
      <div class="brand">
        <span class="material-symbols-rounded brand-icon-lg">calendar_month</span>
        <h1 class="brand-name">Schedule</h1>
        <p class="brand-desc">今すぐ無料でアカウントを作成して<br />スケジュール管理を始めよう</p>
      </div>
      <ul class="feature-list">
        <li>
          <span class="material-symbols-rounded feat-icon">calendar_view_month</span>
          月・週・日のカレンダービュー
        </li>
        <li>
          <span class="material-symbols-rounded feat-icon">palette</span>
          カラーテーマで分類整理
        </li>
        <li>
          <span class="material-symbols-rounded feat-icon">drag_indicator</span>
          ドラッグ＆ドロップで直感操作
        </li>
      </ul>
    </div>

    <!-- フォームエリア -->
    <div class="right-panel">
      <div class="card">
        <h2 class="card-title">新規登録</h2>
        <p class="card-sub">無料アカウントを作成</p>

        <form @submit.prevent="handleRegister" class="form">
          <div v-if="serverError" class="alert-error">{{ serverError }}</div>

          <div class="field">
            <label for="name">お名前</label>
            <input id="name" v-model="form.name" type="text"
              :class="['input', { error: errors.name }]"
              placeholder="山田 太郎" autocomplete="name" required />
            <p v-if="errors.name" class="field-error">{{ errors.name[0] }}</p>
          </div>

          <div class="field">
            <label for="email">メールアドレス</label>
            <input id="email" v-model="form.email" type="email"
              :class="['input', { error: errors.email }]"
              placeholder="you@example.com" autocomplete="email" required />
            <p v-if="errors.email" class="field-error">{{ errors.email[0] }}</p>
          </div>

          <div class="field">
            <label for="password">パスワード <span class="hint">（8文字以上）</span></label>
            <input id="password" v-model="form.password" type="password"
              :class="['input', { error: errors.password }]"
              placeholder="••••••••" autocomplete="new-password" required />
            <p v-if="errors.password" class="field-error">{{ errors.password[0] }}</p>
          </div>

          <div class="field">
            <label for="password_confirmation">パスワード（確認）</label>
            <input id="password_confirmation" v-model="form.passwordConfirmation" type="password"
              class="input" placeholder="••••••••" autocomplete="new-password" required />
          </div>

          <button type="submit" class="btn-submit" :disabled="loading">
            <span v-if="loading" class="spinner" />
            {{ loading ? '登録中...' : 'アカウントを作成' }}
          </button>
        </form>

        <p class="switch-link">
          すでにアカウントをお持ちの方は
          <NuxtLink to="/login">ログイン</NuxtLink>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ===== ページ全体 ===== */
.page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
@media (min-width: 1024px) {
  .page { flex-direction: row; }
}

/* ===== スマホ用トップバナー ===== */
.mobile-top {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2.5rem 1.5rem 2.25rem;
  background: linear-gradient(150deg, #1e1b4b 0%, #312e81 40%, #4f46e5 100%);
  color: #fff;
}
@media (min-width: 1024px) {
  .mobile-top { display: none; }
}

.mobile-logo {
  font-size: 2.75rem !important;
  color: #a5b4fc;
  margin-bottom: 0.625rem;
  vertical-align: middle;
}
.mobile-brand {
  font-size: 1.875rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 0.375rem;
}
.mobile-tagline {
  font-size: 0.9rem;
  color: #c7d2fe;
  margin-bottom: 1.25rem;
  line-height: 1.6;
}
.mobile-chips {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.5rem;
}
.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 999px;
  font-size: 0.78rem;
  color: #e0e7ff;
  font-weight: 500;
}
.chip .material-symbols-rounded {
  font-size: 0.95rem !important;
  color: #a5b4fc;
}

/* ===== デスクトップ用左パネル ===== */
.left-panel {
  display: none;
  flex-direction: column;
  justify-content: center;
  padding: 3rem;
  background: linear-gradient(150deg, #1e1b4b 0%, #312e81 40%, #4f46e5 100%);
  color: #fff;
}
@media (min-width: 1024px) {
  .left-panel { display: flex; flex: 0 0 480px; }
}

.brand { margin-bottom: 3rem; }
.brand-icon-lg {
  font-size: 3rem !important;
  color: #a5b4fc;
  display: block;
  margin-bottom: 1rem;
  vertical-align: middle;
}
.brand-name {
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 1rem;
}
.brand-desc { font-size: 1.1rem; color: #c7d2fe; line-height: 1.7; }
.feature-list { list-style: none; display: flex; flex-direction: column; gap: 1rem; }
.feature-list li {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  font-size: 1rem;
  color: #e0e7ff;
  padding: 0.875rem 1.25rem;
  background: rgba(255, 255, 255, 0.07);
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.feat-icon {
  font-size: 1.375rem !important;
  color: #a5b4fc;
  flex-shrink: 0;
  vertical-align: middle;
}

/* ===== フォームエリア ===== */
.right-panel {
  flex: 1;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 2rem 1.25rem 2.5rem;
  background: #f8fafc;
}
@media (min-width: 1024px) {
  .right-panel {
    align-items: center;
    padding: 2rem 1.5rem;
  }
}

.card {
  width: 100%;
  max-width: 440px;
  background: #fff;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04), 0 20px 40px rgba(99, 102, 241, 0.08);
  border: 1px solid #e2e8f0;
}
@media (max-width: 480px) {
  .card { padding: 1.75rem 1.25rem; border-radius: 16px; }
}

.card-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 0.375rem;
  letter-spacing: -0.02em;
}
.card-sub { font-size: 0.95rem; color: #64748b; margin-bottom: 2rem; }
.form { display: flex; flex-direction: column; gap: 1.25rem; }
.field { display: flex; flex-direction: column; gap: 0.5rem; }
.field label { font-size: 0.875rem; font-weight: 600; color: #374151; }
.hint { font-weight: 400; color: #94a3b8; font-size: 0.82rem; }
.input {
  padding: 0.75rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  color: #0f172a;
  background: #f8fafc;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.input:focus {
  border-color: #6366f1;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}
.input.error { border-color: #ef4444; }
.field-error { font-size: 0.8rem; color: #ef4444; }
.alert-error {
  padding: 0.875rem 1rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  color: #ef4444;
  font-size: 0.875rem;
}
.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
  margin-top: 0.375rem;
}
.btn-submit:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.38);
}
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.switch-link {
  text-align: center;
  margin-top: 1.75rem;
  font-size: 0.9rem;
  color: #64748b;
}
.switch-link a { color: #6366f1; font-weight: 700; text-decoration: none; }
.switch-link a:hover { text-decoration: underline; }
</style>
