<template>
  <div class="overlay">
    <div class="modal">
      <div class="modal-header">
        <h2>{{ mode === 'create' ? 'Thêm sản phẩm' : 'Chỉnh sửa sản phẩm' }}</h2>
        <button class="btn-close" @click="$emit('close')">×</button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-body">
        <!-- Thông tin chung -->
        <div class="form-group">
          <label>Tên sản phẩm <span>*</span></label>
          <input v-model="form.name" type="text" required />
        </div>

        <!-- Nhóm danh mục + danh mục con -->
        <div class="form-row">
          <div class="form-group">
            <label>Nhóm sản phẩm <span>*</span></label>
            <select v-model="selectedParentId" @change="onParentChange">
              <option value="">-- Chọn nhóm (Áo / Quần / ...) --</option>
              <option
                v-for="pc in parentCategories"
                :key="pc.id"
                :value="pc.id"
              >
                {{ pc.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Danh mục <span>*</span></label>
            <select v-model="form.category_id" required>
              <option value="">-- Chọn danh mục trong nhóm --</option>
              <option
                v-for="cate in childCategories"
                :key="cate.id"
                :value="cate.id"
              >
                {{ cate.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Giá gốc (VND) <span>*</span></label>
            <input
              v-model.number="form.price"
              type="number"
              min="0"
              required
            />
          </div>

          <div class="form-group">
            <label>Trạng thái</label>
            <select v-model="form.status">
              <option value="active">Đang bán</option>
              <option value="inactive">Ngừng bán</option>
              <option value="out_of_stock">Hết hàng (Tự động nếu kho = 0)</option>
            </select>
          </div>

          <div class="form-group">
            <label>Tổng tồn kho (Tự động tính)</label>
            <div class="total-stock-badge">
              {{ totalQuantity }}
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Mô tả</label>
          <textarea v-model="form.description" rows="3" />
        </div>

        <!-- Cấu hình Thuộc tính (Động) -->
        <div class="attributes-config">
          <div class="config-header">
            <h3>Cấu hình thuộc tính (VD: Màu sắc, Kích thước)</h3>
            <button type="button" class="btn small" @click="addAttributeKey">
              + Thêm thuộc tính
            </button>
          </div>
          <div class="attribute-keys-list">
            <div v-for="(key, kIdx) in attributeKeys" :key="kIdx" class="key-item">
              <select v-model="attributeKeys[kIdx]" @change="onAttributeKeyChange(kIdx)" style="border:none; outline:none; font-size:13px; padding:4px;">
                <option value="" disabled>-- Chọn thuộc tính --</option>
                <option value="Màu sắc">Màu sắc</option>
                <option value="Kích thước">Kích thước</option>
                <option value="Chất liệu">Chất liệu</option>
                <option value="Kiểu dáng">Kiểu dáng</option>
                <option value="Giới tính">Giới tính</option>
              </select>
              <button type="button" class="btn-remove" @click="removeAttributeKey(kIdx)">×</button>
            </div>
          </div>
        </div>

        <!-- Biến thể -->
        <div class="variants-block">
          <div class="variants-header">
            <h3>Danh sách biến thể</h3>
            <button type="button" class="btn small primary" @click="addVariantRow">
              + Thêm biến thể
            </button>
          </div>

          <div class="variant-table-wrapper">
            <table class="variant-table">
              <thead>
                <tr>
                  <th v-for="key in attributeKeys" :key="key">{{ key || 'Thuộc tính' }}</th>
                  <th>Số lượng</th>
                  <th>Phụ phí (+VND)</th>
                  <th>SKU riêng</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(v, index) in variants" :key="index">
                  <td v-for="key in attributeKeys" :key="key">
                    <select v-if="attributeOptions[key]" v-model="v.variant_attributes[key]">
                      <option value="" disabled>-- Chọn --</option>
                      <option v-for="opt in attributeOptions[key]" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                    <input v-else v-model="v.variant_attributes[key]" type="text" :placeholder="'Giá trị ' + key" />
                  </td>
                  <td>
                    <input
                      v-model.number="v.quantity"
                      type="number"
                      min="0"
                      style="width: 80px"
                    />
                  </td>
                  <td>
                    <input
                      v-model.number="v.additional_price"
                      type="number"
                      min="0"
                      style="width: 100px"
                    />
                  </td>
                  <td>
                    <input v-model="v.sku" type="text" placeholder="Tự sinh" />
                  </td>
                  <td class="text-right">
                    <button
                      type="button"
                      class="btn-icon danger"
                      v-if="variants.length > 1"
                      @click="removeVariantRow(index)"
                    >
                      Xóa
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <p class="variant-hint">
            * Hệ thống sẽ tự động tính toán tổng tồn kho từ các biến thể này.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn ghost" @click="$emit('close')">
            Hủy
          </button>
          <button type="submit" class="btn primary" :disabled="submitting">
            {{ submitting ? 'Đang lưu...' : 'Lưu lại' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
  mode: {
    type: String,
    default: 'create'
  },
  product: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'saved'])

const selectedParentId = ref('')
const attributeKeys = ref(['Màu sắc', 'Kích thước']) // Mặc định

const form = ref({
  name: '',
  category_id: '',
  price: 0,
  description: '',
  status: 'active'
})

const variants = ref([])
const submitting = ref(false)

const isEdit = computed(() => props.mode === 'edit')

const baseAttributeOptions = {
  'Màu sắc': ['Đen', 'Trắng', 'Đỏ', 'Xanh', 'Vàng', 'Xám', 'Hồng', 'Cam'],
  'Kích thước': ['S', 'M', 'L', 'XL', 'XXL'],
  'Chất liệu': ['Cotton', 'Kaki', 'Denim', 'Nỉ', 'Lụa', 'Len', 'Polyester', 'Da'],
  'Kiểu dáng': ['Regular Fit', 'Slim Fit', 'Oversize', 'Khác'],
  'Giới tính': ['Nam', 'Nữ', 'Unisex']
}

const attributeOptions = computed(() => {
  const options = JSON.parse(JSON.stringify(baseAttributeOptions));
  // Add any existing values from variants to the options so they don't get lost
  variants.value.forEach(v => {
    Object.keys(v.variant_attributes || {}).forEach(key => {
      const val = v.variant_attributes[key];
      if (val && options[key] && !options[key].includes(val)) {
        options[key].push(val);
      }
    });
  });
  return options;
});

// ---- helpers ----
const createEmptyAttributes = () => {
  const attrs = {}
  attributeKeys.value.forEach(key => {
    if (key) attrs[key] = ''
  })
  return attrs
}

const defaultVariant = () => ({
  variant_attributes: createEmptyAttributes(),
  quantity: 0,
  additional_price: 0,
  sku: ''
})

const totalQuantity = computed(() => {
  return variants.value.reduce((sum, v) => sum + (Number(v.quantity) || 0), 0)
})

watch(totalQuantity, (newVal) => {
  if (newVal === 0) {
    form.value.status = 'out_of_stock'
  } else if (form.value.status === 'out_of_stock' && newVal > 0) {
    form.value.status = 'active'
  }
})

const resetForm = () => {
  selectedParentId.value = ''
  attributeKeys.value = ['Màu sắc', 'Kích thước']
  form.value = {
    name: '',
    category_id: '',
    price: 0,
    description: '',
    status: 'active'
  }
  variants.value = [defaultVariant()]
}

// Nhóm cha / con cho danh mục
const parentCategories = computed(() =>
  props.categories.filter(c => !c.parent_id)
)

const childCategories = computed(() => {
  if (selectedParentId.value) {
    return props.categories.filter(
      c => c.parent_id === Number(selectedParentId.value)
    )
  }
  return props.categories.filter(c => c.parent_id !== null)
})

const onParentChange = () => {
  form.value.category_id = ''
}

// Thuộc tính động
const addAttributeKey = () => {
  attributeKeys.value.push('')
}

const removeAttributeKey = (index) => {
  const keyToRemove = attributeKeys.value[index]
  attributeKeys.value.splice(index, 1)
  // Xóa key này khỏi tất cả variants
  variants.value.forEach(v => {
    delete v.variant_attributes[keyToRemove]
  })
}

const syncVariantsKeys = () => {
  variants.value.forEach(v => {
    attributeKeys.value.forEach(key => {
      if (key && v.variant_attributes[key] === undefined) {
        v.variant_attributes[key] = ''
      }
    })
  })
}

const onAttributeKeyChange = (index) => {
  const selectedKey = attributeKeys.value[index]
  const isDuplicate = attributeKeys.value.some((key, i) => key === selectedKey && i !== index)
  
  if (isDuplicate && selectedKey) {
    Swal.fire('Cảnh báo', `Thuộc tính "${selectedKey}" đã được chọn!`, 'warning')
    attributeKeys.value[index] = '' // reset
    return
  }
  syncVariantsKeys()
}

// Map dữ liệu khi edit
watch(
  () => props.product,
  (val) => {
    if (isEdit.value && val) {
      form.value = {
        name: val.name || '',
        category_id: val.category_id || '',
        price: val.price || 0,
        description: val.description || '',
        status: val.status || 'active'
      }

      const cate = props.categories.find(c => c.id === val.category_id)
      if (cate) {
        selectedParentId.value = cate.parent_id || cate.id
      } else {
        selectedParentId.value = ''
      }

      if (val.variants && val.variants.length) {
        // Lấy tất cả các keys từ variant đầu tiên (giả sử chúng giống nhau)
        const firstVariantAttrs = val.variants[0].variant_attributes || {}
        attributeKeys.value = Object.keys(firstVariantAttrs)
        if (attributeKeys.value.length === 0) {
            attributeKeys.value = ['Màu sắc', 'Kích thước']
        }

        variants.value = val.variants.map(v => ({
          variant_attributes: v.variant_attributes || createEmptyAttributes(),
          quantity: v.quantity ?? 0,
          additional_price: v.additional_price ?? 0,
          sku: v.sku || ''
        }))
      } else {
        variants.value = [defaultVariant()]
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

// Thao tác với biến thể
const addVariantRow = () => {
  variants.value.push(defaultVariant())
}

const removeVariantRow = (index) => {
  if (variants.value.length === 1) return
  variants.value.splice(index, 1)
}

const handleSubmit = async () => {
  if (!form.value.name || !form.value.category_id) return

  // Làm sạch attributes: bỏ các key rỗng
  const processedVariants = variants.value.map(v => {
    const cleanAttrs = {}
    attributeKeys.value.forEach(k => {
      if (k && v.variant_attributes[k]) {
        cleanAttrs[k] = v.variant_attributes[k]
      }
    })
    return {
      ...v,
      variant_attributes: cleanAttrs
    }
  })

  // Chỉ gửi những biến thể có ít nhất 1 thuộc tính hoặc có số lượng > 0
  const cleanVariants = processedVariants.filter(
    v => Object.keys(v.variant_attributes).length > 0 || v.quantity > 0
  )

  // Kiểm tra trùng lặp biến thể
  const variantSet = new Set()
  for (const v of cleanVariants) {
    const sortedKeys = Object.keys(v.variant_attributes).sort()
    const attrString = sortedKeys.map(k => `${k}:${v.variant_attributes[k]}`).join('|')
    
    if (variantSet.has(attrString)) {
      Swal.fire('Cảnh báo', 'Không được thêm trùng lặp các biến thể có cùng giá trị thuộc tính!', 'warning')
      return // Dừng submit
    }
    variantSet.add(attrString)
  }

  const payload = {
    ...form.value,
    variants: cleanVariants
  }

  submitting.value = true
  try {
    if (isEdit.value && props.product) {
      await axios.post(`/admin/products/${props.product.id}`, {
        ...payload,
        _method: 'PUT'
      });
    } else {
      await axios.post('/admin/products', payload)
    }
    Swal.fire('Thành công', 'Lưu sản phẩm thành công', 'success')
    emit('saved')
  } catch (e) {
    console.error(e)
    Swal.fire('Lỗi', 'Lưu sản phẩm thất bại', 'error')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
/* ============================================
   MODAL FORM - LIGHT MINIMAL STYLE
   ============================================ */

/* ============== OVERLAY ============== */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

/* ============== MODAL ============== */
.modal {
  background: #ffffff;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  border: 1px solid #e5e5e5;
}

/* ============== MODAL HEADER ============== */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 32px;
  border-bottom: 2px solid #e5e5e5;
  background: #fafafa;
}

.modal-header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #333333;
  letter-spacing: -0.5px;
}

.btn-close {
  background: transparent;
  border: 1px solid #d0d0d0;
  color: #666;
  width: 36px;
  height: 36px;
  font-size: 28px;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: #f5f5f5;
  border-color: #333333;
  color: #333333;
}

/* ============== MODAL BODY ============== */
.modal-body {
  padding: 32px;
  overflow-y: auto;
  flex: 1;
}

/* ============== FORM GROUPS ============== */
.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group label span {
  color: #dc2626;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
  width: 100%;
  background: #fafafa;
  border: 1px solid #d0d0d0;
  color: #1a1a1a;
  padding: 12px 16px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #333333;
  background: #ffffff;
}

.form-group textarea {
  resize: vertical;
}

.total-stock-badge {
  background: #f1f2f6;
  padding: 12px 16px;
  border-radius: 4px;
  font-weight: 800;
  font-size: 1.2rem;
  color: #1e1e2d;
  border: 1px dashed #A08B7A;
  text-align: center;
}

.form-group select {
  cursor: pointer;
}

/* ============== FORM ROW ============== */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

/* ============== VARIANTS BLOCK ============== */
.variants-block {
  margin-top: 32px;
  padding-top: 32px;
  border-top: 2px solid #e5e5e5;
}

.variants-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.variants-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #333333;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ============== VARIANT TABLE ============== */
.variant-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  margin-bottom: 12px;
  background: #fafafa;
  border: 1px solid #e5e5e5;
}

.variant-table thead {
  background: #f5f5f5;
  border-bottom: 2px solid #e5e5e5;
}

.variant-table th {
  padding: 12px 8px;
  text-align: left;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.5px;
}

.variant-table td {
  padding: 8px;
  border-bottom: 1px solid #e5e5e5;
}

.variant-table tbody tr:last-child td {
  border-bottom: none;
}

.variant-table input[type="text"],
.variant-table input[type="number"],
.variant-table select {
  width: 100%;
  background: #ffffff;
  border: 1px solid #d0d0d0;
  color: #1a1a1a;
  padding: 8px 10px;
  font-size: 13px;
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.variant-table input:focus,
.variant-table select:focus {
  border-color: #333333;
}

.variant-table input::placeholder {
  color: #999;
  font-size: 12px;
}

.variant-table select {
  cursor: pointer;
}

.variant-hint {
  font-size: 12px;
  color: #999;
  margin: 8px 0 0 0;
  font-style: italic;
}

/* ============== MODAL FOOTER ============== */
.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px 32px;
  border-top: 2px solid #e5e5e5;
  background: #fafafa;
}

/* ============== BUTTONS ============== */
.btn {
  background: transparent;
  border: 1px solid #c0c0c0;
  color: #1a1a1a;
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  white-space: nowrap;
}

.btn:hover {
  background: #f5f5f5;
  border-color: #a0a0a0;
}

.btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.btn.primary {
  background: #A08B7A;
  color: #fff;
  border-color: #333333;
  font-weight: 600;
}

.btn.primary:hover {
  background: #1a1a1a;
  border-color: #1a1a1a;
}

.btn.ghost {
  border-color: #d0d0d0;
  color: #666;
}

.btn.ghost:hover {
  border-color: #a0a0a0;
  color: #1a1a1a;
}

.btn.small {
  padding: 8px 16px;
  font-size: 12px;
}

.btn.danger {
  border-color: #dc2626;
  color: #dc2626;
}

.btn.danger:hover {
  background: #dc2626;
  color: #fff;
}

/* Dynamic Attributes Config */
.attributes-config {
  margin-bottom: 24px;
  background: #f9f9f9;
  padding: 16px;
  border: 1px dashed #d0d0d0;
}

.config-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.config-header h3 {
  margin: 0;
  font-size: 14px;
  color: #555;
  text-transform: uppercase;
}

.attribute-keys-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.key-item {
  display: flex;
  align-items: center;
  background: #fff;
  border: 1px solid #d0d0d0;
  padding: 4px 8px;
  border-radius: 4px;
}

.key-item input {
  border: none;
  outline: none;
  font-size: 13px;
  width: 120px;
  padding: 4px;
}

.btn-remove {
  background: none;
  border: none;
  color: #999;
  cursor: pointer;
  font-size: 18px;
  margin-left: 4px;
  padding: 0 4px;
}

.btn-remove:hover {
  color: #dc2626;
}

.variant-table-wrapper {
  overflow-x: auto;
  border: 1px solid #e5e5e5;
  margin-bottom: 12px;
}

.btn-icon {
  background: none;
  border: 1px solid #d0d0d0;
  padding: 6px 12px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.2s;
}

.btn-icon.danger {
  color: #dc2626;
  border-color: #fecaca;
}

.btn-icon.danger:hover {
  background: #dc2626;
  color: #fff;
  border-color: #dc2626;
}

/* ============== UTILITIES ============== */
.text-right {
  text-align: right;
}

/* ============== RESPONSIVE ============== */
@media (max-width: 768px) {
  .overlay {
    padding: 0;
  }

  .modal {
    max-width: 100%;
    max-height: 100vh;
    height: 100vh;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }

  .modal-header h2 {
    font-size: 20px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .variant-table {
    font-size: 11px;
  }

  .variant-table th,
  .variant-table td {
    padding: 6px 4px;
  }

  .variant-table input,
  .variant-table select {
    padding: 6px 8px;
    font-size: 12px;
  }

  .variants-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .btn.small {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .variant-table th:nth-child(5),
  .variant-table td:nth-child(5),
  .variant-table th:nth-child(6),
  .variant-table td:nth-child(6) {
    display: none;
  }
}
</style>
