<template>
  <div class="admin-content">

    <div class="page-header">
      <div class="header-main">
        <div class="title-section">
          <h1 class="page-title">Quản lý danh mục</h1>
          <p class="page-subtitle">Phân loại sản phẩm để khách hàng dễ dàng tìm kiếm.</p>
        </div>
        <div class="header-actions">
          <button class="btn-premium-add" @click="resetForm">
            Thêm danh mục mới
          </button>
        </div>
      </div>
    </div>

    <div class="category-manager-container">
      <!-- BÊN TRÁI: DANH SÁCH -->
      <div class="category-list-pane card">
        <div class="pane-header">
          <h3>Cấu trúc hiện tại</h3>
          <div class="stats-badge">{{ flatCategories.length }} nhóm</div>
        </div>

        <div class="tree-container" v-if="!loading">
          <div v-for="parent in categoriesTree" :key="parent.id" class="node-wrapper">
            <!-- Danh mục Cha -->
            <div class="node-content parent" :class="{ active: editingId === parent.id }" @click="editCategory(parent)">
              <div class="node-info">
                <span class="name">{{ parent.name }}</span>
              </div>
              <div class="node-meta">
                <span v-if="parent.status === 'inactive'" class="status-text-danger">Tạm ẩn</span>
                <span class="product-count">{{ parent.products_count || 0 }} SP</span>
                <span class="arrow-icon">›</span>
              </div>
            </div>

            <!-- Danh mục Con -->
            <div v-if="parent.children && parent.children.length > 0" class="children-nodes">
              <div v-for="child in parent.children" :key="child.id" class="node-content child"
                :class="{ active: editingId === child.id }" @click="editCategory(child)">
                <div class="node-info">
                  <span class="tree-line"></span>
                  <span class="name">{{ child.name }}</span>
                </div>
                <div class="node-meta">
                  <span v-if="child.status === 'inactive'" class="status-text-danger">Tạm ẩn</span>
                  <span class="product-count">{{ child.products_count || 0 }} SP</span>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="categoriesTree.length === 0" class="empty-state">
            <p>Chưa có dữ liệu danh mục.</p>
          </div>
        </div>
        <div v-else class="loading-container">
          <div class="premium-spinner"></div>
          <p>Đang tải...</p>
        </div>
      </div>

      <!-- BÊN PHẢI: FORM CHỈNH SỬA -->
      <div class="category-form-pane card" :class="{ 'editing-mode': editingId }">
        <div class="form-header">
          <div class="header-left">
            <span class="mode-label">{{ editingId ? 'CHẾ ĐỘ SỬA' : 'CHẾ ĐỘ THÊM' }}</span>
            <h3>{{ editingId ? 'Cập nhật: ' + form.name : 'Tạo mới danh mục' }}</h3>
          </div>
          <button v-if="editingId" class="btn-cancel" @click="resetForm">Hủy bỏ</button>
        </div>

        <form @submit.prevent="saveCategory" class="premium-form">
          <div class="form-grid">
            <div class="form-group full-width">
              <label>Tên danh mục <span style="color: #e74c3c">*</span></label>
              <input v-model="form.name" type="text" class="premium-input" placeholder="Ví dụ: Đồ Nam, Phụ Kiện..." required minlength="2" />
            </div>
            
            <div class="form-group">
              <label>Thuộc nhóm</label>
              <select v-model="form.parent_id" class="premium-input select" :disabled="hasChildren">
                <option :value="null">Danh mục gốc (Cấp cao nhất)</option>
                <optgroup label="Chọn nhóm cha">
                  <option v-for="cat in rootCategoriesOnly" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </optgroup>
              </select>
              <p v-if="hasChildren" class="field-hint warning">
                Không thể đổi nhóm vì danh mục này đang chứa các mục con.
              </p>
            </div>

            <div class="form-group">
              <label>Trạng thái hiển thị</label>
              <select v-model="form.status" class="premium-input select">
                <option value="active">Đang hiển thị</option>
                <option value="inactive">Đang tạm ẩn</option>
              </select>
              <p class="field-hint">Tạm ẩn sẽ không cho khách hàng thấy trên web.</p>
            </div>
          </div>

          <div class="form-group">
            <label>Mô tả chi tiết</label>
            <textarea v-model="form.description" class="premium-input textarea" rows="4" placeholder="Nhập mô tả ngắn gọn..."></textarea>
          </div>

          <div class="form-footer">
            <button type="submit" class="btn-save-premium" :disabled="saving">
              <span v-if="saving" class="spinner-small"></span>
              {{ editingId ? 'Lưu thay đổi' : 'Xác nhận tạo mới' }}
            </button>
            
            <button v-if="editingId && canDelete" type="button" class="btn-delete-premium" @click="confirmDelete" :disabled="saving">
              Xóa bỏ
            </button>
          </div>
        </form>

        <div class="integrity-card">
          <div class="integrity-text">
            Mẹo: Danh mục cha nên là các nhóm chính. Danh mục con giúp khách hàng lọc sản phẩm chi tiết hơn.
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const categoriesTree = ref([])
const flatCategories = ref([])
const loading = ref(false)
const saving = ref(false)
const editingId = ref(null)
const userRole = ref(localStorage.getItem('user_role'))

