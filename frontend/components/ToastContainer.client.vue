<script setup lang="ts">
const { toasts } = useToast()

const iconMap: Record<string, string> = {
  success: 'check_circle',
  error: 'cancel',
  info: 'info',
}
</script>

<template>
  <Teleport to="body">
    <div class="toast-wrap" aria-live="polite">
      <TransitionGroup name="toast" tag="div" class="toast-list">
        <div v-for="t in toasts" :key="t.id" :class="['toast', t.type]">
          <span class="material-symbols-rounded toast-icon">{{ iconMap[t.type] }}</span>
          <span class="toast-msg">{{ t.message }}</span>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-wrap {
  position: fixed;
  top: 1.25rem;
  right: 1.25rem;
  z-index: 9999;
  pointer-events: none;
}
.toast-list {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  align-items: flex-end;
}
.toast {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.75rem 1.125rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  min-width: 240px;
  max-width: 360px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.14);
  pointer-events: auto;
  border-left: 4px solid transparent;
}
.toast.success { background: #f0fdf4; color: #166534; border-left-color: #22c55e; }
.toast.error   { background: #fef2f2; color: #991b1b; border-left-color: #ef4444; }
.toast.info    { background: #eff6ff; color: #1e40af; border-left-color: #3b82f6; }
.toast-icon {
  font-size: 1.25rem !important;
  flex-shrink: 0;
}
.toast.success .toast-icon { color: #22c55e; }
.toast.error   .toast-icon { color: #ef4444; }
.toast.info    .toast-icon { color: #3b82f6; }
.toast-msg { flex: 1; line-height: 1.4; }

.toast-enter-active { transition: all 0.28s cubic-bezier(0.34, 1.2, 0.64, 1); }
.toast-leave-active { transition: all 0.22s ease-in; }
.toast-enter-from { opacity: 0; transform: translateX(100%) scale(0.9); }
.toast-leave-to   { opacity: 0; transform: translateX(100%) scale(0.95); }
.toast-move { transition: transform 0.25s ease; }
</style>
