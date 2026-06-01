<template>
  <div>
    <h2>Quản lý người dùng</h2>

    <!-- Modal Edit User -->
    <Teleport to="body">
      <div v-if="selectedUser" class="admin-premium-overlay">
        <div class="admin-main-modal">
          <div class="modal-header-top">
            <h3>Hồ sơ người dùng & Lịch sử hoạt động</h3>
            <button @click="cancelEdit" class="modal-close-btn">&times;</button>
          </div>
          
          <form @submit.prevent="saveUser" class="modal-content-flex">
            <!-- Left: User Info -->
            <div class="modal-side-profile">
              <div class="avatar-container">
                <img v-if="previewAvatar" :src="previewAvatar" class="avatar-img">
                <div class="avatar-actions">
                  <label class="btn-change-avatar">
                    Đổi ảnh đại diện
                    <input type="file" @change="onFileChange" hidden/>
                  </label>
                </div>
              </div>

              <div class="profile-fields-list">
                <div class="field-item">
                  <label>Tên đăng nhập</label>
                  <input type="text" v-model="selectedUser.username" disabled class="field-input-disabled"/>
                </div>
                <div class="field-item">
                  <label>Địa chỉ Email</label>
                  <input type="email" v-model="selectedUser.email" disabled class="field-input-disabled"/>
                </div>
                <div class="field-item">
                  <label>Họ và tên khách hàng</label>
                  <input type="text" v-model="selectedUser.full_name" placeholder="Nhập tên khách hàng"/>
                </div>
                <div class="field-item">
                  <label>Số điện thoại</label>
                  <input type="text" v-model="selectedUser.phone" placeholder="Nhập số điện thoại (10 số)" pattern="0[0-9]{9}" title="Số điện thoại phải bắt đầu bằng số 0 và bao gồm đúng 10 chữ số"/>
                </div>
                <div class="field-item">
                  <label>Địa chỉ thường trú</label>
                  <textarea v-model="selectedUser.address" rows="3" placeholder="Nhập địa chỉ đầy đủ"></textarea>
                </div>
              </div>
              
              <div class="profile-footer-actions">
                <button type="submit" class="btn-primary-action">Cập nhật hồ sơ</button>
                <button type="button" @click="cancelEdit" class="btn-secondary-action">Đóng lại</button>
              </div>
            </div>

            <!-- Right: Business Activity -->
            <div class="modal-main-activity">
              <template v-if="selectedUser.role === 'user'">
                <h4 class="activity-title">Tổng quan giao dịch</h4>
                <div class="activity-stats-row">
                  <div class="stat-card-mini">
                    <span class="sc-label">Đơn hàng</span>
                    <span class="sc-value">{{ stats.total_orders }}</span>
                  </div>
                  <div class="stat-card-mini">
                    <span class="sc-label">Tổng chi tiêu</span>
                    <span class="sc-value-green">{{ Math.round(stats.total_order_value).toLocaleString() }} đ</span>
                  </div>
                  <div class="stat-card-mini">
                    <span class="sc-label">Lượt đánh giá</span>
                    <span class="sc-value-blue">{{ stats.total_reviews }}</span>
                  </div>
                </div>

                <h4 class="activity-title">Lịch sử đơn hàng gần đây</h4>
                <div class="activity-table-wrapper">
                  <table class="activity-data-table">
                    <thead>
                      <tr>
                        <th style="width: 40px"></th>
                        <th>Mã đơn</th>
                        <th>Ngày tạo</th>
                        <th>Tổng giá trị</th>
                        <th>Trạng thái</th>
                      </tr>
                    </thead>
                    <tbody v-for="order in selectedUser.orders" :key="order.id">
                      <tr @click="toggleOrder(order.id)" class="order-master-row" :class="{ 'is-expanded': expandedOrders.includes(order.id) }">
                        <td class="expand-icon">
                          <span v-if="expandedOrders.includes(order.id)">▼</span>
                          <span v-else>▶</span>
                        </td>
                        <td class="order-code-cell">{{ order.order_code }}</td>
                        <td>{{ new Date(order.created_at).toLocaleDateString('vi-VN') }}</td>
                        <td class="order-price-cell">{{ Math.round(order.total_amount).toLocaleString() }} đ</td>
                        <td>
                          <span :class="['order-badge', order.order_status]">
                            {{ order.order_status }}
                          </span>
                        </td>
                      </tr>
                      <!-- Expanded Item Details -->
                      <tr v-if="expandedOrders.includes(order.id)" class="order-details-row">
                        <td colspan="5">
                          <div class="items-mini-list">
                            <div v-for="item in order.order_items" :key="item.id" class="mini-item-card">
                              <img :src="item.product_image_url" class="mini-item-img" />
                              <div class="mini-item-info">
                                <p class="mi-name">{{ item.product_name }}</p>
                                <p class="mi-variant">
                                  <span v-if="item.color">Màu: {{ item.color }}</span>
                                  <span v-if="item.size"> | Size: {{ item.size }}</span>
                                </p>
                              </div>
                              <div class="mini-item-price">
                                <p class="mi-qty">x{{ item.quantity }}</p>
                                <p class="mi-subtotal">{{ Math.round(item.price).toLocaleString() }} đ</p>
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                    <tbody v-if="!selectedUser.orders?.length">
                      <tr>
                        <td colspan="5" class="table-empty-msg">Chưa có dữ liệu đơn hàng cho người dùng này.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </template>
              <template v-else>
                <div class="admin-info-view">
                    <div class="shield-icon-placeholder">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <h3>Tài khoản quyền Quản trị</h3>
                    <p>Hệ thống không theo dõi lịch sử mua hàng cá nhân đối với tài khoản nhân viên và quản trị viên.</p>
                </div>
              </template>
            </div>
          </form>
        </div>
      </div>
    </Teleport>


    <!-- Create Staff Modal -->
    <Teleport to="body">
      <div v-if="createModal" class="admin-premium-overlay">
        <div class="admin-small-modal">
          <div class="modal-header-top">
            <h3>Thêm nhân viên mới</h3>
            <button @click="closeCreateModal" class="modal-close-btn">&times;</button>
          </div>
          <form @submit.prevent="createAdmin" class="modal-simple-body">
            <input type="hidden" v-model="newAdmin.role" />
            
            <div class="staff-create-layout">
              <div class="avatar-center-box">
                <img v-if="newPreviewAvatar" :src="newPreviewAvatar" class="avatar-circle">
                <label class="btn-select-file">
                  Chọn ảnh nhân viên
                  <input type="file" @change="onNewFileChange" hidden/>
                </label>
              </div>

              <div class="staff-form-grid">
                <div class="form-field">
                  <label>Username</label>
                  <input type="text" v-model="newAdmin.username" required placeholder="Tên đăng nhập"/>
                </div>
                <div class="form-field">
                  <label>Email</label>
                  <input type="email" v-model="newAdmin.email" required placeholder="Địa chỉ email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Vui lòng nhập đúng định dạng email"/>
                </div>
                <div class="form-field">
                  <label>Họ và tên</label>
                  <input type="text" v-model="newAdmin.full_name" required placeholder="Nhập tên đầy đủ"/>
                </div>
                <div class="form-field">
                  <label>Số điện thoại</label>
                  <input type="text" v-model="newAdmin.phone" required placeholder="VD: 0912345678 (10 số)" pattern="0[0-9]{9}" title="Số điện thoại phải bắt đầu bằng số 0 và bao gồm đúng 10 chữ số"/>
                </div>
                <div class="form-field span-2">
                  <label>Địa chỉ</label>
                  <input type="text" v-model="newAdmin.address" placeholder="Nhập địa chỉ nhân viên"/>
                </div>
                <div class="form-field">
                  <label>Mật khẩu</label>
                  <input type="password" v-model="newAdmin.password" required placeholder="Tối thiểu 8 ký tự"/>
                </div>
                <div class="form-field">
                  <label>Xác nhận lại</label>
                  <input type="password" v-model="newAdmin.password_confirmation" required placeholder="Xác nhận lại mật khẩu"/>
                </div>
                <div class="form-field span-2 admin-confirm-box">
                  <label>Nhập mật khẩu Admin của bạn</label>
                  <input type="password" v-model="newAdmin.current_admin_password" placeholder="Bắt buộc để xác thực quyền tạo"/>
                </div>
              </div>
            </div>

            <div class="modal-bottom-actions">
              <button type="submit" class="btn-confirm-save">Tạo nhân viên</button>
              <button type="button" @click="closeCreateModal" class="btn-cancel-light">Hủy bỏ</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>


    <!-- Filter & Search -->
    <div class="flex flex-wrap gap-3 mb-6 items-center bg-white p-4 rounded-lg shadow-sm border border-gray-100">
      <div class="flex gap-2 mr-auto">
        <button v-if="isSuperAdmin" @click="toggleShowDeleted"
          :class="showDeleted ? 'bg-gray-500' : 'bg-red-600'"
          class="text-white px-4 py-2 rounded-md font-medium transition-colors">
          {{ showDeleted ? 'Ẩn tài khoản đã xóa' : 'Xem tài khoản đã xóa' }}
        </button>
        <button v-if="isSuperAdmin" @click="openCreateModal" class="bg-blue-700 text-white px-4 py-2 rounded-md font-medium hover:bg-blue-800 transition-colors">
          + Thêm nhân viên mới
        </button>
      </div>

      <div class="flex gap-2 items-center">
        <div class="relative">
          <input v-model="search" placeholder="Tìm tên, email, SĐT..." @input="handleSearch" 
            class="pl-3 pr-10 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none w-64 transition-all"/>
        </div>
        
        <select v-model="filterRole" @change="performSearch" class="px-3 py-2 border rounded-md outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer bg-white">
          <option value="">Tất cả vai trò</option>
          <option value="staff">Nhân viên</option>
          <option value="user">Khách hàng</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <table class="table-auto w-full border">
      <thead>
        <tr>
              <th>ID</th>
              <th style="width:48px;min-width:48px">Avatar</th>
              <th>Username</th>
              <th>Email</th>
              <th>Họ tên</th>
              <th>Role</th>
              <th>Ngày tạo</th>
              <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in showDeleted ? deletedUsers : users.data" :key="user.id">
          <td>{{ user.id }}</td>
              <td class="px-2" style="width:48px;min-width:48px">
                <img v-if="user.avatar" :src="avatarPath(user.avatar)" style="width:32px;height:32px;border-radius:9999px;object-fit:cover;display:block"/>
              </td>
          <td>{{ user.username }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.full_name }}</td>
          <td>
            <span v-if="user.role === 'admin'">Quản trị viên</span>
            <span v-else-if="user.role === 'staff'">Nhân viên</span>
            <span v-else>Người dùng</span>
          </td>
          <td>{{ formatDate(user.created_at) }}</td>
          <td>
            <template v-if="showDeleted">
              <button @click="restoreUser(user.id)" class="bg-green-600 text-white px-2 py-1 rounded">Khôi phục</button>
            </template>
            <template v-else-if="user.email !== 'admin@example.com'">
              <!-- show edit for normal users; for admin/staff rows only show if current is super-admin -->
              <button v-if="user.role !== 'admin' || isSuperAdmin" @click="viewUser(user.id)" class="text-blue-600">Xem chi tiết</button>
              <button v-if="(user.role === 'admin' || user.role === 'staff') && isSuperAdmin" @click="deleteUser(user.id)" class="text-red-600">Xóa</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination (numeric) -->
    <div class="mt-4 flex gap-2 items-center">
      <button @click="prevPage" :disabled="currentPage === 1"><</button>
      <template v-for="p in pages()" :key="p">
        <button @click="goToPage(p)" :class="{ 'font-bold underline': currentPage === p }">{{ p }}</button>
      </template>
      <button @click="nextPage" :disabled="currentPage === (users.last_page || 1)">></button>
    </div>

    <!-- Custom Alert/Toast -->
    <Transition name="toast">
      <div v-if="toast.show" :class="['custom-toast', toast.type]">
        <div class="toast-content">
          <span class="toast-icon">
            <svg v-if="toast.type==='success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          </span>
          <span class="toast-message">{{ toast.message }}</span>
        </div>
      </div>
    </Transition>

    <!-- Custom Confirm Modal -->
    <div v-if="confirmModal.show" class="confirm-overlay">
      <div class="confirm-card">
        <div class="confirm-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <h3>Xác nhận thao tác</h3>
        <p>{{ confirmModal.message }}</p>
        <div class="confirm-actions">
          <button @click="confirmModal.show = false" class="btn-cancel">Hủy bỏ</button>
          <button @click="executeConfirm" class="btn-confirm">Đồng ý</button>
        </div>
      </div>
    </div>
