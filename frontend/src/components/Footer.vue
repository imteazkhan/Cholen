<template>
  <footer class="bg-light border-top py-4 mt-auto">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-12 col-md-4 text-center text-md-start mb-2 mb-md-0">
          <p class="text-muted mb-0">© 2025 <b>Cholen!</b> Ride Sharing App. All rights reserved.</p>
        </div>
        
        <div class="col-12 col-md-4 text-center mb-2 mb-md-0">
          <div class="d-flex justify-content-center gap-3">
            <a href="#" class="text-muted text-decoration-none hover-primary">Privacy Policy</a>
            <a href="#" class="text-muted text-decoration-none hover-primary">Terms of Service</a>
          </div>
        </div>
        
        <div class="col-12 col-md-4 text-center text-md-end">
          <div class="d-flex justify-content-center justify-content-md-end gap-3">
            <a href="#" class="text-muted hover-primary" title="Facebook">
              <i class="bi bi-facebook fs-5"></i>
            </a>
            <a href="#" class="text-muted hover-primary" title="Twitter">
              <i class="bi bi-twitter fs-5"></i>
            </a>
            <a href="#" class="text-muted hover-primary" title="LinkedIn">
              <i class="bi bi-linkedin fs-5"></i>
            </a>
            <a href="#" class="text-muted hover-primary" title="GitHub">
              <i class="bi bi-github fs-5"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
// Footer component
</script>

<style scoped>
.hover-primary {
  transition: color 0.3s ease;
}

.hover-primary:hover {
  color: #0d6efd !important;
}

footer {
  background-color: #f8f9fa;
}
</style>