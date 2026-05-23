<template>
  <footer class="site-footer">
    <div class="footer-container">
      
      <div class="footer-top">
        <div class="newsletter-wrapper">
          <h3 class="newsletter-title">ĐĂNG KÝ NHẬN TIN</h3>
          <p class="newsletter-desc">Nhận thông tin cập nhật mới nhất về bộ sưu tập và ưu đãi độc quyền.</p>
          <form class="newsletter-form" @submit.prevent>
            <input type="email" placeholder="EMAIL CỦA BẠN" class="newsletter-input" />
            <button class="newsletter-btn">ĐĂNG KÝ</button>
          </form>
        </div>
      </div>

      <div class="divider"></div>

      <div class="footer-middle">
        <div class="footer-col">
          <h4 class="footer-heading">22.DÉCEMBRE</h4>
          <p class="brand-desc">
            Một chốn nhỏ bình yên mang đậm phong cách tối giản, vintage nhẹ nhàng dành cho những tâm hồn hoài niệm.
          </p>
          <div class="contact-info">
            <p>Hotline: 1900 0000</p>
            <p>Email: hello@22decembre.com</p>
          </div>
        </div>

        <div class="footer-col">
          <h4 class="footer-heading">KHÁM PHÁ</h4>
          <ul class="footer-links">
            <li><router-link :to="{ name: 'product-category', params: { slug: 'new-arrivals' } }">New Arrivals</router-link></li>
            <li><router-link :to="{ name: 'product-category', params: { slug: 'ao' } }">Áo</router-link></li>
            <li><router-link :to="{ name: 'product-category', params: { slug: 'quan' } }">Quần</router-link></li>
            <li><router-link :to="{ name: 'product-category', params: { slug: 'sale' } }">Sale Off</router-link></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4 class="footer-heading">HỖ TRỢ</h4>
          <ul class="footer-links">
            <li><router-link to="/help/shipping">Chính sách vận chuyển</router-link></li>
            <li><router-link to="/help/returns">Đổi trả & Hoàn tiền</router-link></li>
            <li><router-link to="/help/size-guide">Hướng dẫn chọn size</router-link></li>
            <li><router-link to="/contact">Liên hệ</router-link></li>
          </ul>
        </div>

        <div class="footer-col">
        <h4 class="footer-heading">THEO DÕI</h4>
        <div class="social-links">
            
            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M22.676 0H1.324C.593 0 0 .593 0 1.324v21.352C0 23.408.593 24 1.324 24h11.494v-9.294H9.689v-3.621h3.129V8.41c0-3.099 1.894-4.785 4.659-4.785 1.325 0 2.464.097 2.796.141v3.24l-1.917.001c-1.504 0-1.796.715-1.796 1.763v2.311h3.584l-.467 3.621h-3.117V24h6.116c.73 0 1.324-.592 1.324-1.324V1.324C24 .593 23.408 0 22.676 0z"/>
            </svg>
            <span>FACEBOOK</span>
            </a>

            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M12 2.163c3.204 0 3.584.012 4.849.07 1.366.062 2.633.344 3.608 1.312.969.969 1.25 2.238 1.312 3.608.058 1.265.07 1.645.07 4.849s-.012 3.584-.07 4.849c-.062 1.366-.344 2.633-1.312 3.608-.969.969-2.238 1.25-3.608 1.312-1.265.058-1.645.07-4.849.07s-3.584-.012-4.849-.07c-1.366-.062-2.633-.344-3.608-1.312-.969-.969-1.25-2.238-1.312-3.608-.058-1.265-.07-1.645-.07-4.849s.012-3.584.07-4.849c.062-1.366.344-2.633 1.312-3.608.969-.969 2.238-1.25 3.608-1.312 1.265-.058 1.645-.07 4.849-.07M12 0C8.741 0 8.333.014 7.053.072 2.695.272.272 2.69 .072 7.053.014 8.333 0 8.741 0 12c0 3.259.014 3.667.072 4.947.2 4.358 2.618 6.78 6.98 6.981 1.28.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm3.98-10.98a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/>
            </svg>
            <span>INSTAGRAM</span>
            </a>

            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 2.23-.9 4.46-2.37 6.11-1.48 1.67-3.6 2.67-5.87 2.81-2.26.15-4.59-.39-6.42-1.72-1.85-1.35-3.07-3.41-3.36-5.69-.28-2.28.29-4.67 1.68-6.46 1.41-1.81 3.63-2.91 5.95-3.04v4.02c-1.16.05-2.32.48-3.15 1.3-1.07 1.05-1.48 2.68-1.09 4.14.39 1.48 1.65 2.66 3.12 3.01 1.49.36 3.12.06 4.31-.95 1.05-.88 1.64-2.23 1.67-3.61.02-4.9.01-9.8.01-14.71z"/>
            </svg>
            <span>TIKTOK</span>
            </a>

            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M21.582 6.186a2.686 2.686 0 0 0-1.884-1.895C17.973 3.846 12 3.846 12 3.846s-5.973 0-7.698.445a2.686 2.686 0 0 0-1.884 1.895C2 7.915 2 12 2 12s0 4.085.418 5.814a2.686 2.686 0 0 0 1.884 1.895C5.973 20.154 12 20.154 12 20.154s5.973 0 7.698-.445a2.686 2.686 0 0 0 1.884-1.895C22 16.085 22 12 22 12s0-4.085-.418-5.814zM9.996 15.005l0-6.01 5.362 3.004-5.362 3.006z"/>
            </svg>
            <span>YOUTUBE</span>
            </a>

        </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p class="copyright">© 2025 22.DÉCEMBRE. ALL RIGHTS RESERVED.</p>
        <div class="payment-methods">
          <span>VISA</span>
          <span>MASTERCARD</span>
          <span>MOMO</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