</template>

<script>
import axios from 'axios'
import { ref, onMounted, onBeforeUnmount } from 'vue'

export default {
  setup() {
    const users = ref({data: []})
    const search = ref('')
    const filterRole = ref('')
    const filterStatus = ref('')
    let searchTimeout = null

    const handleSearch = () => {
      if (searchTimeout) clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        performSearch()
      }, 500)
    }
    const selectedUser = ref(null)
    const stats = ref({})
    const previewAvatar = ref(null)
    let avatarFile = null
    let newAvatarFile = null
    let currentPage = ref(1)
    const createModal = ref(false)
    const newAdmin = ref({ username: '', email: '', password: '', password_confirmation: '', full_name: '', phone: '', address: '', role: 'staff' })
    const newPreviewAvatar = ref(null)
    const isSuperAdmin = ref(false)

    const deletedUsers = ref([])   // danh sách user đã xóa
    const showDeleted = ref(false) // toggle giữa active / deleted

    const expandedOrders = ref([])
    const toggleOrder = (orderId) => {
      const idx = expandedOrders.value.indexOf(orderId)
      if (idx > -1) expandedOrders.value.splice(idx, 1)
      else expandedOrders.value.push(orderId)
    }
    
    // UI Notification/Confirm
    const toast = ref({ show: false, message: '', type: 'success' })
    const confirmModal = ref({ show: false, message: '', onConfirm: null })

    const showToast = (msg, type = 'success') => {
      toast.value = { show: true, message: msg, type }
      setTimeout(() => { toast.value.show = false }, 3000)
    }

    const askConfirm = (msg, onConfirm) => {
      confirmModal.value = { show: true, message: msg, onConfirm }
    }

    const executeConfirm = () => {
      if (confirmModal.value.onConfirm) confirmModal.value.onConfirm()
      confirmModal.value.show = false
    }

    const fetchUsers = async () => {
      const res = await axios.get('/admin/users', {
        params: { 
          search: search.value,
          role: filterRole.value,
          page: currentPage.value
        },
        headers: {
          Authorization: token ? `Bearer ${token}` : undefined
        }
      })
      users.value = res.data
    }
    const performSearch = () => {
      // when user triggers a search, reset to first page
      currentPage.value = 1
      fetchUsers()
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
        isSuperAdmin.value = res.data?.email === 'admin@example.com'
      } catch (err) {
        isSuperAdmin.value = false
      }
    }

    const backendOrigin = import.meta.env.VITE_BACKEND_URL || window.location.origin;
    const avatarPath = (path) => {
      if (!path) return null
      const p = String(path)
      // already an absolute URL
      if (p.startsWith('http')) return p

      // normalized absolute path starting with /
      if (p.startsWith('/')) {
        // if it's a public uploads or storage path, load from backend origin
        if (p.startsWith('/uploads/') || p.startsWith('/storage/')) {
          return backendOrigin + p
        }
        return p
      }

      // handle values like 'public/uploads/..', 'uploads/..', or storage disk paths
      if (p.startsWith('public/uploads/')) {
        return backendOrigin + '/' + p.replace(/^public\//, '')
      }
      if (p.startsWith('uploads/')) return backendOrigin + '/' + p
      if (p.startsWith('storage/')) return backendOrigin + '/' + p

      // fallback assume storage disk path
      return backendOrigin + '/storage/' + p
    }
    
    const token = localStorage.getItem('token')

    const viewUser = async (id) => {
      const res = await axios.get(`/admin/users/${id}`,{
        headers: {
          Authorization: token ? `Bearer ${token}` : undefined
        }})
      selectedUser.value = res.data.user
      stats.value = res.data.stats
      previewAvatar.value = avatarPath(selectedUser.value.avatar)
    }

    const onFileChange = (e) => {
      avatarFile = e.target.files[0]
      previewAvatar.value = URL.createObjectURL(avatarFile)
    }

    const onNewFileChange = (e) => {
      newAvatarFile = e.target.files[0]
      newPreviewAvatar.value = URL.createObjectURL(newAvatarFile)
    }

    const saveUser = async () => {
      // ---- VALIDATE ----
      if (!selectedUser.value.full_name?.trim()) {
        return showToast("Full Name không được để trống", "error");
      }
      if (!selectedUser.value.phone?.trim()) {
        return showToast("Phone không được để trống", "error");
      }
      if (!/^0[0-9]{9}$/.test(selectedUser.value.phone)) {
        return showToast("Phone phải bắt đầu bằng số 0 và gồm đúng 10 chữ số", "error");
      }
      if (!selectedUser.value.address?.trim()) {
        return showToast("Address không được để trống", "error");
      }

      if (avatarFile) {
        // 1. Kiểm tra dung lượng (2MB = 2 * 1024 * 1024 bytes)
        if (avatarFile.size > 2 * 1024 * 1024) {
            return showToast("Dung lượng ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.", "error");
        }

        // 2. Kiểm tra định dạng (MIME types)
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(avatarFile.type)) {
            return showToast("Định dạng ảnh không hợp lệ! Chỉ chấp nhận file .JPG hoặc .PNG.", "error");
        }
      }

      const formData = new FormData()
      // Do not append the avatar URL string (Laravel expects a file for 'avatar' when validating 'image').
      for (const key in selectedUser.value) {
        if (key === 'avatar') continue
        formData.append(key, selectedUser.value[key])
      }
      if (avatarFile) {
        formData.append('avatar', avatarFile)
      }
      try {
        await axios.post(`/admin/users/${selectedUser.value.id}?_method=PUT`, formData)
        fetchUsers()
        showToast('Cập nhật thành công!')
        setTimeout(() => window.location.reload(), 1500)
      } catch (err) {
        console.error(err)
        const data = err.response?.data
        if (data?.errors) {
          const msgs = Object.values(data.errors).flat().join('\n')
          showToast(msgs, 'error')
        } else {
          showToast(data?.message || 'Lỗi khi cập nhật user', 'error')
        }
      }
    }

      fetchUsers()

      const changeStatus = async (id) => {
        const newStatus = prompt('Nhập trạng thái mới: active, inactive, banned')
        if (!newStatus) return
        await axios.post(`/admin/users/${id}/status`, { status: newStatus, _method: 'PATCH' })
        fetchUsers()
      }

    const viewOrders = async (id) => {
      const res = await axios.get(`/admin/users/${id}/orders`)
      console.log('Orders', res.data)
      showToast(`User có ${res.data.length} đơn hàng. Xem console để chi tiết.`)
    }

    const openCreateModal = () => {
      createModal.value = true
    }

    const closeCreateModal = () => {
      createModal.value = false
      newAdmin.value = { username: '', email: '', password: '', password_confirmation: '', full_name: '', phone: '', address: '', role: 'staff' }
      newPreviewAvatar.value = null
      newAvatarFile = null
    }

    const createAdmin = async () => {
          
      // ================= VALIDATION =================
      if (!newAdmin.value.username?.trim()) {
        return showToast("Username không được để trống", "error");
      }

      if (!newAdmin.value.email?.trim()) {
        return showToast("Email không được để trống", "error");
      }
      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!emailRegex.test(newAdmin.value.email)) {
        return showToast("Email không hợp lệ!", "error");
      }

      if (!newAdmin.value.full_name?.trim()) {
        return showToast("Full name không được để trống", "error");
      }

      if (!newAdmin.value.phone?.trim()) {
        return showToast("Số điện thoại không được để trống", "error");
      }

      if (!/^0[0-9]{9}$/.test(newAdmin.value.phone)) {
        return showToast("Số điện thoại phải bắt đầu bằng số 0 và gồm đúng 10 chữ số", "error");
      }

      if (!newAdmin.value.address?.trim()) {
        return showToast("Địa chỉ không được để trống", "error");
      }

      if (!newAdmin.value.password?.trim()) {
        return showToast("Password không được để trống", "error");
      }

      if (newAdmin.value.password.length < 8) {
        return showToast("Mật khẩu phải có ít nhất 8 ký tự", "error");
      }

      if (!newAdmin.value.password_confirmation?.trim()) {
        return showToast("Vui lòng nhập lại mật khẩu", "error");
      }

      if (newAdmin.value.password !== newAdmin.value.password_confirmation) {
        return showToast("Mật khẩu và nhập lại mật khẩu không khớp", "error");
      }

      if (!newAdmin.value.current_admin_password?.trim()) {
        return showToast("Bạn phải nhập password admin đang login để xác nhận", "error");
      }

      if (newAvatarFile) {
        // 1. Kiểm tra dung lượng (2MB)
        if (newAvatarFile.size > 2 * 1024 * 1024) {
             return showToast("Dung lượng ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.", "error");
        }
        
        // 2. Kiểm tra định dạng
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(newAvatarFile.type)) {
             return showToast("Định dạng ảnh không hợp lệ! Chỉ chấp nhận file .JPG hoặc .PNG.", "error");
        }
      }

      const formData = new FormData()
      for (const key in newAdmin.value) {
        formData.append(key, newAdmin.value[key])
      }
      if (newAvatarFile) {
        formData.append('avatar', newAvatarFile)
      }
      try {
        await axios.post('/admin/users', formData)
        fetchUsers()
        closeCreateModal()
        showToast('Tài khoản nhân viên mới đã được tạo và email đã gửi!');
      } catch (err) {
        console.error(err)
        const data = err.response?.data
        if (data?.errors) {
          const msgs = Object.values(data.errors).flat().join('\n')
          showToast(msgs, 'error')
        } else {
          showToast(data?.message || 'Lỗi khi tạo nhân viên', 'error')
        }
      }
    }


    const fetchDeletedUsers = async () => {
      const res = await axios.get('/admin/users/deleted', { withCredentials: true })
      deletedUsers.value = res.data
    }

    const toggleShowDeleted = () => {
      showDeleted.value = !showDeleted.value
      if (showDeleted.value) fetchDeletedUsers()
      else fetchUsers()
    }

    const restoreUser = async (id) => {
      askConfirm('Bạn có chắc muốn khôi phục user này?', async () => {
        try {
          await axios.post(`/admin/users/${id}/restore`, {_method: 'PATCH'});
          showToast('Đã khôi phục user')
          fetchDeletedUsers()
          fetchUsers()
        } catch (err) {
          console.error(err)
          showToast(err.response?.data?.message || 'Lỗi khi khôi phục user', 'error')
        }
      })
    }


    const deleteUser = async (id) => {
      askConfirm('Bạn có chắc muốn xóa user này?', async () => {
        try {
          await axios.post(`/admin/users/${id}`, {_method: 'DELETE'});
          fetchUsers()
          showToast('Đã xóa user')
        } catch (err) {
          console.error(err)
          showToast(err.response?.data?.message || 'Lỗi khi xóa user', 'error')
        }
      })
    }

    const cancelEdit = () => {
      selectedUser.value = null
      previewAvatar.value = null
      avatarFile = null
      stats.value = {}
      expandedOrders.value = []
    }

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--
        fetchUsers()
      }
    }

    const nextPage = () => {
      const last = users.value.last_page || 1
      if (currentPage.value < last) {
        currentPage.value++
        fetchUsers()
      }
    }

    const pages = () => {
      const last = users.value.last_page || 1
      return Array.from({ length: last }, (_, i) => i + 1)
    }

    const goToPage = (p) => {
      if (p === currentPage.value) return
      currentPage.value = p
      fetchUsers()
    }

    fetchUsers()
    fetchCurrentUser()

    // listen for auth changes (login/logout) to refresh isSuperAdmin
    const onAuthChanged = () => fetchCurrentUser()
    window.addEventListener('auth-changed', onAuthChanged)
    window.__onAuthChangedUsers = onAuthChanged

    onBeforeUnmount(() => {
      const handler = window.__onAuthChangedUsers
      if (handler) window.removeEventListener('auth-changed', handler)
    })

    const formatDate = (dateStr) => {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN');
    }

    return {
      users, search, filterRole, filterStatus, selectedUser, stats, previewAvatar, onFileChange, onNewFileChange, handleSearch,
      saveUser, viewUser, changeStatus, viewOrders, prevPage, nextPage, createModal, newAdmin, newPreviewAvatar,
      openCreateModal, closeCreateModal, createAdmin, deleteUser, cancelEdit, pages, goToPage, currentPage,
      isSuperAdmin, performSearch, avatarPath, deletedUsers, showDeleted, toggleShowDeleted, restoreUser,
      toast, confirmModal, executeConfirm, expandedOrders, toggleOrder, formatDate
    }
  }
}
</script>

