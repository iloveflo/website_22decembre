<template>
  <div class="admin-layout" :style="adminVars">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar" :class="{ 'collapsed': isSidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo-container">
          <span class="logo-icon">22</span>
          <span class="logo-text" v-show="!isSidebarCollapsed">DÉCEMBRE</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <!-- Root Admin only -->
        <template v-if="currentUser?.role === 'admin'">
          <router-link to="/admin/quan-ly-nguoi-dung" class="nav-item">
            <div class="icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <span class="nav-label">Quản lý người dùng</span>
          </router-link>

          <router-link to="/admin/quan-ly-khuyen-mai" class="nav-item">
            <div class="icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
            </div>
            <span class="nav-label">Quản lý khuyến mãi</span>
          </router-link>

          <router-link to="/admin/quan-ly-danh-muc" class="nav-item">
            <div class="icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <span class="nav-label">Quản lý danh mục</span>
          </router-link>

          <router-link to="/admin/thong-ke-bao-cao" class="nav-item">
            <div class="icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            </div>
            <span class="nav-label">Thống kê báo cáo</span>
          </router-link>
        </template>

        <!-- Staff only -->
        <template v-else-if="currentUser?.role === 'staff'">
          <router-link to="/admin/quan-ly-san-pham" class="nav-item">
            <div class="icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </div>
            <span class="nav-label">Quản lý sản phẩm</span>
          </router-link>

          <router-link to="/admin/quan-ly-don-hang" class="nav-item">
            <div class="icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            </div>
            <span class="nav-label">Quản lý đơn hàng</span>
          </router-link>

          <router-link to="/admin/quan-ly-danh-muc" class="nav-item">
            <div class="icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <span class="nav-label">Quản lý danh mục</span>
          </router-link>
        </template>
      </nav>

      <div class="sidebar-footer">
        <button @click="logout" class="logout-btn">
          <div class="icon-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          </div>
          <span class="nav-label" v-show="!isSidebarCollapsed">Đăng xuất</span>
        </button>
      </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="main-wrapper">
      <!-- TOP NAVBAR -->
      <header class="top-navbar">
        <div class="header-left">
          <button @click="isSidebarCollapsed = !isSidebarCollapsed" class="toggle-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
          </button>
          <div class="breadcrumb">
            <span class="text-muted">{{ currentUser?.role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}</span>
            <span class="separator">/</span>
            <span class="current-page">{{ currentPageTitle }}</span>
          </div>
        </div>

        <div class="header-right">
          <div v-if="currentUser" class="user-profile">
            <div class="user-info">
              <div class="user-name">{{ currentUser?.full_name || (currentUser?.role === 'admin' ? 'Quản trị viên' : 'Nhân viên') }}</div>
              <div class="user-role">
                {{ currentUser?.role === 'admin' ? 'Quản trị viên' : (currentUser?.role === 'staff' ? 'Nhân viên' : 'Người dùng') }}
              </div>
            </div>
            <img v-if="currentUser.avatar" :src="avatarPath(currentUser.avatar)" class="user-avatar" />
            <div v-else class="user-avatar placeholder">
              {{ (currentUser.username || 'A').charAt(0).toUpperCase() }}
            </div>
          </div>
        </div>
      </header>

      <!-- CONTENT -->
      <main class="content-view">
        <router-view v-slot="{ Component }">
          <transition name="fade-slide" mode="out-in">
            <div :key="route?.path || 'page'" class="content-wrapper">
              <component :is="Component" v-if="Component" />
              
              <!-- WELCOME / DASHBOARD OVERVIEW -->
              <div v-else class="dashboard-home">
              <div class="welcome-banner">
                <div class="banner-content">
                  <h1>Chào mừng trở lại, {{ currentUser?.full_name || (currentUser?.role === 'admin' ? 'Quản trị viên' : 'Nhân viên') }}!</h1>
                  <p>Hệ thống đang hoạt động ổn định. Bạn có <b>{{ dashboardStats?.pending_orders || 0 }}</b> đơn hàng mới cần xử lý.</p>
                  <div class="banner-actions">
                    <router-link to="/admin/quan-ly-don-hang" class="btn-primary" style="text-decoration:none; display:inline-block;">Xem đơn hàng</router-link>
                    <router-link to="/admin/thong-ke-bao-cao" class="btn-secondary" style="text-decoration:none; display:inline-block;" v-if="currentUser?.role === 'admin'">Báo cáo nhanh</router-link>
                  </div>
                </div>
                <div class="banner-illustration">
                  <!-- Abstract decoration -->
                  <div class="circle circle-1"></div>
                  <div class="circle circle-2"></div>
                </div>
              </div>

              <div class="stats-grid">
                <!-- Root Admin Stats -->
                <template v-if="currentUser?.role === 'admin'">
                  <div class="stat-card">
                    <div class="stat-icon users">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </div>
                    <div class="stat-info">
                      <span class="stat-label">Tổng khách hàng</span>
                      <span class="stat-value">{{ dashboardStats?.adminStats?.total_customers || 0 }}</span>
                      <span class="stat-trend" :class="dashboardStats?.adminStats?.customer_growth >= 0 ? 'positive' : 'negative'">
                        {{ dashboardStats?.adminStats?.customer_growth > 0 ? '+' : '' }}{{ dashboardStats?.adminStats?.customer_growth || 0 }}% tháng trước
                      </span>
                    </div>
                  </div>
                  <div class="stat-card">
                    <div class="stat-icon revenue">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div class="stat-info">
                      <span class="stat-label">Doanh thu tháng này</span>
                      <span class="stat-value">{{ dashboardStats?.adminStats?.revenue || '0' }}</span>
                      <span class="stat-trend" :class="dashboardStats?.adminStats?.revenue_growth >= 0 ? 'positive' : 'negative'">
                        {{ dashboardStats?.adminStats?.revenue_growth > 0 ? '+' : '' }}{{ dashboardStats?.adminStats?.revenue_growth || 0 }}% tháng trước
                      </span>
                    </div>
                  </div>
                </template>

                <!-- Staff Stats -->
                <template v-else-if="currentUser?.role === 'staff'">
                  <div class="stat-card">
                    <div class="stat-icon orders">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path></svg>
                    </div>
                    <div class="stat-info">
                      <span class="stat-label">Đơn hàng mới</span>
                      <span class="stat-value">{{ dashboardStats?.staffStats?.new_orders || 0 }}</span>
                      <span class="stat-trend" :class="dashboardStats?.staffStats?.order_growth > 0 ? 'positive' : (dashboardStats?.staffStats?.order_growth < 0 ? 'negative' : 'neutral')">
                        {{ dashboardStats?.staffStats?.order_growth > 0 ? '+' : '' }}{{ dashboardStats?.staffStats?.order_growth || 0 }} so với hôm qua
                      </span>
                    </div>
                  </div>
                  <div class="stat-card">
                    <div class="stat-icon products">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    </div>
                    <div class="stat-info">
                      <span class="stat-label">Sản phẩm</span>
                      <span class="stat-value">{{ dashboardStats?.staffStats?.total_products || 0 }}</span>
                      <span class="stat-trend neutral">Hệ thống</span>
                    </div>
                  </div>
                </template>
              </div>

              <div class="dashboard-sections">
                <div class="section-card">
                  <h3 class="section-title">Thông báo hệ thống</h3>
                  <ul class="notification-list">
                    <li class="notification-item warning">
                      <span class="dot"></span>
                      <p>Không xóa dữ liệu quan trọng nếu không chắc chắn.</p>
                    </li>
                    <li class="notification-item warning">
                      <span class="dot"></span>
                      <p>{{ currentUser?.role === 'admin' ? 'Chỉ tạo nhân viên mới nếu bạn có quyền Super Admin.' : 'Tuân thủ các quy định vận hành của cửa hàng.' }}</p>
                    </li>
                    <li class="notification-item info">
                      <span class="dot"></span>
                      <p>Kiểm tra kỹ thông tin người dùng, sản phẩm trước khi cập nhật.</p>
                    </li>
                  </ul>
                </div>
                <div class="section-card">
                  <h3 class="section-title">Hoạt động gần đây</h3>
                  <div class="activity-timeline" v-if="dashboardStats?.recentActivities?.length">
                    <div class="activity-item" v-for="(act, idx) in dashboardStats.recentActivities" :key="idx">
                      <div class="time">{{ act.time }}</div>
                      <div class="desc">{{ act.desc }}</div>
                    </div>
                  </div>
                  <div class="activity-timeline" v-else>
                    <div class="activity-item">
                      <div class="desc" style="color: #999">Đang tải hoặc chưa có hoạt động nào gần đây.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </transition>
        </router-view>
      </main>
    </div>

    <!-- SESSION MODAL -->
    <div v-if="showSessionAlert" class="modal-overlay">
      <div class="premium-modal">
        <div class="modal-badge">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        </div>
        <h3>Phiên làm việc hết hạn</h3>
        <p>Để đảm bảo an toàn cho dữ liệu hệ thống, vui lòng đăng nhập lại để tiếp tục công việc.</p>
        <button @click="handleSessionExpiredConfirm" class="btn-confirm">
          Xác nhận đăng nhập
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const currentUser = ref(null)
const router = useRouter()
const route = useRoute()
const showSessionAlert = ref(false)
const isSidebarCollapsed = ref(false)
let sessionCheckInterval = null

const dashboardStats = ref(null)

const fetchDashboardStats = async () => {
  if (route.path !== '/admin') return
  try {
    const res = await axios.get('/admin/dashboard-stats')
    dashboardStats.value = res.data
  } catch (err) {
    console.error('Failed to fetch dashboard stats', err)
  }
}

watch(() => route.path, (newPath) => {
  if (newPath === '/admin' && !dashboardStats.value) {
    fetchDashboardStats()
  }
})

// CSS Variables mapping
const adminVars = {
  '--sidebar-width': '280px',
  '--sidebar-collapsed-width': '80px',
  '--header-height': '70px',
  '--primary-color': '#A08B7A',
  '--primary-dark': '#8a7566',
  '--bg-main': '#f8f9fa',
  '--card-shadow': '0 4px 20px rgba(0, 0, 0, 0.05)',
  '--transition': 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
}

const currentPageTitle = computed(() => {
  const path = route?.path || ''
  if (path.includes('quan-ly-nguoi-dung')) return 'Quản lý người dùng'
  if (path.includes('quan-ly-san-pham')) return 'Quản lý sản phẩm'
  if (path.includes('quan-ly-don-hang')) return 'Quản lý đơn hàng'
  if (path.includes('quan-ly-khuyen-mai')) return 'Quản lý khuyến mãi'
  if (path.includes('quan-ly-danh-muc')) return 'Quản lý danh mục'
  if (path.includes('thong-ke-bao-cao')) return 'Thống kê báo cáo'
  return 'Tổng quan'
})

const checkSessionExpiration = () => {
  const token = localStorage.getItem('token')
  const expiresAtString = localStorage.getItem('expires_at')
  if (!token || !expiresAtString) return

  const now = new Date()
  const expirationTime = new Date(expiresAtString)

  if (!isNaN(expirationTime.getTime()) && now >= expirationTime) {
    showSessionAlert.value = true
    if (sessionCheckInterval) clearInterval(sessionCheckInterval)
  }
}

const handleSessionExpiredConfirm = async () => {
  showSessionAlert.value = false
  await logout()
  router.push('/login')
}

const fetchCurrentUser = async () => {
  try {
    let res = null
    try {
      res = await axios.get('/me', { withCredentials: true })
    } catch (err) {
      const token = localStorage.getItem('token')
      if (token) {
        res = await axios.get('/me', { headers: { Authorization: `Bearer ${token}` } })
      } else {
        throw err
      }
    }
    currentUser.value = res.data
    localStorage.setItem('user_role', res.data.role)
  } catch (err) {
    currentUser.value = null
  }
}

onMounted(() => {
  fetchCurrentUser().then(() => {
    fetchDashboardStats()
  })
  checkSessionExpiration()
  sessionCheckInterval = setInterval(checkSessionExpiration, 60000)
  
  const onAuthChanged = () => fetchCurrentUser()
  window.addEventListener('auth-changed', onAuthChanged)
  window.__onAuthChanged = onAuthChanged
})

onBeforeUnmount(() => {
  const handler = window.__onAuthChanged
  if (handler) window.removeEventListener('auth-changed', handler)
})

onUnmounted(() => {
  if (sessionCheckInterval) clearInterval(sessionCheckInterval);
});

const logout = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      await axios.post('/logout', {}, {
        headers: { Authorization: `Bearer ${token}` },
      })
    }
  } catch (e) {
    console.error('Logout error:', e)
  }

  localStorage.removeItem('token')
  localStorage.removeItem('rememberedEmail')
  localStorage.removeItem('expires_at')

  if (axios.defaults.headers.common['Authorization']) {
    delete axios.defaults.headers.common['Authorization']
  }

  window.dispatchEvent(new Event('auth-changed'))
  router.push('/')
}

