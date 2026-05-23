<template>
  <main class="home-vintage">
    <!-- HERO SECTION: Elegant Split Layout -->
    <section class="hero-split">
      <div class="hero-text fade-up">
        <span class="hero-subtitle">— Xuân Hè 2026 —</span>
        <h1 class="hero-title">22.Décembre</h1>
        <div class="hero-divider"></div>
        <p class="hero-desc">Nơi thời trang không chỉ là quần áo, mà là sự lưu giữ những khoảnh khắc thanh lịch vượt thời gian.</p>
        <router-link to="/products" class="btn-vintage-dark">Khám Phá Cửa Hàng</router-link>
      </div>
      <div class="hero-visual fade-up delay-1">
        <div class="hero-img-frame">
          <div class="hero-img-inner">
            <img src="/images/photo-1515886657613-9f3515b0c78f.jpg" alt="Vintage Fashion" />
          </div>
        </div>
      </div>
    </section>

    <!-- MANIFESTO SECTION -->
    <section class="manifesto-section reveal">
      <div class="manifesto-content">
        <h2 class="manifesto-title">"Cái đẹp thực sự không nằm ở sự phô trương, mà ẩn mình trong những chi tiết tinh tế nhất."</h2>
        <p class="manifesto-text">Tại 22.Décembre, chúng tôi lựa chọn từng thớ vải, từng đường kim mũi chỉ với mong muốn mang đến cho bạn không chỉ một món đồ thời trang, mà là một trải nghiệm của sự thanh bình và vẻ đẹp cổ điển giữa nhịp sống hiện đại.</p>
        <img src="/22decembre.svg" class="manifesto-logo" alt="22.Decembre Logo"/>
      </div>
    </section>

    <!-- FEATURED CATEGORIES: Elegant Grid -->
    <section class="categories-section reveal">
      <div class="section-header">
        <h2>Danh Mục Nổi Bật</h2>
        <div class="divider"></div>
      </div>
      <div class="categories-grid">
        <router-link to="/products" class="category-card">
          <div class="cat-img-wrapper">
            <img src="/images/photo-1558618666-fcd25c85cd64.jpg" alt="Monochrome" style="object-position: top center;" />
          </div>
          <div class="cat-info">
            <h3>Áo Dài Tay</h3>
            <span>Khám phá</span>
          </div>
        </router-link>
        <router-link to="/products" class="category-card">
          <div class="cat-img-wrapper">
            <img src="/images/photo-1591047139829-d91aecb6caea.jpg" alt="Jackets" style="object-position: top center;" />
          </div>
          <div class="cat-info">
            <h3>Áo Khoác Nam</h3>
            <span>Khám phá</span>
          </div>
        </router-link>
        <router-link to="/products" class="category-card">
          <div class="cat-img-wrapper">
            <img src="/images/photo-1553062407-98eeb64c6a62.jpg" alt="Accessories" style="object-position: center;" />
          </div>
          <div class="cat-info">
            <h3>Phụ Kiện</h3>
            <span>Khám phá</span>
          </div>
        </router-link>
      </div>
    </section>

    <!-- EDITORIAL BLOCKS -->
    <section class="editorial-section">
      <div 
        v-for="(block, index) in editorials" 
        :key="index"
        class="editorial-block reveal"
        :class="{ 'reverse': index % 2 !== 0 }"
      >
        <div class="ed-image">
          <div class="ed-img-inner">
            <img :src="block.image" :alt="block.title" style="object-position: top center;" />
          </div>
        </div>
        <div class="ed-content">
          <span class="ed-meta">{{ block.meta }}</span>
          <h2 class="ed-title">{{ block.title }}</h2>
          <p class="ed-desc">{{ block.desc }}</p>
          <router-link :to="block.link" class="link-underline">{{ block.linkText }}</router-link>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const editorials = ref([
  {
    meta: "SỰ TINH TẾ",
    title: "Essential Basics",
    desc: "Nền tảng của mọi tủ đồ hoàn hảo. Những item cơ bản được nâng tầm với chất liệu premium và đường cắt tinh tế đến từng chi tiết.",
    linkText: "Khám phá Basics",
    link: "/products",
    image: "/images/photo-1489987707025-afc232f7ea0f.jpg"
  },
  {
    meta: "THỜI TRANG BỀN VỮNG",
    title: "Chất Liệu Nguyên Bản",
    desc: "Thời trang có trách nhiệm. Chúng tôi ưu tiên sử dụng linen, cotton hữu cơ và các chất liệu thân thiện với làn da cũng như môi trường.",
    linkText: "Tìm hiểu thêm",
    link: "/products",
    image: "/images/photo-1532453288672-3a27e9be9efd.jpg"
  },
  {
    meta: "GÓC NHÌN",
    title: "Nhật Ký 22.Décembre",
    desc: "Góc nhìn về những điều nhỏ bé, nghệ thuật và lối sống chậm. Đọc những bài viết sâu sắc từ đội ngũ sáng tạo của chúng tôi.",
    linkText: "Đọc Journal",
    link: "/about",
    image: "/images/photo-1524758631624-e2822e304c36.jpg"
  }
]);

