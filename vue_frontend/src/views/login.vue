<template>
  <div class="login-page-wrapper">
    <div class="login-container">
      <h2 class="login-title">ĐĂNG NHẬP</h2>
      
      <form @submit.prevent="handleLogin" class="login-form">
        
        <div class="form-group">
          <label class="form-label">EMAIL</label>
          <input 
            type="email" 
            v-model="email" 
            required 
            placeholder="NHẬP EMAIL CỦA BẠN"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <div class="label-row">
            <label class="form-label">MẬT KHẨU</label>
            <router-link to="/forgot-password" class="forgot-link">QUÊN MẬT KHẨU?</router-link>
          </div>
          <div style="position: relative;">
            <input 
              :type="showPassword ? 'text' : 'password'" 
              v-model="password" 
              required 
              placeholder="NHẬP MẬT KHẨU"
              class="form-input"
              style="padding-right: 40px;"
            />
            <button 
              type="button" 
              @click="showPassword = !showPassword" 
              style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; display: flex; align-items: center; justify-content: center;"
              tabindex="-1"
              title="Ẩn/Hiện mật khẩu"
            >
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">MÃ XÁC NHẬN</label>
          <div class="captcha-container">
            <div class="captcha-box" v-if="captchaImg">
               <img :src="captchaImg" alt="captcha" class="captcha-img" />
            </div>
            <button type="button" @click="fetchCaptcha" class="btn-reload" title="Đổi mã khác">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            </button>
          </div>
          <input 
            type="text" 
            v-model="captchaInput" 
            required 
            placeholder="Nhập các ký tự trong ảnh"
            class="form-input mt-2"
          />
        </div>
        <div class="form-group checkbox-group">
          <label class="custom-checkbox">
            <input type="checkbox" v-model="rememberMe">
            <span class="checkmark"></span>
            <span class="checkbox-label">GHI NHỚ ĐĂNG NHẬP</span>
          </label>
        </div>

        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

        <button type="submit" class="btn-submit">ĐĂNG NHẬP</button>

        <div class="divider-text">HOẶC TIẾP TỤC VỚI</div>

        <div class="social-login-group">
          <button type="button" @click="loginWithGoogle" class="btn-social btn-google">
            <svg class="social-icon" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
              <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"/>
                <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.059 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"/>
                <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.769 -21.864 51.959 -21.864 51.129 C -21.864 50.299 -21.734 49.489 -21.484 48.729 L -21.484 45.639 L -25.464 45.639 C -26.284 47.269 -26.754 49.129 -26.754 51.129 C -26.754 53.129 -26.284 54.989 -25.464 56.619 L -21.484 53.529 Z"/>
                <path fill="#EA4335" d="M -14.754 43.769 C -12.984 43.769 -11.404 44.379 -10.154 45.579 L -6.714 42.139 C -8.804 40.189 -11.514 39.019 -14.754 39.019 C -19.444 39.019 -23.494 41.719 -25.464 45.639 L -21.484 48.729 C -20.534 45.879 -17.884 43.769 -14.754 43.769 Z"/>
              </g>
            </svg>
            <span>Google</span>
          </button>

          <button type="button" @click="loginWithFacebook" class="btn-social btn-facebook">
            <svg class="social-icon" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
              <path fill="#ffffff" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            <span>Facebook</span>
          </button>
        </div>
        <div class="footer-links">
           <span class="no-account-text">Chưa có tài khoản?</span>
           <router-link to="/register" class="btn-register-link">Đăng ký ngay</router-link>
        </div>
        
      </form>
    </div>

    <!-- Modal thông báo tài khoản bị xóa/khóa -->
    <Teleport to="body">
      <div v-if="showDeletedModal" class="locked-overlay">
        <div class="locked-card">
          <div class="locked-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
          </div>
          <h3>TRUY CẬP BỊ TỪ CHỐI</h3>
          <p>Tài khoản của bạn đã bị vô hiệu hóa hoặc tạm dừng hoạt động bởi Quản trị viên.</p>
          <div class="locked-details">
            Vui lòng liên hệ với bộ phận kỹ thuật hoặc Quản lý nhân sự để được hỗ trợ khôi phục tài khoản.
          </div>
          <button @click="showDeletedModal = false" class="btn-locked-close">ĐÃ HIỂU</button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const errorMessage = ref('')