// Không cần logic phức tạp
</script>

<style scoped>
/* Reset cơ bản */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.site-footer {
  background-color: #E6E0D8;
  color: #333333;
  font-family: var(--font-body);
  padding: 80px 0 30px 0;
  border-top: 1px solid #D9CFC4;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

/* --- NEWSLETTER SECTION --- */
.footer-top {
  max-width: 600px;
  margin: 0 auto 70px auto;
  text-align: center;
}

.newsletter-title {
  font-size: 18px;
  font-weight: 800;
  letter-spacing: 3px;
  margin-bottom: 12px;
  text-transform: uppercase;
  color: #333333;
}

.newsletter-desc {
  font-size: 14px;
  color: #555555;
  margin-bottom: 28px;
  letter-spacing: 0.3px;
  line-height: 1.5;
}

.newsletter-form {
  display: flex;
  justify-content: center;
  gap: 0;
  max-width: 500px;
  margin: 0 auto;
}

.newsletter-input {
  flex: 1;
  padding: 16px 20px;
  background-color: #FAF9F6;
  border: 1px solid #D9CFC4;
  border-right: none;
  color: #333333;
  font-size: 14px;
  outline: none;
  border-radius: 0;
  transition: border-color 0.3s ease;
  font-family: inherit;
}

.newsletter-input:focus {
  border-color: #e0e0e0;
}

.newsletter-input::placeholder {
  color: #888888;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 12px;
}

.newsletter-btn {
  padding: 16px 32px;
  background-color: #A08B7A;
  color: #FFF;
  border: 1px solid #A08B7A;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  transition: all 0.3s ease;
  border-radius: 0;
  white-space: nowrap;
}

.newsletter-btn:hover {
  background-color: #333333;
  color: #FFF;
  border-color: #333333;
  transform: translateX(2px);
}

.newsletter-btn:active {
  transform: scale(0.98);
}

/* Divider */
.divider {
  height: 1px;
  background: linear-gradient(to right, transparent, #D9CFC4 20%, #D9CFC4 80%, transparent);
  margin-bottom: 70px;
}

/* --- MAIN GRID --- */
.footer-middle {
  display: grid;
  grid-template-columns: 1.8fr 1fr 1fr 0.8fr;
  gap: 60px;
  margin-bottom: 70px;
}

.footer-heading {
  font-size: 15px;
  font-weight: 800;
  margin-bottom: 28px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #333333;
}

.brand-desc {
  font-size: 14px;
  color: #555555;
  line-height: 1.8;
  margin-bottom: 24px;
  max-width: 350px;
}

.contact-info p {
  font-size: 14px;
  color: #555555;
  margin: 8px 0;
  line-height: 1.6;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 14px;
}

.footer-links a {
  color: #555555;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.3s ease;
  display: inline-block;
  position: relative;
}

.footer-links a::after {
  content: '';
  position: absolute;
  width: 0;
  height: 1px;
  bottom: -2px;
  left: 0;
  background-color: #333;
  transition: width 0.3s ease;
}

.footer-links a:hover {
  color: #333;
  transform: translateX(4px);
}

.footer-links a:hover::after {
  width: 100%;
}

/* Social Icons */
.social-links {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.social-icon {
  color: #333333;
  text-decoration: none;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #D9CFC4;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  width: fit-content;
  min-width: 160px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  letter-spacing: 1.2px;
  text-transform: uppercase;
  border-radius: 0;
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.social-icon::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 0%;
  height: 100%;
  background-color: #A08B7A;
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: -1;
}

.icon-svg {
  display: block;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  transition: transform 0.4s ease;
}

.social-icon:hover::before {
  width: 100%;
}

.social-icon:hover {
  color: #FFF;
  border-color: #A08B7A;
  transform: translateX(5px);
  box-shadow: 0 4px 15px rgba(160, 139, 122, 0.3);
}

.social-icon:hover .icon-svg {
  transform: scale(1.15);
}

/* --- BOTTOM SECTION --- */
.footer-bottom {
  border-top: 1px solid #E6E0D8;
  padding-top: 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.copyright {
  font-size: 12px;
  color: #666666;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.payment-methods {
  display: flex;
  align-items: center;
  gap: 20px;
}

.payment-methods span {
  font-size: 12px;
  color: #666666;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
}

/* --- RESPONSIVE DESIGN --- */

/* Tablet (768px - 1024px) */
@media (max-width: 1024px) {
  .footer-middle {
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 50px;
  }
  
  .social-links {
    grid-column: span 3;
    flex-direction: row;
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}

/* Tablet nhỏ (600px - 768px) */
@media (max-width: 768px) {
  .site-footer {
    padding: 60px 0 24px 0;
  }
  
  .footer-container {
    padding: 0 20px;
  }
  
  .footer-top {
    margin-bottom: 50px;
  }
  
  .newsletter-title {
    font-size: 18px;
  }
  
  .newsletter-desc {
    font-size: 13px;
  }
  
  .footer-middle {
    grid-template-columns: 1fr 1fr;
    gap: 40px;
  }
  
  .footer-col:first-child {
    grid-column: span 2;
  }
  
  .social-links {
    grid-column: span 2;
  }
  
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
}

/* Mobile (max 600px) */
@media (max-width: 600px) {
  .site-footer {
    padding: 50px 0 20px 0;
  }
  
  .footer-top {
    margin-bottom: 40px;
  }
  
  .newsletter-title {
    font-size: 16px;
    letter-spacing: 2px;
  }
  
  .newsletter-desc {
    font-size: 13px;
    margin-bottom: 24px;
  }
  
  .newsletter-form {
    flex-direction: column;
    gap: 12px;
  }
  
  .newsletter-input {
    width: 100%;
    border: 1px solid #333;
    padding: 14px 18px;
  }
  
  .newsletter-btn {
    width: 100%;
    padding: 14px 24px;
  }
  
  .divider {
    margin-bottom: 40px;
  }
  
  .footer-middle {
    grid-template-columns: 1fr;
    gap: 40px;
    text-align: center;
  }
  
  .footer-heading {
    font-size: 14px;
    margin-bottom: 20px;
  }
  
  .brand-desc {
    margin: 0 auto 20px auto;
    max-width: 100%;
  }
  
  .contact-info p {
    font-size: 13px;
  }
  
  .footer-links a {
    font-size: 13px;
  }
  
  .footer-links a:hover {
    transform: translateX(0);
    transform: scale(1.02);
  }
  
  .social-links {
    flex-direction: column;
    align-items: center;
  }
  
  .social-icon {
    width: 100%;
    max-width: 280px;
    justify-content: center;
    min-width: auto;
  }
  
  .footer-bottom {
    padding-top: 20px;
    gap: 12px;
  }
  
  .copyright,
  .payment-methods span {
    font-size: 11px;
  }
  
  .payment-methods {
    flex-wrap: wrap;
    justify-content: center;
  }
}

/* Mobile nhỏ (max 400px) */
@media (max-width: 400px) {
  .footer-container {
    padding: 0 16px;
  }
  
  .newsletter-title {
    font-size: 15px;
  }
  
  .newsletter-input,
  .newsletter-btn {
    font-size: 13px;
  }
  
  .footer-heading {
    font-size: 13px;
  }
  
  .social-icon {
    padding: 12px 16px;
    font-size: 11px;
    gap: 12px;
  }
  
  .icon-svg {
    width: 16px;
    height: 16px;
  }
}
</style>