let observer = null;

onMounted(() => {
  // Intersection Observer for Reveal animations (Optimized for performance)
  const options = {
    root: null,
    rootMargin: '0px 0px -50px 0px',
    threshold: 0.1
  };

  const handleIntersect = (entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
        // Ngừng observe sau khi đã hiện để tiết kiệm tài nguyên
        observer.unobserve(entry.target);
      }
    });
  };

  observer = new IntersectionObserver(handleIntersect, options);
  const targets = document.querySelectorAll('.reveal');
  targets.forEach(target => observer.observe(target));

  // Trigger fade-up cho hero
  setTimeout(() => {
    const fadeUps = document.querySelectorAll('.fade-up');
    fadeUps.forEach(el => el.classList.add('active'));
  }, 50);
});

onUnmounted(() => {
  if (observer) observer.disconnect();
});
</script>

<style scoped>
/* =====================
   GLOBAL & ANIMATIONS
   ===================== */
.home-vintage {
  background-color: var(--bg-color); /* Cream/Beige */
  color: var(--text-color);
  font-family: var(--font-body);
}

/* Animations được tối ưu hóa bằng will-change */
.fade-up {
  opacity: 0;
  transform: translateY(20px);
  will-change: opacity, transform;
  transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.fade-up.active {
  opacity: 1;
  transform: translateY(0);
}
.delay-1 { transition-delay: 0.15s; }

.reveal {
  opacity: 0;
  transform: translateY(30px);
  will-change: opacity, transform;
  transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.reveal.active {
  opacity: 1;
  transform: translateY(0);
}

/* =====================
   HERO SPLIT LAYOUT
   ===================== */
.hero-split {
  min-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8%;
  padding: 100px 5% 60px;
  max-width: 1400px;
  margin: 0 auto;
}

.hero-text {
  flex: 1;
  max-width: 500px;
}

.hero-subtitle {
  display: block;
  font-size: 13px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: #A08B7A;
  margin-bottom: 20px;
}

.hero-title {
  font-family: var(--font-heading);
  font-size: clamp(3rem, 6vw, 5rem);
  font-weight: 500;
  line-height: 1.1;
  margin: 0 0 25px 0;
  color: #333;
}

.hero-divider {
  width: 60px;
  height: 2px;
  background: #A08B7A;
  margin-bottom: 25px;
}

.hero-desc {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #555;
  margin-bottom: 45px;
}

.btn-vintage-dark {
  display: inline-block;
  padding: 16px 40px;
  background: #A08B7A;
  color: #fff;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-size: 13px;
  transition: all 0.3s ease;
  border: 1px solid #A08B7A;
}

.btn-vintage-dark:hover {
  background: #333;
  border-color: #333;
}

/* Khung ảnh Hero */
.hero-visual {
  flex: 1;
  display: flex;
  justify-content: center;
}

.hero-img-frame {
  width: 100%;
  max-width: 420px;
  aspect-ratio: 3/4;
  padding: 15px;
  background: #fff;
  border: 1px solid #E6E0D8;
  box-shadow: 0 15px 40px rgba(0,0,0,0.03);
}

.hero-img-inner {
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.hero-img-inner img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top center;
  transition: transform 1.5s ease;
}

.hero-img-frame:hover .hero-img-inner img {
  transform: scale(1.03);
}

/* =====================
   MANIFESTO SECTION
   ===================== */
.manifesto-section {
  padding: 100px 20px;
  text-align: center;
}

.manifesto-content {
  max-width: 800px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.manifesto-title {
  font-family: var(--font-heading);
  font-size: clamp(1.8rem, 4vw, 2.8rem);
  font-weight: 400;
  font-style: italic;
  color: #333;
  line-height: 1.4;
  margin-bottom: 40px;
}

.manifesto-text {
  font-size: 1.05rem;
  line-height: 1.9;
  color: #555;
  margin-bottom: 50px;
}

.manifesto-logo {
  width: 50px;
  opacity: 0.8;
}

/* =====================
   CATEGORIES GRID
   ===================== */
.categories-section {
  padding: 60px 40px 100px;
  max-width: 1400px;
  margin: 0 auto;
}

.section-header {
  text-align: center;
  margin-bottom: 60px;
}

.section-header h2 {
  font-family: var(--font-heading);
  font-size: 2.2rem;
  font-weight: 500;
  color: #333;
  margin-bottom: 20px;
}

.section-header .divider {
  width: 50px;
  height: 1px;
  background: #A08B7A;
  margin: 0 auto;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
}

.category-card {
  display: block;
  text-decoration: none;
}

.cat-img-wrapper {
  overflow: hidden;
  margin-bottom: 20px;
  aspect-ratio: 3/4;
}

.cat-img-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.category-card:hover .cat-img-wrapper img {
  transform: scale(1.03);
}

.cat-info {
  text-align: center;
}

.cat-info h3 {
  font-family: var(--font-heading);
  font-size: 1.3rem;
  color: #333;
  margin: 0 0 8px 0;
  font-weight: 400;
}

.cat-info span {
  font-size: 11px;
  color: #A08B7A;
  text-transform: uppercase;
  letter-spacing: 2px;
  transition: color 0.3s;
}

.category-card:hover .cat-info span {
  color: #333;
}

/* =====================
   EDITORIAL BLOCKS
   ===================== */
.editorial-section {
  padding: 0 0 80px;
}

.editorial-block {
  display: flex;
  align-items: center;
  max-width: 1100px;
  margin: 0 auto 100px;
  gap: 60px;
  padding: 0 40px;
}

.editorial-block.reverse {
  flex-direction: row-reverse;
}

.ed-image {
  flex: 1;
}

.ed-img-inner {
  overflow: hidden;
  aspect-ratio: 4/5;
}

.ed-img-inner img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.editorial-block:hover .ed-img-inner img {
  transform: scale(1.02);
}

.ed-content {
  flex: 1;
  padding: 0 20px;
}

.ed-meta {
  display: block;
  font-size: 11px;
  color: #A08B7A;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-bottom: 15px;
}

.ed-title {
  font-family: var(--font-heading);
  font-size: 2.4rem;
  font-weight: 500;
  color: #333;
  margin: 0 0 25px 0;
  line-height: 1.2;
}

.ed-desc {
  font-size: 1.05rem;
  line-height: 1.8;
  color: #555;
  margin-bottom: 35px;
}

.link-underline {
  display: inline-block;
  color: #333;
  text-decoration: none;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
  padding-bottom: 5px;
  border-bottom: 1px solid #333;
  transition: all 0.3s ease;
}

.link-underline:hover {
  color: #A08B7A;
  border-color: #A08B7A;
}

/* =====================
   NEWSLETTER
   ===================== */
.newsletter-section {
  background: #E6E0D8;
  padding: 80px 20px;
  text-align: center;
}

.newsletter-box {
  max-width: 500px;
  margin: 0 auto;
}

.newsletter-box h2 {
  font-family: var(--font-heading);
  font-size: 2.2rem;
  color: #333;
  margin-bottom: 15px;
  font-weight: 400;
}

.newsletter-box p {
  color: #555;
  margin-bottom: 40px;
  font-size: 0.95rem;
}

.newsletter-form {
  display: flex;
  border-bottom: 1px solid #333;
}

.newsletter-form input {
  flex: 1;
  background: transparent;
  border: none;
  padding: 12px 0;
  font-size: 0.95rem;
  color: #333;
  outline: none;
}

.newsletter-form input::placeholder {
  color: #888;
}

.newsletter-form button {
  background: transparent;
  border: none;
  color: #333;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  padding: 0 15px;
  transition: color 0.3s;
}

.newsletter-form button:hover {
  color: #A08B7A;
}

/* =====================
   RESPONSIVE
   ===================== */
@media (max-width: 992px) {
  .hero-split {
    flex-direction: column;
    text-align: center;
    gap: 50px;
  }
  .hero-divider {
    margin: 0 auto 25px;
  }
  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .editorial-block {
    gap: 40px;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 3rem;
  }
  .categories-grid {
    grid-template-columns: 1fr;
  }
  .editorial-block, .editorial-block.reverse {
    flex-direction: column;
    padding: 0 20px;
    gap: 30px;
  }
  .ed-image, .ed-content {
    width: 100%;
  }
  .ed-content {
    padding: 0;
  }
  .manifesto-section {
    padding: 60px 20px;
  }
  .categories-section {
    padding: 40px 20px 60px;
  }
}
</style>