const canDelete = computed(() => {
  return userRole.value === 'admin' // Chỉ admin mới có quyền xóa
})

const form = ref({
  name: '',
  parent_id: null,
  description: '',
  status: 'active'
})

const rootCategoriesOnly = computed(() => {
  return flatCategories.value.filter(c => 
    c.parent_id === null && c.id !== editingId.value
  )
})

const hasChildren = computed(() => {
  if (!editingId.value) return false
  const current = categoriesTree.value.find(c => c.id === editingId.value)
  return current && current.children && current.children.length > 0
})

const fetchCategories = async () => {
  loading.value = true
  try {
    const [treeRes, flatRes] = await Promise.all([
      axios.get('/admin/categories/tree'),
      axios.get('/admin/categories')
    ])
    categoriesTree.value = treeRes.data
    flatCategories.value = flatRes.data
  } catch (e) {
    Swal.fire('Lỗi hệ thống', 'Không thể kết nối để lấy danh mục', 'error')
  } finally {
    loading.value = false
  }
}

const editCategory = (cat) => {
  editingId.value = cat.id
  form.value = {
    name: cat.name,
    parent_id: cat.parent_id,
    description: cat.description || '',
    status: cat.status
  }
}

const resetForm = () => {
  editingId.value = null
  form.value = {
    name: '',
    parent_id: null,
    description: '',
    status: 'active'
  }
}

const saveCategory = async () => {
  if (!form.value.name || form.value.name.trim().length < 2) {
    Swal.fire('Cảnh báo', 'Vui lòng nhập tên danh mục (ít nhất 2 ký tự)', 'warning')
    return
  }
  
  saving.value = true
  try {
    if (editingId.value) {
      await axios.put(`/admin/categories/${editingId.value}`, form.value)
      Swal.fire('Thành công', 'Đã cập nhật thông tin danh mục', 'success')
    } else {
      await axios.post('/admin/categories', form.value)
      Swal.fire('Thành công', 'Đã thêm danh mục mới vào hệ thống', 'success')
    }
    await fetchCategories()
    if (!editingId.value) resetForm()
  } catch (e) {
    const msg = e.response?.data?.message || 'Không thể lưu thay đổi'
    Swal.fire('Thất bại', msg, 'error')
  } finally {
    saving.value = false
  }
}