<style scoped>
/* =========================================
   GLOBAL & RESET (Modern Monochrome Theme)
   ========================================= */
:root {
  --mono-bg: #ffffff;
  --mono-text: #333333;
  --mono-gray-light: #FAF9F6;
  --mono-border: #E6E0D8;
  --mono-accent: #A08B7A;
}

/* Áp dụng font hiện đại cho toàn bộ container */
div {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  color: var(--mono-text);
  box-sizing: border-box;
}

/* Tiêu đề chính */
h2 {
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  margin-bottom: 2rem;
  border-bottom: 2px solid var(--mono-text);
  padding-bottom: 1rem;
  display: inline-block;
}


/* Input file trong form */
input[type="file"] {
  font-size: 0.875rem;
  padding: 0.5rem 0;
}

/* Các div bao quanh input */
form > div {
  margin-bottom: 1.25rem;
}

/* Label */
form label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
  color: #555;
}

/* Input fields */
form input[type="text"],
form input[type="email"],
form input[type="password"],
input.border /* Toolbar search input */,
select.border {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 0; /* Vuông vức hiện đại */
  background: var(--mono-gray-light);
  transition: all 0.2s;
  outline: none;
}

form input:focus,
input.border:focus,
select.border:focus {
  background: white;
  border-color: var(--mono-text);
  box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
}

