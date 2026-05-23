<template>
  <Transition name="bounce">
    <div v-if="isVisible && coupons.length" class="popup-overlay" @click.self="closePopup">
      <div class="popup-content">

        <button class="btn-close-popup" @click="closePopup" aria-label="Đóng">✕</button>

        <!-- Header -->
        <div class="popup-header">
          <span class="sale-badge">KHUYẾN MÃI HOT</span>
          <h2 class="popup-title">ƯU ĐÃI DÀNH CHO BẠN</h2>
          <p class="popup-subtitle">Sao chép mã và dùng ngay tại giỏ hàng</p>
        </div>

        <!-- Coupon Carousel -->
        <div class="carousel-wrap">
          <button class="nav-btn prev" @click="prev" v-if="coupons.length > 1">‹</button>

          <div class="voucher-ticket">
            <div class="voucher-left">
              <span class="discount-amount">
                {{ activeCoupon.discount_type === 'percent'
                    ? `-${activeCoupon.discount_value}%`
                    : formatCurrency(activeCoupon.discount_value) }}
              </span>
              <span class="discount-label">GIẢM GIÁ</span>
              <span v-if="activeCoupon.max_discount > 0" class="discount-max">
                Tối đa {{ formatCurrency(activeCoupon.max_discount) }}
              </span>
            </div>

            <div class="voucher-dash"></div>

            <div class="voucher-right">
              <p class="coupon-desc">{{ activeCoupon.description }}</p>

              <div class="code-box" @click="copyCode" :class="{ copied }">
                <span class="code-label">MÃ CODE</span>
                <span class="code-text">{{ activeCoupon.code }}</span>
                <span class="copy-hint">{{ copied ? '✓ Đã sao chép!' : 'Nhấn để sao chép' }}</span>
              </div>

              <div class="coupon-conditions">
                <span v-if="activeCoupon.min_order_value > 0">
                  Đơn tối thiểu {{ formatCurrency(activeCoupon.min_order_value) }}
                </span>
                <span v-if="activeCoupon.end_date">
                  · Hết hạn {{ formatDate(activeCoupon.end_date) }}
                </span>
                <span v-if="activeCoupon.usage_limit > 0" class="usage-left">
                  · Còn {{ activeCoupon.usage_limit - activeCoupon.used_count }} lượt
                </span>
              </div>
            </div>
          </div>

          <button class="nav-btn next" @click="next" v-if="coupons.length > 1">›</button>
        </div>

        <!-- Dots -->
        <div class="dots" v-if="coupons.length > 1">
          <span
            v-for="(_, i) in coupons"
            :key="i"
            class="dot"
            :class="{ active: i === currentIndex }"
            @click="currentIndex = i"
          ></span>
        </div>

        <!-- Thông tin nhận mã -->
        <div class="how-to-get">
          <p class="how-title">Làm thế nào để nhận mã?</p>
          <ul>
            <li>Mã <strong>công khai</strong> — tất cả khách hàng đều có thể dùng</li>
            <li>Mã <strong>cá nhân</strong> — được tặng qua email sau khi đặt hàng thành công, nhân dịp sinh nhật hoặc sự kiện VIP</li>
            <li>Mỗi mã chỉ dùng <strong>1 lần duy nhất</strong> trên mỗi tài khoản</li>
          </ul>
        </div>

        <button class="btn-action" @click="goShop">
          MUA SẮM NGAY
        </button>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const isVisible = ref(false);
const coupons = ref([]);
const currentIndex = ref(0);
const copied = ref(false);

const activeCoupon = computed(() => coupons.value[currentIndex.value] || {});

const prev = () => {
  currentIndex.value = (currentIndex.value - 1 + coupons.value.length) % coupons.value.length;
  copied.value = false;
};
const next = () => {
  currentIndex.value = (currentIndex.value + 1) % coupons.value.length;
  copied.value = false;
};

onMounted(async () => {
  if (sessionStorage.getItem('promo_popup_seen')) return;

  try {
    const res = await axios.get('/coupons/featured');
    if (res.data.status === 'success' && res.data.data.length > 0) {
      coupons.value = res.data.data;
      setTimeout(() => { isVisible.value = true; }, 1200);
    }
  } catch (e) {
    console.error('Lỗi tải mã khuyến mãi:', e);
  }
});

const closePopup = () => {
  isVisible.value = false;
  sessionStorage.setItem('promo_popup_seen', 'true');
};

const copyCode = () => {
  if (!activeCoupon.value.code) return;
  navigator.clipboard.writeText(activeCoupon.value.code);
  copied.value = true;
  setTimeout(() => { copied.value = false; }, 2200);
};

const goShop = () => {
  closePopup();
  router.push('/products');
};

const formatCurrency = (v) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v);

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('vi-VN');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Work+Sans:wght@700;900&display=swap');

/* ── Overlay ── */
.popup-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.65);
  backdrop-filter: blur(6px);
  z-index: 9999;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

/* ── Card ── */
.popup-content {
  position: relative;
  background: #fff;
  width: 100%;
  max-width: 460px;
  border: 3px solid #111;
  box-shadow: 10px 10px 0 #111;
  overflow: hidden;
}

