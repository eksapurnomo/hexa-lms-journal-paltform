<template>
    <section class="position-relative z-1 rounded-3 rounded-md-pill p-3 p-md-5 newsletter">
        <div class="row">
            <div class="col-12 col-lg-6 my-lg-auto pe-0 pe-md-5 pt-0 py-lg-4">
                <h3 class="fw-bold">{{ $t("Don't Miss Update") }}</h3>
                <p class="m-0">
                    {{ $t("Stay connected with us to keep yourself updated. Subscribe to our newsletter to get great offers and updates") }}
                </p>
            </div>
            <div class="col-12 col-lg-6 my-lg-auto ps-2 mt-2 ps-xl-5">
                <form class="input-group" role="search" @submit="submitForm($event)">
                    <input class="form-control search-input border-0 search-input" type="text" v-model="suscribeEmail"
                        :placeholder="$t('Enter your email')" />
                    <div class="bg-white search-btn-wrapper">
                        <button type="submit" class="btn btn-dark rounded-pill border-3 border-white">
                            {{ $t('Subscribe') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

<style lang="scss" scoped>
.newsletter {
    background-image: url("/assets/images/website/newsletter-bg.svg");
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
    color: white;

    .search-input {
        border-top-left-radius: 50px;
        border-bottom-left-radius: 50px;

        &::placeholder {
            color: #868686;
        }
    }

    .search-btn-wrapper {
        border-top-right-radius: 50px;
        border-bottom-right-radius: 50px;

        button {
            padding-left: 30px;
            padding-right: 30px;
        }
    }
}
</style>

<script setup>

import { ref } from "vue";
const suscribeEmail = ref("");
import Swal from "sweetalert2";
import axios from "axios";
import { useI18n } from "vue-i18n";
const { t } = useI18n();


const submitForm = (e) => {
    e.preventDefault();


    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    if (!emailRegex.test(suscribeEmail.value)) {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "error",
            title: "Invalid email address"
        });
        return;
    }

    axios.post("/newslatter/subscribe", {
        email: suscribeEmail.value
    })
    .then((response) => {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "success",
            title: t("Thank you for subscribing to our newsletter")
        });
    }).catch((error) => {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "error",
            title: `${error.response.data.message}`
        });
    })
    suscribeEmail.value = "";
};

</script>