/* =========================================
   BUTTON OVERRIDES (Ghi đè class Tailwind)
   ========================================= */

/* Button chung */
button {
  font-weight: 600;
  font-size: 0.8rem;
  padding: 0.6rem 1.2rem !important; /* Ghi đè padding Tailwind */
  border-radius: 4px !important;
  cursor: pointer;
  transition: transform 0.1s, box-shadow 0.1s;
  border: 1px solid transparent;
}

button:active {
  transform: translateY(1px);
}

/* Nút Primary (Lưu, Tạo, Tìm kiếm) - Biến màu xanh/lá thành Đen */
button[class*="bg-blue-600"],
button[class*="bg-green-600"] {
  background-color: #A08B7A !important;
  color: #fff !important;
  border: 1px solid #A08B7A !important;
}

button[class*="bg-blue-600"]:hover,
button[class*="bg-green-600"]:hover {
  background-color: #333333 !important;
}

/* Nút Secondary (Hủy)*/
button[class*="bg-gray-400"] {
  background-color: white !important;
  color: var(--mono-text) !important;
  border: 1px solid #ccc !important;
}

button[class*="bg-gray-400"]:hover {
  background-color:  #ee1919  !important;
}

/* Nút Delete (Đỏ) */
button.text-red-600 {
  color: #333333 !important; /* Chuyển text đỏ thành đen */
  text-decoration: line-through; /* Gạch ngang để biểu thị xóa */
  opacity: 0.6;
}
button.text-red-600:hover {
  opacity: 1;
  text-decoration: none;
  background-color: #ffecec;
}