const showDeletedModal = ref(false)

// --- BIẾN CHO CAPTCHA ---
const captchaInput = ref('')
const captchaImg = ref('') // Lưu chuỗi base64 của ảnh
const captchaKey = ref('') // Lưu key định danh của ảnh đó

// --- HÀM LẤY CAPTCHA MỚI ---
const fetchCaptcha = async () => {
  try {
    const response = await axios.get('/captcha') // Gọi API backend
    if (response.data.status) {
        captchaImg.value = response.data.data.img
        captchaKey.value = response.data.data.key
        captchaInput.value = '' // Reset input cũ
    }
  } catch (error) {
    console.error('Lỗi lấy captcha:', error)
  }
}

onMounted(async () => {
  // Lấy Captcha ngay khi load trang
  await fetchCaptcha();

  // --- PHẦN 1: XỬ LÝ SOCIAL LOGIN (GIỮ NGUYÊN) ---
  const params = new URLSearchParams(window.location.search)
  const socialToken = params.get('token')
  const socialRole = params.get('user_role')
  const socialEmail = params.get('user_email')

  if (socialToken) {
    localStorage.setItem('token', socialToken)
    // Social login không liên quan đến "ghi nhớ đăng nhập" thủ công — xóa để tránh ô nhiễm
    localStorage.removeItem('rememberedEmail')
    localStorage.removeItem('rememberedMe')
    window.history.replaceState({}, document.title, window.location.pathname)

    if (socialRole === 'admin' || socialRole === 'staff') {
      window.location.href = '/admin'
    } else {
      window.location.href = '/'
    }
    return;
  }

  // --- PHẦN 2: KIỂM TRA ĐĂNG NHẬP CŨ ---
  const token = localStorage.getItem('token')
  // Chỉ điền email & tick checkbox khi user đã bấm "ghi nhớ" rõ ràng từ lần trước
  const isRemembered = localStorage.getItem('rememberedMe') === 'true'
  const savedEmail = localStorage.getItem('rememberedEmail')

  if (token) {
    try {
      const res = await axios.get('/me', {
        headers: { Authorization: `Bearer ${token}` }
      })
      const currentUser = res.data.user
      if (currentUser.role === 'admin' || currentUser.role === 'staff') {
        window.location.href = '/admin'
      } else {
        window.location.href = '/'
      }
    } catch (err) {
      // Token hết hạn — xóa token nhưng GIỮ rememberedEmail nếu user đã bật ghi nhớ
      localStorage.removeItem('token')
      if (isRemembered && savedEmail) {
        email.value = savedEmail
        rememberMe.value = true
      }
    }
  } else if (isRemembered && savedEmail) {
    // Chưa có token, nhưng user đã tick "ghi nhớ" từ trước → điền sẵn email
    email.value = savedEmail
    rememberMe.value = true
  }
})

const handleLogin = async () => {
  errorMessage.value = ''

  try {
    // Gửi thêm captcha và key lên server
    const response = await axios.post('/login', {
      email: email.value,
      password: password.value,
      captcha: captchaInput.value, // User nhập
      key: captchaKey.value,       // Key từ server
      rememberMe: rememberMe.value,
    })

    const userRole = response.data.user.role
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user_role', userRole)

    if (response.data.expires_at) {
        localStorage.setItem('expires_at', response.data.expires_at);
    }

    if (rememberMe.value && userRole !== 'admin') {
      // Lưu email VÀ flag xác nhận user đã bật ghi nhớ
      localStorage.setItem('rememberedEmail', email.value)
      localStorage.setItem('rememberedMe', 'true')
    } else {
      // Xóa sạch cả 2 khi user không chọn ghi nhớ
      localStorage.removeItem('rememberedEmail')
      localStorage.removeItem('rememberedMe')
    }

    if (userRole === 'admin' || userRole === 'staff') {
      window.location.href = '/admin'
    } else {
      window.location.href = '/'
    }

  } catch (error) {
    console.error('Login error:', error)
    
    // Nếu lỗi, nên reload lại captcha mới ngay để tránh user nhập lại mã cũ (thường mã chỉ dùng 1 lần)
    fetchCaptcha(); 
    
    // Xử lý thông báo lỗi
    if (error.response?.data?.code === 'ACCOUNT_DELETED') {
      showDeletedModal.value = true
    } else if (error.response?.data?.errors?.captcha) {
         // Lỗi riêng cho captcha (nếu backend trả về dạng errors array)
         errorMessage.value = error.response.data.errors.captcha[0];
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Đăng nhập thất bại. Vui lòng kiểm tra lại.'
    }
  }
}