const backendOrigin = import.meta.env.VITE_BACKEND_URL || window.location.origin;

const avatarPath = (path) => {
  if (!path) return null
  const p = String(path)
  if (p.startsWith('http')) return p
  if (p.startsWith('/')) return backendOrigin + p
  if (p.startsWith('public/uploads/')) return backendOrigin + '/' + p.replace(/^public\//, '')
  if (p.startsWith('uploads/')) return backendOrigin + '/' + p
  if (p.startsWith('storage/')) return backendOrigin + '/' + p
  return backendOrigin + '/storage/' + p
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f4f7f6;
  font-family: 'Quicksand', sans-serif;
  color: #2d3436;
}

/* SIDEBAR STYLES */
.admin-sidebar {
  width: var(--sidebar-width);
  background: #1e1e2d; /* Dark sophisticated background */
  color: #fff;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: sticky;
  top: 0;
  height: 100vh;
  z-index: 100;
  box-shadow: 4px 0 15px rgba(0,0,0,0.1);
}

.admin-sidebar.collapsed {
  width: var(--sidebar-collapsed-width);
}

.sidebar-header {
  padding: 30px 20px;
  display: flex;
  justify-content: center;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: var(--primary-color);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.2rem;
  color: #fff;
}

.logo-text {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: 2px;
}

.sidebar-nav {
  flex: 1;
  padding: 20px 15px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-radius: 12px;
  color: #a2a3b7;
  text-decoration: none;
  transition: var(--transition);
  gap: 15px;
  white-space: nowrap;
  overflow: hidden;
}

.nav-item:hover {
  background: rgba(255,255,255,0.05);
  color: #fff;
}

.nav-item.router-link-active {
  background: var(--primary-color);
  color: #fff;
  box-shadow: 0 4px 15px rgba(160, 139, 122, 0.3);
}

.icon-box {
  min-width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-box svg {
  width: 20px;
  height: 20px;
}

.sidebar-footer {
  padding: 20px 15px;
  border-top: 1px solid rgba(255,255,255,0.05);
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-radius: 12px;
  background: transparent;
  border: none;
  color: #ef4444;
  cursor: pointer;
  transition: var(--transition);
  gap: 15px;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
}

/* MAIN WRAPPER */
.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.top-navbar {
  height: var(--header-height);
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  position: sticky;
  top: 0;
  z-index: 90;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.toggle-btn {
  background: none;
  border: none;
  color: #636e72;
  cursor: pointer;
  padding: 5px;
  border-radius: 5px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.9rem;
}

.breadcrumb .text-muted { color: #b2bec3; }
.breadcrumb .separator { color: #dfe6e9; }
.breadcrumb .current-page { font-weight: 600; color: #2d3436; }

.header-right {
  display: flex;
  align-items: center;
  gap: 30px;
}

.search-bar {
  position: relative;
  width: 300px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  color: #b2bec3;
}

.search-bar input {
  width: 100%;
  padding: 10px 15px 10px 40px;
  border-radius: 10px;
  border: 1px solid #f1f2f6;
  background: #f8f9fa;
  outline: none;
  transition: border 0.2s;
}

.search-bar input:focus {
  border-color: var(--primary-color);
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
}

.user-role {
  font-size: 0.75rem;
  color: #b2bec3;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  object-fit: cover;
  border: 2px solid #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.user-avatar.placeholder {
  background: var(--primary-color);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

/* CONTENT VIEW */
.content-view {
  padding: 30px;
  flex: 1;
  overflow-y: auto;
}

/* DASHBOARD HOME */
.dashboard-home {
  max-width: 1200px;
  margin: 0 auto;
}

.welcome-banner {
  background: linear-gradient(135deg, #1e1e2d 0%, #3a3a5e 100%);
  border-radius: 24px;
  padding: 40px;
  color: #fff;
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
  position: relative;
  overflow: hidden;
}

.banner-content {
  position: relative;
  z-index: 2;
  max-width: 60%;
}

.banner-content h1 {
  font-size: 2rem;
  margin-bottom: 15px;
  color: #fff;
}

.banner-content p {
  opacity: 0.8;
  margin-bottom: 25px;
  line-height: 1.6;
}

.banner-actions {
  display: flex;
  gap: 15px;
}

.btn-primary {
  background: var(--primary-color);
  color: #fff;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}

.btn-secondary {
  background: rgba(255,255,255,0.1);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.2);
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  backdrop-filter: blur(5px);
}

.banner-illustration .circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,0.05);
}

.circle-1 { width: 300px; height: 300px; right: -50px; top: -100px; }
.circle-2 { width: 150px; height: 150px; right: 150px; bottom: -50px; }

/* STATS GRID */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: #fff;
  padding: 25px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: var(--card-shadow);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon svg { width: 28px; height: 28px; }

.stat-icon.users { background: rgba(52, 152, 219, 0.1); color: #3498db; }
.stat-icon.orders { background: rgba(230, 126, 34, 0.1); color: #e67e22; }
.stat-icon.revenue { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
.stat-icon.products { background: rgba(155, 89, 182, 0.1); color: #9b59b6; }

.stat-label {
  display: block;
  font-size: 0.85rem;
  color: #b2bec3;
  margin-bottom: 5px;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
}

.stat-trend {
  font-size: 0.75rem;
  font-weight: 600;
}

.stat-trend.positive { color: #2ecc71; }
.stat-trend.negative { color: #e74c3c; }
.stat-trend.neutral { color: #b2bec3; }

/* DASHBOARD SECTIONS */
.dashboard-sections {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.section-card {
  background: #fff;
  padding: 30px;
  border-radius: 20px;
  box-shadow: var(--card-shadow);
}

.section-title {
  margin-bottom: 20px;
  font-size: 1.1rem;
}

.notification-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: 0.9rem;
}

.notification-item .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}

.notification-item.warning .dot { background: #f1c40f; }
.notification-item.info .dot { background: #3498db; }

.activity-timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.activity-item {
  display: flex;
  gap: 20px;
  font-size: 0.9rem;
  position: relative;
}

.activity-item .time {
  min-width: 60px;
  color: #b2bec3;
  font-weight: 600;
}

.activity-item .desc {
  color: #2d3436;
}

/* MODAL STYLES */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.premium-modal {
  background: #fff;
  padding: 40px;
  border-radius: 24px;
  max-width: 450px;
  width: 90%;
  text-align: center;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}

.modal-badge {
  width: 80px;
  height: 80px;
  background: #fff5f5;
  color: #ff7675;
  border-radius: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 25px;
}

.premium-modal h3 { font-size: 1.5rem; margin-bottom: 15px; }
.premium-modal p { color: #636e72; line-height: 1.6; margin-bottom: 30px; }

.btn-confirm {
  width: 100%;
  background: #1e1e2d;
  color: #fff;
  border: none;
  padding: 15px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}

/* ANIMATIONS */
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

@media (max-width: 992px) {
  .dashboard-sections {
    grid-template-columns: 1fr;
  }
  .search-bar { width: 200px; }
}

@media (max-width: 768px) {
  .admin-sidebar {
    position: fixed;
    left: -280px;
  }
  .admin-sidebar.mobile-open {
    left: 0;
  }
  .banner-content { max-width: 100%; }
}
</style>