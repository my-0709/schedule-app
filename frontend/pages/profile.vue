<script setup lang="ts">
definePageMeta({ middleware: "auth" });

const authStore = useAuthStore();
const router = useRouter();

const profileForm = reactive({ name: "", email: "" });
const profileErrors = ref<Record<string, string[]>>({});
const profileSuccess = ref("");
const profileLoading = ref(false);

const passwordForm = reactive({
  currentPassword: "",
  newPassword: "",
  newPasswordConfirmation: "",
});
const passwordErrors = ref<Record<string, string[]>>({});
const passwordSuccess = ref("");
const passwordLoading = ref(false);

const avatarFile = ref<File | null>(null);
const avatarPreview = ref<string | null>(null);
const avatarLoading = ref(false);
const avatarError = ref("");

const deleteForm = reactive({ password: "" });
const deleteErrors = ref<Record<string, string[]>>({});
const showDeleteConfirm = ref(false);
const deleteLoading = ref(false);

const avatarInitial = computed(
  () => authStore.user?.name?.charAt(0).toUpperCase() || "?",
);

onMounted(async () => {
  await authStore.fetchMe();
  profileForm.name = authStore.user?.name || "";
  profileForm.email = authStore.user?.email || "";
});

const onAvatarChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;
  if (file.size > 2 * 1024 * 1024) {
    avatarError.value = "画像サイズは2MB以下にしてください。";
    return;
  }
  avatarFile.value = file;
  avatarError.value = "";
  const reader = new FileReader();
  reader.onload = (ev) => {
    avatarPreview.value = ev.target?.result as string;
  };
  reader.readAsDataURL(file);
};

const handleUploadAvatar = async () => {
  if (!avatarFile.value) return;
  avatarLoading.value = true;
  avatarError.value = "";
  try {
    await authStore.uploadAvatar(avatarFile.value);
    avatarFile.value = null;
    avatarPreview.value = null;
  } catch (err: any) {
    avatarError.value = err.data?.message || "アップロードに失敗しました。";
  } finally {
    avatarLoading.value = false;
  }
};

const handleUpdateProfile = async () => {
  profileErrors.value = {};
  profileSuccess.value = "";
  profileLoading.value = true;
  try {
    await authStore.updateProfile(profileForm.name, profileForm.email);
    profileSuccess.value = "プロフィールを更新しました。";
  } catch (err: any) {
    if (err.data?.errors) profileErrors.value = err.data.errors;
  } finally {
    profileLoading.value = false;
  }
};

const handleChangePassword = async () => {
  passwordErrors.value = {};
  passwordSuccess.value = "";
  passwordLoading.value = true;
  try {
    await authStore.changePassword(
      passwordForm.currentPassword,
      passwordForm.newPassword,
      passwordForm.newPasswordConfirmation,
    );
    passwordSuccess.value = "パスワードを変更しました。";
    passwordForm.currentPassword = "";
    passwordForm.newPassword = "";
    passwordForm.newPasswordConfirmation = "";
  } catch (err: any) {
    if (err.data?.errors) passwordErrors.value = err.data.errors;
  } finally {
    passwordLoading.value = false;
  }
};

const handleDeleteAccount = async () => {
  deleteErrors.value = {};
  deleteLoading.value = true;
  try {
    await authStore.deleteAccount(deleteForm.password);
    await router.push("/login");
  } catch (err: any) {
    if (err.data?.errors) deleteErrors.value = err.data.errors;
  } finally {
    deleteLoading.value = false;
  }
};
</script>

