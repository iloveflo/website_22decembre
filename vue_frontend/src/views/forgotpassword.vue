<template>
  <div class="forgot-wrapper">
    <!-- Toast Notification -->
    <Transition name="toast">
      <div v-if="toast.show" class="toast" :class="toast.type">
        <span class="toast-icon">{{ toast.type === 'success' ? '✓' : '!' }}</span>
        <span class="toast-msg">{{ toast.message }}</span>
      </div>
    </Transition>

    <div class="forgot-box">
      <div class="icon-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>

      <h2 class="title">Quên mật khẩu</h2>
      <p class="subtitle">
        Nhập email của bạn để nhận liên kết đặt lại mật khẩu.
      </p>

      <form @submit.prevent="submitEmail" class="form">
        <div class="form-group" :class="{ 'has-error': fieldError }">
          <label>Email</label>
          <input 
            type="email" 
            v-model="email" 
            class="input"
            placeholder="example@gmail.com"
            @input="fieldError = ''"
          />
          <span v-if="fieldError" class="field-error">{{ fieldError }}</span>
        </div>

        <button type="submit" class="btn-submit" :disabled="loading">
          <span v-if="loading" class="spinner"></span>
          <span>{{ loading ? 'Đang gửi...' : 'Gửi yêu cầu' }}</span>
        </button>

        <router-link to="/login" class="back-link">
          ← Quay lại đăng nhập
        </router-link>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue"
import axios from "axios"

const email = ref("")
const loading = ref(false)
const fieldError = ref("")

const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null

function showToast(message, type = 'success') {
  clearTimeout(toastTimer)
  toast.message = message
  toast.type = type
  toast.show = true
  toastTimer = setTimeout(() => { toast.show = false }, 4000)
}

async function submitEmail() {
  fieldError.value = ""

  if (!email.value.trim()) {
    fieldError.value = "Vui lòng nhập email!"
    return
  }

  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
  if (!emailRegex.test(email.value)) {
    fieldError.value = "Email không hợp lệ!"
    return
  }

  try {
    loading.value = true
    const response = await axios.post("/forgot-password", { email: email.value })
    showToast(response.data.message || "Email khôi phục đã được gửi! Vui lòng kiểm tra hộp thư.", "success")
    email.value = ""
  } catch (error) {
    const msg = error.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại"
    showToast(msg, "error")
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-wrapper {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #ffffff;
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
.forgot-box {
  width: 400px;
  padding: 44px 40px;
  background: #fff;
  border: 2px solid #E6E0D8;
  box-shadow: 8px 8px 0 #000;
  text-align: center;
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

.title {
  font-size: 24px;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 8px;
  color: #333333;
  letter-spacing: 1px;
}

.subtitle {
  color: #777;
  margin-bottom: 28px;
  font-size: 14px;
  line-height: 1.6;
}

/* ── Form ── */
.form-group {
  text-align: left;
  margin-bottom: 20px;
}

.form-group.has-error .input {
  border-color: #c0392b;
}

label {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: #333333;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input {
  width: 100%;
  padding: 12px 14px;
  border: 2px solid #E6E0D8;
  background: #fafafa;
  color: #333333;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s, background 0.2s;
  box-sizing: border-box;
}

.input:focus {
  border-color: #A08B7A;
  background: #fff;
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

/* ── Back link ── */
.back-link {
  display: inline-block;
  margin-top: 20px;
  font-size: 13px;
  color: #A08B7A;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.back-link:hover { color: #333; }
</style>