/* =========================================
   TOOLBAR & TABLE
   ========================================= */

/* Vùng filter/search */
.flex.gap-2.mb-4 {
  background: var(--mono-gray-light);
  padding: 1rem;
  border: 1px solid var(--mono-border);
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

/* Table Styling */
table.table-auto {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
  border: 1px solid #e5e5e5 !important;
}

table thead tr {
  border-bottom: 2px solid var(--mono-text);
}

table th {
  border: 1px solid #e5e5e5;
  text-align: left;
  padding: 1rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
}

table tbody tr {
  border-bottom: 1px solid var(--mono-border);
  transition: background 0.2s;
}

table tbody tr:hover {
  background-color: var(--mono-gray-light);
}

table td {
  border: 1px solid #e5e5e5;
  padding: 1rem;
  font-size: 0.9rem;
  vertical-align: middle;
}

/* Ảnh đại diện tròn trong bảng */
table td img {
  filter: grayscale(0%); /* Chuyển ảnh thành đen trắng */
  transition: filter 0.3s;
}

/* =========================================
   PAGINATION
   ========================================= */

/* Container của phân trang */
.mt-4.flex.gap-2 {
  /* Cấu hình Flexbox để sửa lỗi dọc và căn giữa */
  display: flex !important;           /* Bắt buộc dùng Flexbox */
  flex-direction: row !important;     /* Bắt buộc xếp ngang */
  justify-content: center !important; /* Căn chính giữa */
  align-items: center !important;     /* Căn giữa theo chiều dọc */
  flex-wrap: wrap !important;         /* Cho phép xuống dòng nếu màn hình quá nhỏ */
  
  /* Khoảng cách và viền */
  gap: 0.5rem !important;             /* Khoảng cách giữa các nút */
  padding-top: 1.5rem;
  margin-top: 1.5rem;
  border-top: 1px solid var(--mono-border);
  width: 100%;                        /* Chiếm hết chiều rộng để căn giữa chính xác */
}

/* Style chung cho các nút phân trang */
.mt-4.flex.gap-2 button {
  background: white;
  border: 1px solid #ddd;
  color: var(--mono-text);
  min-width: 36px;
  height: 36px;
  
  /* Flexbox nội bộ để số trang nằm giữa nút */
  display: flex !important;
  align-items: center;
  justify-content: center;
  
  padding: 0 !important;
  border-radius: 4px; /* Bo góc nhẹ cho hiện đại */
  transition: all 0.2s;
}

/* Hiệu ứng Hover cho nút thường */
.mt-4.flex.gap-2 button:hover:not(:disabled) {
  border-color: var(--mono-text);
  background-color: #f9f9f9;
}

/* Trang đang active (Nền đen - Chữ trắng) */
.mt-4.flex.gap-2 button.font-bold.underline {
  background: var(--mono-text) !important;
  color: rgb(61, 56, 56) !important;
  border-color: var(--mono-text) !important;
  text-decoration: none !important; /* Bỏ gạch chân */
  font-weight: bold;
  cursor: default;
}

/* Nút Prev/Next khi bị Disable */
button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  background: #f5f5f5 !important;
  border-color: #eee !important;
}