<template>
  <div class="app">
    <header class="header">
      <div class="header-inner">
        <NuxtLink to="/schedules" class="back-btn">
          <span class="material-symbols-rounded">arrow_back</span> 戻る
        </NuxtLink>
        <div class="header-brand">
          <span class="material-symbols-rounded brand-icon">calendar_month</span>
          <span class="header-title">プロフィール設定</span>
        </div>
        <div style="width: 160px" />
      </div>
    </header>

    <main class="main">
      <div class="sections">
        <!-- アバター -->
        <section class="section">
          <div class="section-header">
            <h2 class="section-title">プロフィール画像</h2>
            <p class="section-desc">アプリ上で表示されるあなたのアイコン画像</p>
          </div>
          <div class="avatar-row">
            <div class="avatar-wrap">
              <img
                v-if="avatarPreview || authStore.user?.avatar"
                :src="avatarPreview || authStore.user?.avatar || ''"
                class="avatar-img"
                alt="avatar"
              />
              <span v-else class="avatar-initial">{{ avatarInitial }}</span>
            </div>
            <div class="avatar-upload">
              <label class="upload-label">
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/gif,image/webp"
                  class="file-input"
                  @change="onAvatarChange"
                />
                <span class="material-symbols-rounded upload-icon">cloud_upload</span>
                画像をアップロード
              </label>
              <p class="upload-hint">JPG・PNG・GIF・WebP、最大 2MB</p>
              <p v-if="avatarError" class="field-error">{{ avatarError }}</p>
              <button
                v-if="avatarFile"
                class="btn-primary"
                :disabled="avatarLoading"
                @click="handleUploadAvatar"
              >
                <span v-if="avatarLoading" class="spinner" />
                {{ avatarLoading ? "アップロード中..." : "アップロードする" }}
              </button>
            </div>
          </div>
        </section>

        <!-- プロフィール編集 -->
        <section class="section">
          <div class="section-header">
            <h2 class="section-title">基本情報</h2>
            <p class="section-desc">名前とメールアドレスを変更できます</p>
          </div>
          <form @submit.prevent="handleUpdateProfile" class="form">
            <div v-if="profileSuccess" class="alert-success">
              {{ profileSuccess }}
            </div>
            <div class="field-row">
              <div class="field">
                <label>お名前</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  :class="['input', { error: profileErrors.name }]"
                  required
                />
                <p v-if="profileErrors.name" class="field-error">
                  {{ profileErrors.name[0] }}
                </p>
              </div>
              <div class="field">
                <label>メールアドレス</label>
                <input
                  v-model="profileForm.email"
                  type="email"
                  :class="['input', { error: profileErrors.email }]"
                  required
                />
                <p v-if="profileErrors.email" class="field-error">
                  {{ profileErrors.email[0] }}
                </p>
              </div>
            </div>
            <div class="form-actions">
              <button
                type="submit"
                class="btn-primary"
                :disabled="profileLoading"
              >
                <span v-if="profileLoading" class="spinner" />
                {{ profileLoading ? "更新中..." : "変更を保存" }}
              </button>
            </div>
          </form>
        </section>

        <!-- パスワード変更 -->
        <section class="section">
          <div class="section-header">
            <h2 class="section-title">パスワード変更</h2>
            <p class="section-desc">
              安全のため定期的にパスワードを変更してください
            </p>
          </div>
          <form @submit.prevent="handleChangePassword" class="form">
            <div v-if="passwordSuccess" class="alert-success">
              {{ passwordSuccess }}
            </div>
            <div class="field">
              <label>現在のパスワード</label>
              <input
                v-model="passwordForm.currentPassword"
                type="password"
                :class="['input', { error: passwordErrors.current_password }]"
                required
              />
              <p v-if="passwordErrors.current_password" class="field-error">
                {{ passwordErrors.current_password[0] }}
              </p>
            </div>
            <div class="field-row">
              <div class="field">
                <label
                  >新しいパスワード
                  <span class="hint">（8文字以上）</span></label
                >
                <input
                  v-model="passwordForm.newPassword"
                  type="password"
                  :class="['input', { error: passwordErrors.new_password }]"
                  required
                />
                <p v-if="passwordErrors.new_password" class="field-error">
                  {{ passwordErrors.new_password[0] }}
                </p>
              </div>
              <div class="field">
                <label>新しいパスワード（確認）</label>
                <input
                  v-model="passwordForm.newPasswordConfirmation"
                  type="password"
                  class="input"
                  required
                />
              </div>
            </div>
            <div class="form-actions">
              <button
                type="submit"
                class="btn-primary"
                :disabled="passwordLoading"
              >
                <span v-if="passwordLoading" class="spinner" />
                {{ passwordLoading ? "変更中..." : "パスワードを変更" }}
              </button>
            </div>
          </form>
        </section>

        <!-- 危険ゾーン（退会） -->
        <section class="section section-danger">
          <div class="section-header">
            <h2 class="section-title danger-title">アカウントの削除</h2>
            <p class="section-desc">
              退会するとすべてのデータが完全に削除されます。この操作は取り消せません。
            </p>
          </div>
          <button class="btn-danger" @click="showDeleteConfirm = true">
            退会する
          </button>
        </section>
      </div>
    </main>

    <!-- 退会確認モーダル -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="showDeleteConfirm"
          class="overlay"
          @click.self="showDeleteConfirm = false"
        >
          <div class="modal" role="dialog" aria-modal="true">
            <div class="modal-header">
              <h3>退会の確認</h3>
              <button class="modal-close" @click="showDeleteConfirm = false">
                <span class="material-symbols-rounded">close</span>
              </button>
            </div>
            <p class="modal-body">
              本当に退会しますか？<br />
              すべてのデータが削除され、復元できません。
            </p>
            <form
              @submit.prevent="handleDeleteAccount"
              style="padding: 0 1.75rem 1.5rem"
            >
              <div class="field" style="margin-bottom: 1.25rem">
                <label>確認のためパスワードを入力</label>
                <input
                  v-model="deleteForm.password"
                  type="password"
                  :class="['input', { error: deleteErrors.password }]"
                  required
                />
                <p v-if="deleteErrors.password" class="field-error">
                  {{ deleteErrors.password[0] }}
                </p>
              </div>
              <div
                style="display: flex; justify-content: flex-end; gap: 0.75rem"
              >
                <button
                  type="button"
                  class="btn-cancel"
                  @click="showDeleteConfirm = false"
                >
                  キャンセル
                </button>
                <button
                  type="submit"
                  class="btn-danger"
                  :disabled="deleteLoading"
                >
                  {{ deleteLoading ? "処理中..." : "退会する" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.app {
  min-height: 100vh;
  background: #f8fafc;
}
.header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4f46e5 100%);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}
.header-inner {
  max-width: 1000px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  height: 68px;
}
.header-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.brand-icon {
  font-size: 1.5rem !important;
  color: #a5b4fc;
  vertical-align: middle;
}
.header-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #fff;
}
.back-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  width: 160px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s;
}
.back-btn:hover {
  color: #fff;
}