/* ── Close ── */
.btn-close-popup {
  position: absolute; top: 10px; right: 12px;
  background: transparent; border: none;
  font-size: 1.4rem; cursor: pointer;
  z-index: 10; color: #555;
  line-height: 1;
}
.btn-close-popup:hover { color: #111; }

/* ── Header ── */
.popup-header {
  background: #111;
  color: #fff;
  text-align: center;
  padding: 20px 24px 16px;
}
.sale-badge {
  display: inline-block;
  background: #A08B7A;
  color: #fff;
  font-family: 'Space Mono', monospace;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 12px;
  letter-spacing: 2px;
  margin-bottom: 8px;
}
.popup-title {
  font-family: 'Work Sans', sans-serif;
  font-size: 1.7rem;
  font-weight: 900;
  text-transform: uppercase;
  margin: 0 0 4px;
  line-height: 1.1;
}
.popup-subtitle {
  font-size: 12px;
  color: rgba(255,255,255,0.7);
  margin: 0;
  font-family: 'Space Mono', monospace;
}

/* ── Carousel wrap ── */
.carousel-wrap {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 16px 8px 0;
}

.nav-btn {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  border: 2px solid #E6E0D8;
  background: #fff;
  border-radius: 50%;
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
  color: #555;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.nav-btn:hover { border-color: #A08B7A; color: #A08B7A; }

/* ── Voucher ticket ── */
.voucher-ticket {
  flex: 1;
  display: flex;
  border: 2px solid #E6E0D8;
  min-height: 110px;
  overflow: hidden;
  border-radius: 4px;
}

.voucher-left {
  background: #A08B7A;
  color: #fff;
  padding: 14px 12px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  min-width: 90px;
  text-align: center;
}
.discount-amount {
  font-family: 'Work Sans', sans-serif;
  font-size: 1.7rem; font-weight: 900;
  line-height: 1;
}
.discount-label { font-size: 9px; letter-spacing: 1.5px; margin-top: 3px; opacity: 0.85; }
.discount-max { font-size: 9px; opacity: 0.75; margin-top: 3px; text-align: center; }

.voucher-dash {
  width: 0;
  border-left: 2px dashed #ccc;
  position: relative;
  flex-shrink: 0;
}
.voucher-dash::before, .voucher-dash::after {
  content: ''; position: absolute; left: -7px;
  width: 13px; height: 13px;
  background: #fff; border-radius: 50%;
  border: 2px solid #E6E0D8;
}
.voucher-dash::before { top: -7px; }
.voucher-dash::after { bottom: -7px; }

.voucher-right {
  flex: 1;
  padding: 10px 12px;
  display: flex; flex-direction: column; gap: 6px;
}

.coupon-desc {
  font-size: 11.5px;
  color: #444;
  margin: 0;
  line-height: 1.4;
}

/* Code box */
.code-box {
  display: flex; flex-direction: column; align-items: flex-start;
  background: #f5f1ee;
  border: 1.5px dashed #A08B7A;
  border-radius: 4px;
  padding: 6px 10px;
  cursor: pointer;
  transition: background 0.2s;
  user-select: none;
}
.code-box:hover, .code-box.copied { background: #ede9e4; }
.code-label { font-size: 9px; font-weight: 700; color: #999; letter-spacing: 1px; }
.code-text {
  font-family: 'Space Mono', monospace;
  font-size: 1.1rem; font-weight: 700;
  color: #333; letter-spacing: 1.5px;
  line-height: 1.2;
}
.copy-hint { font-size: 9px; color: #A08B7A; font-weight: 600; }

/* Conditions */
.coupon-conditions {
  font-size: 10px;
  color: #999;
  line-height: 1.5;
}
.usage-left { color: #e07b39; font-weight: 700; }

/* ── Dots ── */
.dots {
  display: flex; gap: 5px;
  justify-content: center;
  padding: 10px 0 0;
}
.dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: #ddd;
  cursor: pointer;
  transition: background 0.2s;
}
.dot.active { background: #A08B7A; }

/* ── How to get ── */
.how-to-get {
  margin: 12px 16px 0;
  background: #f9f7f5;
  border: 1px solid #E6E0D8;
  border-radius: 6px;
  padding: 10px 14px;
}
.how-title {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #555;
  margin: 0 0 6px;
}
.how-to-get ul {
  margin: 0; padding: 0 0 0 16px;
  list-style: disc;
}
.how-to-get li {
  font-size: 11px;
  color: #666;
  line-height: 1.6;
}

/* ── CTA Button ── */
.btn-action {
  display: block; width: 100%;
  background: #111; color: #fff;
  border: none;
  border-top: 3px solid #A08B7A;
  padding: 14px;
  margin-top: 14px;
  font-family: 'Work Sans', sans-serif;
  font-weight: 900;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.btn-action:hover { background: #A08B7A; }

/* ── Animation ── */
.bounce-enter-active { animation: bounceIn 0.45s cubic-bezier(0.175,0.885,0.32,1.275); }
.bounce-leave-active { animation: bounceIn 0.3s reverse ease-in; }
@keyframes bounceIn {
  from { transform: scale(0.7); opacity: 0; }
  to   { transform: scale(1);   opacity: 1; }
}
</style>