const confirmDelete = () => {
  Swal.fire({
    title: 'Xác nhận xóa',
    html: `Bạn có chắc chắn muốn xóa vĩnh viễn danh mục <strong>${form.value.name}</strong>?<br>Hành động này chỉ thực hiện được nếu danh mục không có sản phẩm nào.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e74c3c',
    cancelButtonColor: '#f0f0f0',
    cancelButtonText: '<span style="color: #333">Quay lại</span>',
    confirmButtonText: 'Xác nhận xóa'
  }).then((result) => {
    if (result.isConfirmed) {
      executeDelete()
    }
  })
}

const executeDelete = async () => {
  if (!canDelete.value) {
    Swal.fire('Từ chối', 'Bạn không có quyền thực hiện hành động này', 'error')
    return
  }
  
  saving.value = true
  try {
    await axios.delete(`/admin/categories/${editingId.value}`)
    await fetchCategories()
    resetForm()
    Swal.fire('Thành công', 'Đã xóa danh mục khỏi hệ thống', 'success')
  } catch (e) {
    const msg = e.response?.data?.message || 'Danh mục đang được sử dụng, không thể xóa'
    Swal.fire('Thất bại', msg, 'error')
  } finally {
    saving.value = false
  }
}

onMounted(fetchCategories)
</script>

<style scoped>

/* BỐ CỤC TRANG */
.admin-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
  font-family: 'Inter', sans-serif;
}

.page-header { margin-bottom: 40px; }
.header-main { display: flex; justify-content: space-between; align-items: flex-end; }
.page-title { font-size: 1.8rem; font-weight: 800; color: #1a1a1a; margin: 0; }
.page-subtitle { color: #888; margin-top: 4px; font-size: 0.95rem; }

.btn-premium-add {
  background: #1a1a1a; color: #fff; border: none; padding: 12px 24px;
  border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-premium-add:hover { background: #333; }

.category-manager-container {
  display: grid;
  grid-template-columns: 400px 1fr;
  gap: 30px;
}

.card {
  background: #fff; border-radius: 12px; padding: 30px; border: 1px solid #eee;
}

.pane-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;
}
.pane-header h3 { font-size: 1rem; font-weight: 700; margin: 0; text-transform: uppercase; }
.stats-badge { font-size: 0.8rem; color: #888; }

/* CÂY DANH MỤC */
.tree-container { display: flex; flex-direction: column; gap: 8px; max-height: 650px; overflow-y: auto; }
.node-content {
  padding: 12px 16px; border-radius: 8px; cursor: pointer;
  display: flex; justify-content: space-between; align-items: center;
  background: #fdfdfd; border: 1px solid #f0f0f0; transition: 0.2s;
}
.node-content:hover { border-color: #ccc; }
.node-content.active { background: #1a1a1a; color: #fff; border-color: #1a1a1a; }

.node-info { display: flex; align-items: center; gap: 12px; }
.name { font-weight: 600; font-size: 0.95rem; }

.node-content.child { margin-left: 24px; }
.tree-line { width: 12px; height: 1px; background: #ddd; }

.node-meta { display: flex; align-items: center; gap: 12px; }
.status-text-danger { font-size: 0.7rem; font-weight: 700; color: #e74c3c; text-transform: uppercase; }
.product-count { font-size: 0.75rem; color: #888; }
.active .product-count { color: #aaa; }

/* BIỂU MẪU */
.form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.mode-label { font-size: 0.7rem; font-weight: 700; color: #999; border: 1px solid #eee; padding: 2px 8px; border-radius: 4px; }
.form-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; }
.btn-cancel { background: none; border: none; color: #999; font-weight: 600; cursor: pointer; font-size: 0.85rem; }

.premium-form { display: flex; flex-direction: column; gap: 20px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.full-width { grid-column: span 2; }

.form-group label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: #333; }
.premium-input {
  width: 100%; padding: 12px 16px; border-radius: 8px; border: 1px solid #ddd;
  background: #fff; font-family: inherit; font-size: 0.95rem; transition: 0.2s;
}
.premium-input:focus { border-color: #1a1a1a; outline: none; }
.premium-input.textarea { resize: none; }

.field-hint { font-size: 0.8rem; color: #999; margin-top: 6px; }
.field-hint.warning { color: #e67e22; font-weight: 600; }

.form-footer { display: flex; align-items: center; gap: 16px; margin-top: 20px; padding-top: 25px; border-top: 1px solid #f0f0f0; }
.btn-save-premium {
  flex: 1; background: #1a1a1a; color: #fff; border: none; padding: 14px; border-radius: 8px;
  font-weight: 600; font-size: 1rem; cursor: pointer;
}
.btn-delete-premium {
  background: #fff; color: #e74c3c; border: 1px solid #f5c6cb; padding: 14px 20px; border-radius: 8px;
  font-weight: 600; cursor: pointer;
}

.integrity-card { margin-top: 24px; padding: 16px; border-radius: 8px; background: #f9f9f9; font-size: 0.85rem; color: #777; border-left: 4px solid #eee; }

.premium-spinner { width: 30px; height: 30px; border: 3px solid #eee; border-top-color: #1a1a1a; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 1000px) {
  .category-manager-container { grid-template-columns: 1fr; }
}
</style>
