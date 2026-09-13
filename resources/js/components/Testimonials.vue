<template>
  <div id="testimonialCarousel" class="carousel slide mx-auto px-3">
    <div class="carousel-inner">
      <div v-for="(testimonial, index) in testimonials" :key="testimonial.id" class="carousel-item "
        :class="index == 0 ? 'active' : ''">
        <div class="col-lg-9 col-12 mx-auto bg-white p-3 p-md-5 testimonial">
          <div class="d-lg-flex align-items-center">
            <img :src="testimonial.image" class="profile-picture object-fit-cover" height="192px" width="192px" />
            <div class="w-100 px-lg-5 px-md-3 px-2">
              <div class="d-md-flex justify-content-between mb-3">
                <div class="my-4 my-lg-0">
                  <strong class="d-block">{{ testimonial.name }}</strong>
                  <small class="text-muted">{{
                    testimonial.designation
                    }}</small>
                </div>
                <div>
                  <i v-for="i in 5" :key="i" :class="i <= testimonial.rating
                    ? 'bi bi-star-fill text-warning me-1'
                    : 'bi bi-star text-muted me-1'
                    "></i>
                  <strong class="ms-2">{{
                    testimonial.rating.toFixed(1)
                    }}</strong>
                </div>
              </div>
              <p>
                {{ testimonial.description }}
              </p>
            </div>
          </div>
          <i class="bi bi-quote display-1 position-absolute quote-icon"></i>
        </div>
      </div>
    </div>
    <button class="carousel-control-prev text-dark w-25" type="button" data-bs-target="#testimonialCarousel"
      data-bs-slide="prev">
      <span class="bg-white px-2 py-1 border rounded-circle theme-shadow"><i class="bi bi-chevron-left"></i></span>
    </button>
    <button class="carousel-control-next text-dark w-25" type="button" data-bs-target="#testimonialCarousel"
      data-bs-slide="next">
      <span class="bg-white px-2 py-1 border rounded-circle theme-shadow"><i class="bi bi-chevron-right"></i></span>
    </button>
  </div>
</template>

<style scoped lang="scss">
.testimonial {
  border-radius: 2rem;
  box-shadow: 0px 16px 64px 0px #9747ff14;

  .profile-picture {
    border-radius: 1rem;
  }

  .quote-icon {
    color: #9747ff27;
    right: 20%;
    top: 70%;
  }
}
</style>

<script setup>
import { ref, onMounted } from "vue";

let testimonials = ref([]);

onMounted(async () => {
  const response = await axios.get(`/testimonial/list`);
  testimonials.value = response.data.data?.testimonials;
});
</script>