const loginWithGoogle = () => {
  window.location.href = `/auth/google`;
}

const loginWithFacebook = () => {
  window.location.href = `/auth/facebook`;
}
</script>



<style scoped>
/* --- Layout Chung --- */
.login-page-wrapper {
  display: flex;
  justify-content: center;
  padding: 110px 20px;
  background-color: #fff;
  font-family: var(--font-body);
}

.login-container {
  width: 100%;
  max-width: 480px; /* Độ rộng vừa phải, chuẩn form login */
  background: #fff;
  /* Có thể thêm border bao quanh nếu thích kiểu thẻ bài */
  /* border: 2px solid #E6E0D8; padding: 40px; */ 
}

/* --- Typography --- */
.login-title {
  font-size: 32px;
  font-weight: 900;
  text-align: center;
  margin-bottom: 40px;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: #333333;
}

/* --- Inputs & Labels --- */
.form-group {
  margin-bottom: 24px;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.form-label {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  color: #333333;
  text-transform: uppercase;
  display: block;
  margin-bottom: 8px; /* Khoảng cách với input */
}

.form-input {
  width: 100%;
  padding: 14px 16px;
  font-size: 14px;
  border: 1px solid #ccc; /* Viền xám mỏng ban đầu */
  background-color: #fff;
  color: #333333;
  outline: none;
  transition: all 0.2s ease;
  
  /* Vuông vức tuyệt đối */
  border-radius: 0; 
}

/* Focus effect: Viền đen đậm */
.form-input:focus {
  border-color: #A08B7A;
  border-width: 1px; /* Hoặc 2px nếu muốn gắt hơn */
}

.form-input::placeholder {
  color: #999;
  font-size: 12px;
  text-transform: uppercase;
}

/* --- Links --- */
.forgot-link {
  font-size: 11px;
  color: #666;
  text-decoration: underline;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.2s;
}

.forgot-link:hover {
  color: #333333;
}

/* --- Custom Checkbox (Vuông) --- */
.checkbox-group {
  margin-top: -10px;
  margin-bottom: 30px;
}

.custom-checkbox {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Ẩn input gốc */
.custom-checkbox input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Tạo ô vuông mới */
.checkmark {
  height: 16px;
  width: 16px;
  background-color: #fff;
  border: 1px solid #ccc;
  margin-right: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  border-radius: 0; /* Vuông góc */
}

/* Khi hover */
.custom-checkbox:hover input ~ .checkmark {
  border-color: #333333;
}

/* Khi checked: Đổi nền thành đen */
.custom-checkbox input:checked ~ .checkmark {
  background-color: #A08B7A;
  border-color: #A08B7A;
}

/* Dấu tích bên trong (tạo bằng CSS pseudo-element) */
.checkmark:after {
  content: "";
  display: none;
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
  margin-bottom: 2px;
}

.custom-checkbox input:checked ~ .checkmark:after {
  display: block;
}

/* --- Buttons --- */
.btn-submit, .btn-register {
  display: block;
  width: 100%;
  padding: 16px;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  transition: all 0.3s;
  border-radius: 0; /* Vuông góc */
}

/* Nút Login: Đen chủ đạo */
.btn-submit {
  background-color: #A08B7A;
  color: #fff;
  border: 2px solid #A08B7A;
}

.btn-submit:hover {
  background-color: #fff;
  color: #A08B7A;
}

/* Nút Register: Trắng chủ đạo */
.btn-register {
  background-color: #fff;
  color: #333333;
  border: 2px solid #E6E0D8; /* Viền đen dày */
}

.btn-register:hover {
  background-color: #A08B7A;
  color: #fff;
}

/* --- Divider & Error --- */
.divider-text {
  text-align: center;
  margin: 20px 0;
  font-size: 11px;
  color: #999;
  position: relative;
}

/* Tạo đường kẻ ngang 2 bên chữ HOẶC */
.divider-text::before, .divider-text::after {
  content: "";
  position: absolute;
  top: 50%;
  width: 42%; /* Độ dài đường kẻ */
  height: 1px;
  background-color: #eee;
}
.divider-text::before { left: 0; }
.divider-text::after { right: 0; }

.error-msg {
  color: #d00; /* Đỏ đậm thay vì đỏ tươi */
  font-size: 12px;
  margin-bottom: 20px;
  text-align: center;
  font-weight: 600;
  text-transform: uppercase;
}

/* CSS cho Group chứa nút Social */
.social-login-group {
  display: flex;
  gap: 15px; /* Khoảng cách giữa 2 nút */
  margin-bottom: 25px;
}

/* Style chung cho nút Social */
.btn-social {
  flex: 1; /* Để 2 nút dài bằng nhau */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ddd;
  background-color: white;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.social-icon {
  margin-right: 8px;
  width: 20px;
  height: 20px;
}

/* Style riêng cho Google */
.btn-google {
  color: #3c4043;
}
.btn-google:hover {
  background-color: #f8f9fa;
  border-color: #c1c1c1;
}

/* Style riêng cho Facebook */
.btn-facebook {
  background-color: #1877F2;
  color: white;
  border: 1px solid #1877F2;
}
.btn-facebook:hover {
  background-color: #166fe5;
}
.btn-facebook .social-icon path {
  fill: white;
}


/* Tạo đường gạch ngang 2 bên chữ HOẶC */
.divider-text::before,
.divider-text::after {
  content: "";
  flex: 1;
  height: 1px;
  background-color: #e0e0e0;
}
.divider-text::before {
  margin-right: 10px;
}
.divider-text::after {
  margin-left: 10px;
}

/* Phần Footer (Đăng ký) */
.footer-links {
  text-align: center;
  margin-top: 10px;
  font-size: 14px;
}

.no-account-text {
  color: #666;
  margin-right: 5px;
}

.btn-register-link {
  color: #007bff; /* Thay bằng màu chủ đạo của web bạn */
  font-weight: bold;
  text-decoration: none;
}
.btn-register-link:hover {
  text-decoration: underline;
}

/* CSS CHO PHẦN CAPTCHA */
.mt-2 {
    margin-top: 10px;
}
.captcha-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
}

.captcha-box {
    flex-grow: 1;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
    height: 40px; /* Khớp với height config backend */
    display: flex;
    align-items: center;
    justify-content: center;
}

.captcha-img {
    height: 100%;
    width: auto;
    display: block;
}

.btn-reload {
    background: #ececec;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #555;
}

.btn-reload:hover {
    background: #e0e0e0;
    color: #333333;
}
/* CSS CHO MODAL TÀI KHOẢN BỊ KHÓA */
.locked-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.locked-card {
    background: #fff;
    width: 100%;
    max-width: 450px;
    padding: 40px;
    text-align: center;
    border-radius: 0;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.locked-icon {
    width: 60px;
    height: 60px;
    background: #fff5f5;
    color: #e53e3e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.locked-icon svg {
    width: 32px;
    height: 32px;
}

.locked-card h3 {
    font-size: 20px;
    font-weight: 900;
    color: #1a202c;
    margin-bottom: 12px;
    letter-spacing: 1px;
}

.locked-card p {
    color: #4a5568;
    line-height: 1.6;
    margin-bottom: 20px;
}

.locked-details {
    background: #f7fafc;
    padding: 15px;
    border: 1px solid #edf2f7;
    font-size: 13px;
    color: #718096;
    margin-bottom: 30px;
}

.btn-locked-close {
    width: 100%;
    padding: 14px;
    background: #1a202c;
    color: #fff;
    border: none;
    font-weight: 700;
    cursor: pointer;
    letter-spacing: 2px;
    transition: all 0.2s;
}

.btn-locked-close:hover {
    background: #000;
}
</style>