.main {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem 1.25rem 3rem;
}
@media (min-width: 768px) {
  .main {
    padding: 2.5rem 2rem 4rem;
  }
}

.sections {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid #e2e8f0;
  box-shadow:
    0 1px 3px rgba(0, 0, 0, 0.04),
    0 8px 24px rgba(99, 102, 241, 0.05);
}
.section-danger {
  border-color: #fecaca;
  box-shadow:
    0 1px 3px rgba(0, 0, 0, 0.04),
    0 4px 16px rgba(239, 68, 68, 0.08);
}
.section-header {
  margin-bottom: 1.5rem;
}
.section-title {
  font-size: 1.1rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 0.25rem;
}
.danger-title {
  color: #dc2626;
}
.section-desc {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
}

/* アバター */
.avatar-row {
  display: flex;
  align-items: center;
  gap: 1.75rem;
  flex-wrap: wrap;
}
.avatar-wrap {
  width: 88px;
  height: 88px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  border: 3px solid #e0e7ff;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
}
.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.avatar-initial {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: #fff;
  font-size: 2.25rem;
  font-weight: 800;
}
.avatar-upload {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}
.upload-label {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.6rem 1.125rem;
  background: #f8fafc;
  border: 1.5px dashed #c7d2fe;
  border-radius: 10px;
  font-size: 0.875rem;
  color: #4f46e5;
  font-weight: 600;
  cursor: pointer;
  transition:
    background 0.2s,
    border-color 0.2s;
}
.upload-label:hover {
  background: #eef2ff;
  border-color: #a5b4fc;
}
.upload-icon {
  font-size: 1.2rem !important;
  vertical-align: -0.2em;
}
.file-input {
  display: none;
}
.upload-hint {
  font-size: 0.78rem;
  color: #94a3b8;
}

/* フォーム */
.form {
  display: flex;
  flex-direction: column;
  gap: 1.125rem;
}
.field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.field label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}
.hint {
  font-weight: 400;
  color: #94a3b8;
  font-size: 0.8rem;
}
.input {
  padding: 0.75rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  color: #0f172a;
  background: #f8fafc;
  outline: none;
  transition:
    border-color 0.2s,
    box-shadow 0.2s,
    background 0.2s;
}
.input:focus {
  border-color: #6366f1;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.input.error {
  border-color: #ef4444;
}
.field-error {
  font-size: 0.78rem;
  color: #ef4444;
}
.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
@media (max-width: 600px) {
  .field-row {
    grid-template-columns: 1fr;
  }
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  padding-top: 0.375rem;
}
.alert-success {
  padding: 0.875rem 1rem;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 10px;
  color: #15803d;
  font-size: 0.875rem;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1.5rem;
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  transition: all 0.2s;
}
.btn-primary:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-1px);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.btn-danger {
  padding: 0.7rem 1.5rem;
  background: #ef4444;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}
.btn-cancel {
  padding: 0.7rem 1.375rem;
  background: transparent;
  color: #64748b;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.9rem;
  cursor: pointer;
}
.spinner {
  width: 15px;
  height: 15px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* モーダル */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 1rem;
  backdrop-filter: blur(2px);
}
.modal {
  background: #fff;
  border-radius: 20px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 1.75rem 1rem;
}
.modal-header h3 {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
}
.modal-close {
  width: 32px;
  height: 32px;
  border: none;
  background: #f1f5f9;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  color: #64748b;
}
.modal-body {
  padding: 0 1.75rem 1.25rem;
  color: #374151;
  font-size: 0.95rem;
  line-height: 1.7;
}
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s;
}
.modal-enter-active .modal,
.modal-leave-active .modal {
  transition:
    transform 0.2s,
    opacity 0.2s;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .modal,
.modal-leave-to .modal {
  transform: scale(0.96) translateY(8px);
  opacity: 0;
}
</style>