/* CUSTOM TOAST */
.custom-toast {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 20000000 !important;
  padding: 16px 24px;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
  border-left: 5px solid #A08B7A;
  animation: slideIn 0.3s ease-out;
}

.custom-toast.success { border-color: #2ecc71; }
.custom-toast.error { border-color: #e74c3c; }

.toast-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toast-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.custom-toast.success .toast-icon { color: #2ecc71; }
.custom-toast.error .toast-icon { color: #e74c3c; }

/* CUSTOM CONFIRM */
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(4px);
  z-index: 15000000 !important;
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-card {
  background: #fff;
  padding: 35px;
  border-radius: 20px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.confirm-icon {
  width: 60px;
  height: 60px;
  background: #fff5f5;
  color: #ff7675;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.confirm-icon svg { width: 30px; height: 30px; }

.confirm-card h3 { font-size: 1.4rem; margin-bottom: 10px; }
.confirm-card p { color: #636e72; margin-bottom: 25px; }

.confirm-actions {
  display: flex;
  gap: 12px;
}

.confirm-actions button {
  flex: 1;
  padding: 12px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
  border: none;
}

.btn-cancel { background: #f1f2f6; color: #2d3436; }
.btn-confirm { background: #1e1e2d; color: #fff; }

/* ULTIMATE PREMIUM MODAL ARCHITECTURE */
/* 1. SỬA OVERLAY: Chuyển sang Flexbox và sửa lỗi Box Model */
.admin-premium-overlay {
  position: fixed !important;
  inset: 0 !important; 
  width: 100% !important; 
  height: 100% !important;
  background: rgba(0,0,0,0.75) !important;
  backdrop-filter: blur(12px) !important;
  z-index: 10000000 !important;
  display: flex !important; 
  align-items: center !important; 
  justify-content: center !important; 
  padding: 20px !important;
  box-sizing: border-box !important; 
}

/* 2. SỬA MODAL CHÍNH: Bỏ margin thừa và kiểm soát chiều cao */
.admin-main-modal, .admin-small-modal {
  margin: 0 !important; 
  background: #ffffff;
  border-radius: 24px; 
  width: 100%;
  max-width: 1200px;
  height: auto;
  max-height: 90vh; 
  display: flex;
  flex-direction: column;
  box-shadow: 0 40px 120px rgba(0,0,0,0.4);
  overflow: hidden;
  animation: modalEnter 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

/* 3. SỬA FORM: Ép thẻ Form lấp đầy không gian còn lại và ẩn overflow */
.admin-main-modal form.modal-content-flex {
  max-width: 100% !important;
  padding: 0 !important;
  border: none !important;
  box-shadow: none !important;
  flex: 1 !important; 
  height: 100%;
  overflow: hidden !important; 
}

.admin-small-modal { max-width: 700px; }

.modal-header-top {
  padding: 30px 40px;
  background: #fff;
  border-bottom: 1px solid #f1f2f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.modal-header-top h3 {
  font-size: 1.6rem;
  font-weight: 900;
  color: #1e1e2d;
  margin: 0;
  letter-spacing: -0.5px;
}

.modal-close-btn {
  background: #f8f9fa;
  border: none;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  font-size: 1.8rem;
  color: #b2bec3;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s;
}

.modal-close-btn:hover {
  background: #ff7675;
  color: #fff;
  transform: rotate(90deg);
}

.modal-content-flex {
  display: flex !important;
  flex-direction: row !important;
  flex: 1;
  width: 100% !important;
  overflow: hidden;
}

/* 4. SỬA CỘT: Cho phép 2 cột Profile và Activity tự cuộn độc lập */
.modal-side-profile {
  width: 35% !important;
  min-width: 350px;
  flex-shrink: 0 !important;
  background: #fcfcfd;
  padding: 30px; 
  border-right: 1px solid #f1f2f6;
  overflow-y: auto !important; 
  display: flex;
  flex-direction: column;
}

.modal-main-activity {
  flex: 1 !important;
  background: #fff;
  padding: 30px;
  overflow-y: auto !important; 
  display: flex;
  flex-direction: column;
}

/* PROFILE STYLES */
.avatar-container {
  text-align: center;
  margin-bottom: 35px;
}

.avatar-img {
  width: 150px;
  height: 150px;
  border-radius: 40px;
  object-fit: cover;
  margin-bottom: 20px;
  border: 6px solid #fff;
  box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.btn-change-avatar {
  background: #1e1e2d;
  color: #fff;
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
}

.profile-fields-list .field-item {
  margin-bottom: 20px;
}

.field-item label {
  display: block;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #a2a3b7;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}

.field-item input, .field-item textarea {
  width: 100%;
  padding: 14px 18px;
  border: 2px solid #f1f2f6;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 500;
  transition: 0.2s;
}

.field-item input:focus, .field-item textarea:focus {
  border-color: #A08B7A;
  background: #fff;
  outline: none;
}

.field-input-disabled { background: #f8f9fa; color: #b2bec3; cursor: not-allowed; }

/* ACTIVITY STYLES */
.activity-title {
  font-size: 1.1rem;
  font-weight: 900;
  color: #1e1e2d;
  margin-bottom: 20px;
}

.activity-stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card-mini {
  background: #f8f9fa;
  padding: 25px 20px;
  border-radius: 24px;
  text-align: center;
  border: 1px solid #f1f2f6;
}

.stat-card-mini .sc-label {
  display: block;
  font-size: 0.75rem;
  color: #a2a3b7;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.stat-card-mini .sc-value { font-size: 1.6rem; font-weight: 900; color: #1e1e2d; }
.stat-card-mini .sc-value-green { font-size: 1.6rem; font-weight: 900; color: #2ecc71; }
.stat-card-mini .sc-value-blue { font-size: 1.6rem; font-weight: 900; color: #3498db; }

.activity-table-wrapper {
  border: 1px solid #f1f2f6;
  border-radius: 24px;
  margin-bottom: 20px;
}

.activity-data-table {
  width: 100%;
  border-collapse: collapse;
}

.activity-data-table th {
  background: #fafbfc;
  padding: 18px;
  text-align: left;
  font-size: 0.8rem;
  font-weight: 800;
  color: #a2a3b7;
  text-transform: uppercase;
}

.activity-data-table td {
  padding: 18px;
  border-top: 1px solid #f1f2f6;
  font-size: 0.95rem;
}

.order-code-cell { font-family: monospace; color: #3498db; font-weight: 700; }
.order-price-cell { font-weight: 800; color: #2d3436; }

/* EXPANDABLE ROW STYLES */
.order-master-row {
  cursor: pointer;
  transition: background 0.2s;
}
.order-master-row:hover { background: #fcfcfd; }
.order-master-row.is-expanded { background: #f8f9fa; }
.expand-icon { color: #b2bec3; font-size: 0.7rem; text-align: center; }

.order-details-row td {
  padding: 0 !important;
  border-top: none !important;
  background: #f8f9fa;
}

.items-mini-list {
  padding: 15px 30px 25px 70px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.mini-item-card {
  display: flex;
  align-items: center;
  gap: 15px;
  background: #fff;
  padding: 10px 15px;
  border-radius: 12px;
  border: 1px solid #f1f2f6;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}

.mini-item-img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  object-fit: cover;
  background: #f1f2f6;
}

.mini-item-info { flex: 1; }
.mi-name { font-size: 0.9rem; font-weight: 700; color: #2d3436; margin: 0; }
.mi-variant { font-size: 0.75rem; color: #a2a3b7; margin: 2px 0 0; }
.mini-item-price { text-align: right; }
.mi-qty { font-size: 0.75rem; font-weight: 800; color: #b2bec3; margin: 0; }
.mi-subtotal { font-size: 0.85rem; font-weight: 800; color: #1e1e2d; margin: 0; }

/* FOOTER ACTIONS */
.profile-footer-actions {
  margin-top: auto;
  display: flex;
  gap: 15px;
  padding-top: 20px;
}

.btn-primary-action {
  flex: 1;
  background: #1e1e2d;
  color: #fff;
  padding: 16px;
  border-radius: 16px;
  font-weight: 800;
  border: none;
  cursor: pointer;
}

.btn-secondary-action {
  padding: 16px 25px;
  background: #f1f2f6;
  color: #636e72;
  border-radius: 16px;
  font-weight: 700;
  border: none;
  cursor: pointer;
}

/* STAFF CREATE MODAL */
.modal-simple-body { 
  padding: 40px; 
  overflow-y: auto !important; 
  flex: 1;
}
.staff-create-layout { display: flex; flex-direction: column; gap: 40px; }
.avatar-center-box { text-align: center; }
.avatar-circle { width: 140px; height: 140px; border-radius: 70px; object-fit: cover; margin-bottom: 20px; border: 5px solid #f1f2f6; }
.btn-select-file { background: #1e1e2d; color: #fff; padding: 12px 25px; border-radius: 30px; cursor: pointer; }
.staff-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.span-2 { grid-column: span 2; }
.admin-confirm-box { background: #fff5f5; padding: 20px; border-radius: 20px; border: 2px dashed #ff7675; }
.modal-bottom-actions { display: flex; gap: 15px; margin-top: 40px; }
.btn-confirm-save { flex: 1; background: #A08B7A; color: #fff; padding: 16px; border-radius: 16px; border: none; font-weight: 800; cursor: pointer; }
.btn-cancel-light { padding: 16px 30px; background: #f1f2f6; border: none; border-radius: 16px; cursor: pointer; }

@keyframes modalEnter {
  from { opacity: 0; transform: scale(0.9) translateY(40px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

@media (max-width: 1100px) {
  .admin-main-modal { width: 95%; }
  .modal-content-flex { flex-direction: column !important; }
  .modal-side-profile { width: 100% !important; border-right: none; border-bottom: 1px solid #f1f2f6; }
}

/* TOAST ANIMATIONS */
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(30px); }
</style>
