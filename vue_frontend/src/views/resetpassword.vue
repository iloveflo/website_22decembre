<template>
  <div class="reset-wrapper">
    <!-- Toast Notification -->
    <Transition name="toast">
      <div v-if="toast.show" class="toast" :class="toast.type">
        <span class="toast-icon">{{ toast.type === 'success' ? '✓' : '!' }}</span>
        <span class="toast-msg">{{ toast.message }}</span>
      </div>
    </Transition>

    <div class="reset-container">
      <div class="icon-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>

      <h2>Đặt lại mật khẩu</h2>
      <p>Vui lòng nhập mật khẩu mới của bạn.</p>

      <form @submit.prevent="submitReset">
        <div class="form-group" :class="{ 'has-error': errors.password }">
          <label for="password">Mật khẩu mới</label>
          <div class="input-wrap">
            <input
              id="password"
              :type="showPwd ? 'text' : 'password'"
              v-model="password"
              placeholder="Ít nhất 8 ký tự"
              @input="errors.password = ''"
            />
            <button type="button" class="eye-btn" @click="showPwd = !showPwd" tabindex="-1">
              {{ showPwd ? '🙈' : '👁' }}
            </button>
          </div>
          <span v-if="errors.password" class="field-error">{{ errors.password }}</span>
        </div>

        <div class="form-group" :class="{ 'has-error': errors.confirm }">
          <label for="password_confirmation">Nhập lại mật khẩu</label>
          <div class="input-wrap">
            <input
              id="password_confirmation"
              :type="showConfirm ? 'text' : 'password'"
              v-model="password_confirmation"
              placeholder="Nhập lại mật khẩu"
              @input="errors.confirm = ''"
            />
            <button type="button" class="eye-btn" @click="showConfirm = !showConfirm" tabindex="-1">
              {{ showConfirm ? '🙈' : '👁' }}
            </button>
          </div>
          <span v-if="errors.confirm" class="field-error">{{ errors.confirm }}</span>
        </div>

        <button type="submit" :disabled="loading" class="btn-submit">
          <span v-if="loading" class="spinner"></span>
          <span>{{ loading ? 'Đang xử lý...' : 'Đặt lại mật khẩu' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import axios from "axios"

const route = useRoute()
const router = useRouter()

const password = ref("")
const password_confirmation = ref("")
const code = ref("")
const loading = ref(false)
const showPwd = ref(false)
const showConfirm = ref(false)

const errors = reactive({ password: '', confirm: '' })
const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null

function showToast(message, type = 'success') {
  clearTimeout(toastTimer)
  toast.message = message
  toast.type = type
  toast.show = true
  toastTimer = setTimeout(() => { toast.show = false }, 4500)
}

onMounted(() => {
  code.value = route.query.code || ""
  if (!code.value) {
    showToast("Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!", "error")
    setTimeout(() => router.push("/"), 2500)
  }
})

const submitReset = async () => {
  errors.password = ''
  errors.confirm = ''

  if (password.value.length < 8) {
    errors.password = "Mật khẩu phải có ít nhất 8 ký tự!"
    return
  }
  if (password.value !== password_confirmation.value) {
    errors.confirm = "Mật khẩu nhập lại không khớp!"
    return
  }

  try {
    loading.value = true
    const response = await axios.post("/reset-password", {
      code: code.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    showToast(response.data.message || "Mật khẩu đã được đặt lại thành công!", "success")
    setTimeout(() => router.push("/login"), 2000)
  } catch (err) {
    const msg = err.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại"
    showToast(msg, "error")
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.reset-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #fff;
  padding: 40px;
}

/* ── Toast ── */
.toast {
  position: fixed;
  top: 28px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 22px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 6px 24px rgba(0,0,0,0.15);
  min-width: 280px;
  max-width: 440px;
  pointer-events: none;
}
.toast.success { background: #1a7a4a; color: #fff; }
.toast.error   { background: #c0392b; color: #fff; }
.toast-icon { font-size: 18px; flex-shrink: 0; }

.toast-enter-active, .toast-leave-active { transition: all 0.35s cubic-bezier(.4,0,.2,1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(-16px); }

/* ── Card ── */
.reset-container {
  width: 100%;
  max-width: 420px;
  padding: 44px 40px;
  background: #fff;
  border: 2px solid #E6E0D8;
  box-shadow: 8px 8px 0 #000;
  text-align: center;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #f5f1ee;
  color: #A08B7A;
  margin-bottom: 18px;
}

.reset-container h2 {
  font-size: 24px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #333;
  margin-bottom: 8px;
}

.reset-container p {
  font-size: 14px;
  color: #777;
  margin-bottom: 28px;
  line-height: 1.6;
}

/* ── Form ── */
.form-group {
  text-align: left;
  margin-bottom: 20px;
}

.form-group.has-error .input-wrap input {
  border-color: #c0392b;
}

label {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: #333;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrap input {
  width: 100%;
  padding: 12px 42px 12px 14px;
  border: 2px solid #E6E0D8;
  background: #fafafa;
  color: #333;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s, background 0.2s;
  box-sizing: border-box;
}

.input-wrap input:focus {
  border-color: #A08B7A;
  background: #fff;
}

.eye-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  padding: 0;
  line-height: 1;
  color: #999;
}

.field-error {
  display: block;
  margin-top: 5px;
  font-size: 12px;
  color: #c0392b;
  font-weight: 600;
}

/* ── Button ── */
.btn-submit {
  width: 100%;
  padding: 13px;
  background: #A08B7A;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 6px;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-submit:hover:not(:disabled) { background: #222; }
.btn-submit:disabled { opacity: 0.65; cursor: not-allowed; }

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
