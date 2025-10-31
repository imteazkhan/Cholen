<template>
  <section id="features" class="py-5 bg-light">
    <div class="container">
      <div class="row justify-content-center text-center mb-5">
        <div class="col-12 col-lg-8">
          <h2 class="display-5 fw-bold mb-3">Features</h2>
          <p class="lead text-muted">
            Discover the powerful features that make our web app the best choice for your needs.
          </p>
        </div>
      </div>
      
      <div class="row g-4">
        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body text-center p-4">
              <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                <i class="bi bi-bar-chart text-primary" style="font-size: 2rem;"></i>
              </div>
              <h5 class="card-title fw-bold">Powerful Analytics</h5>
              <p class="card-text text-muted">
                Visualize your data with our intuitive charts.
              </p>
            </div>
          </div>
        </div>
        
        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body text-center p-4">
              <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                <i class="bi bi-link-45deg text-primary" style="font-size: 2rem;"></i>
              </div>
              <h5 class="card-title fw-bold">Seamless Integration</h5>
              <p class="card-text text-muted">
                Connect with your favorite tools.
              </p>
            </div>
          </div>
        </div>
        
        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body text-center p-4">
              <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                <i class="bi bi-headset text-primary" style="font-size: 2rem;"></i>
              </div>
              <h5 class="card-title fw-bold">24/7 Support</h5>
              <p class="card-text text-muted">
                We're here to help you anytime.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
// Features component
</script>

<style scoped>
.hover-shadow {
  transition: box-shadow 0.3s ease;
}

.hover-shadow:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>