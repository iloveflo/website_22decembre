<template>
  <div class="home-page">
    <Header />
    
    <main class="main-content">
      <router-view />
    </main>
    
    <Footer />
    

  </div>
</template>

<script setup>
import Header from '../components/header.vue';
import Footer from '../components/footer.vue';
</script>