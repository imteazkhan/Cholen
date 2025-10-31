<template>
  <section id="contact" class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 text-center mb-5">
          <h2 class="display-5 fw-bold">Get in Touch</h2>
        </div>
        <div class="col-12 col-lg-8">
          <form @submit.prevent="submitForm" class="row g-3">
            <div class="col-12">
              <label for="name" class="form-label fw-medium">Name</label>
              <input 
                type="text" 
                class="form-control" 
                id="name" 
                placeholder="Your Name"
                v-model="form.name"
                required
              >
            </div>
            
            <div class="col-12">
              <label for="email" class="form-label fw-medium">Email</label>
              <input 
                type="email" 
                class="form-control" 
                id="email" 
                placeholder="Your Email"
                v-model="form.email"
                required
              >
            </div>
            
            <div class="col-12">
              <label for="message" class="form-label fw-medium">Message</label>
              <textarea 
                class="form-control" 
                id="message" 
                rows="5" 
                placeholder="Your Message"
                v-model="form.message"
                required
              ></textarea>
            </div>
            
            <div class="col-12 text-end">
              <button type="submit" class="btn btn-primary px-4">
                Submit
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';

const form = ref({
  name: '',
  email: '',
  message: ''
});

const submitForm = () => {
  // Handle form submission
  console.log('Form submitted:', form.value);
  
  // Reset form
  form.value = {
    name: '',
    email: '',
    message: ''
  };
  
  // Show success message (you can implement a toast or alert)
  alert('Thank you for your message! We will get back to you soon.');
};
</script>