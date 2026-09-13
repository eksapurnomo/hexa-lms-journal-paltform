<template>
    <section class="bg-light py-5">
        <section class="container">
            <section class="checkout-wizard bg-white theme-shadow my-4">
                <h3 class="fw-bold px-5 py-4 border-bottom mb-0">{{ $t('Checkout') }}</h3>
                <div class="row">
                    <div :class="course?.is_free
                        ? 'col-12'
                        : 'col-12 col-lg-8 border-end p-4 ps-4 ps-lg-5'
                        ">
                        <div class="border rounded-3 p-2 p-lg-4 mb-4">
                            <div class="row">
                                <div class="col-12 col-lg-4">
                                    <img :src="course?.thumbnail" width="100%" alt="" height="140px"
                                        class="object-fit-cover rounded-3" />
                                </div>
                                <div class="col-12 col-lg-8">
                                    <h5 class="mt-4 mt-lg-0">
                                        {{ course?.title }}
                                    </h5>
                                    <div class="d-flex mt-3 pb-3">
                                        <img :src="course?.instructor
                                            ?.profile_picture
                                            " class="rounded-circle object-fit-cover me-3" height="30px"
                                            width="30px" />
                                        <div>
                                            <span class="d-block">{{
                                                course?.instructor?.name
                                                }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        <strong>{{
                                            course?.average_rating
                                            }}</strong>
                                        <span class="text-muted ms-2">({{
                                            course?.review_count
                                        }}
                                            {{ $t('Reviews') }})</span>
                                        <span class="text-muted mx-1">
                                            <i class="bi bi-dot"></i>
                                        </span>
                                        <span class="text-muted">{{
                                            course?.student_count
                                        }}
                                            {{ $t('Enrolled') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-2 p-lg-4">
                            <h5 class="mb-4">{{ $t('Order Details') }}</h5>
                            <div class="bg-light rounded-3 p-4">
                                <div class="price-breakdown mb-3">
                                    <div class="d-flex justify-content-between mb-4" v-if="course?.price">
                                        <span>{{ $t('Course Price') }}</span>
                                        <strong>{{
                                            masterStore?.masterData
                                                ?.currency_symbol
                                        }}{{
                                                course?.is_free
                                                    ? 0
                                                    : course?.price
                                            }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-4" v-else>
                                        <span>{{ $t('Course Price') }}</span>
                                        <strong>{{
                                            masterStore?.masterData
                                                ?.currency_symbol
                                        }}{{
                                                course?.is_free
                                                    ? 0
                                                    : course?.regular_price
                                            }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-4 text-danger">
                                        <span>{{ $t('Discount') }}</span>
                                        <strong>-{{
                                            masterStore?.masterData
                                                ?.currency_symbol
                                        }}{{ discountAmount }}
                                        </strong>
                                    </div>
                                    <div class="mb-3">
                                        <span v-if="!showCouponInput" @click="showCouponInput = true"
                                            class="text-primary text-decoration-underline cursor-pointer">
                                            {{ $t('Have any coupon') }}?</span>
                                    </div>
                                    <form @submit.prevent="validateCoupon" v-if="!couponApplied && showCouponInput"
                                        class="bg-white d-flex rounded-3 p-4 mb-3">
                                        <input type="text" v-model="couponCode" class="form-control me-3"
                                            placeholder="Enter coupon code" />
                                        <button type="submit" class="btn btn-primary">
                                            {{ $t('Apply') }}
                                        </button>
                                    </form>

                                    <div v-if="couponApplied" class="d-flex justify-content-between mb-3">
                                        <div>
                                            <i class="ri-checkbox-circle-fill text-success me-1"></i>
                                            <span class="me-3">{{ $t('Coupon Applied') }}</span>
                                            <small @click="
                                                couponApplied = false;
                                            showCouponInput = true;
                                            couponCode = null;
                                            " class="text-success bg-lightgreen px-2 py-1 rounded cursor-pointer">{{
                                                couponCode }}
                                                <i class="ri-edit-line"></i></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between flex-wrap">
                                    <span>
                                        <span class="fw-bold me-1">{{ $t('Subtotal') }}</span>
                                        <span>({{ $t('Payable') }})</span>
                                    </span>
                                    <span class="fw-bold">{{
                                        masterStore?.masterData
                                            ?.currency_symbol
                                    }}{{ grandTotal }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-center" v-if="course?.is_free">
                            <button @click="freeEnrollment"
                                class="d-flex justify-content-center gap-2 btn btn-primary rounded-pill fw-bold w-50">
                                Enroll Now
                                <i class="bi bi-arrow-right float-end"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 ps-0 d-flex flex-column" v-if="!course?.is_free">
                        <div class="p-2 pe-2 pe-lg-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold">{{ $t('Payment Method') }}</h5>
                                <small class="bg-lightgreen text-success rounded px-2 py-1 text-center">
                                    <i class="ri-shield-check-fill me-1"></i>
                                    {{ $t('100% Secure Payment') }}
                                </small>
                            </div>

                            <div v-for="gateway in masterStore?.masterData
                                ?.payment_methods" :key="gateway.id">
                                <div @click="
                                    selectedPaymentMethod = gateway.gateway
                                    " :class="'border d-flex justify-content-between align-items-center px-3 mb-3 rounded payment-method mx-auto' +
                                        (selectedPaymentMethod ==
                                            gateway.gateway
                                            ? ' selected'
                                            : '')
                                        ">
                                    <img :src="gateway.logo" class="col-3 py-3" />
                                    <input class="form-check-input" type="radio" :checked="selectedPaymentMethod ==
                                        gateway.gateway
                                        " />
                                </div>
                            </div>
                        </div>

                        <div class="border-top p-4 d-flex align-items-start gap-3">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" required
                                @change="acceptTerms = !acceptTerms" />
                            <label class="form-check-label" for="exampleCheck1">
                                {{ $t('By proceeding, you agree to the') }}
                                <router-link to="/page/terms_and_conditions"
                                    class="text-decoration-none bg-transparent border-0 text-primary">
                                    {{ $t('Terms & Condition') }}</router-link>,<router-link to="/page/privacy_policy"
                                    class="text-decoration-none bg-transparent border-0 text-primary">
                                    {{ $t('Privacy Policy') }}</router-link>
                                {{ $t('and') }}
                                <router-link to="/page/refund_policy"
                                    class="text-decoration-none bg-transparent border-0 text-primary">
                                    {{ $t('Refund Policy') }}</router-link> of {{ masterStore?.masterData?.name }}
                            </label>
                        </div>

                        <div class="mt-auto border-top p-4">
                            <button @click="handleButtonClick" class="btn btn-primary rounded-pill fw-bold w-100">
                                {{ $t('Pay') }} {{ masterStore?.masterData?.currency_symbol
                                }}{{ grandTotal }}
                                <i class="bi bi-arrow-right float-end"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </section>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import Header from "../components/Header.vue";
import { useMasterStore } from "@/stores/master";
import { useAuthStore } from "@/stores/auth";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

const masterStore = useMasterStore();
const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const course_id = route.params.id;
const selectedPaymentMethod = ref("stripe");

const course = ref({});
const discountAmount = ref(0);
const grandTotal = ref(0);
const showCouponInput = ref(false);
const couponCode = ref("");
const couponApplied = ref(false);
let acceptTerms = ref(false);

watch(
    () => discountAmount.value,
    () => {
        if (course.value.is_free) {
            grandTotal.value = 0;
        } else if (course.value.regular_price && !course.value.price) {
            grandTotal.value =
                course.value.regular_price - discountAmount.value;
        } else {
            grandTotal.value = course.value.price - discountAmount.value;
        }
    }
);

const validateCoupon = async () => {
    try {
        const response = await axios.get(`/coupon/validate`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
            params: {
                coupon_code: couponCode.value,
            },
        });

        discountAmount.value = response.data.data.discount;

        couponApplied.value = true;

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            },
        });
        Toast.fire({
            icon: "success",
            title: "Coupon applied successfully",
        });
    } catch (error) {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            },
        });
        Toast.fire({
            icon: "error",
            title: "Oops...",
            text: "Coupon code is invalid",
        });
    }
};

const fetchCourseData = async () => {
    try {
        const response = await axios.get(`/course/show/${course_id}`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
        });
        course.value = response.data.data.course;
        // grandtotal measurement start
        if (course.value.is_free) {
            grandTotal.value = 0;
        } else if (course.value.regular_price && !course.value.price) {
            grandTotal.value =
                course.value.regular_price - discountAmount.value;
        } else {
            grandTotal.value = course.value.price - discountAmount.value;
        }
        // grandtotal measurement end
    } catch (error) {
        console.error("Error fetching course data:", error);
    }
};

const handleButtonClick = () => {
    if (acceptTerms.value && selectedPaymentMethod.value) {
        initiateTransaction();
    } else {
        const Toast = Swal.mixin({
            toast: true,
            position: "bottom-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            },
        });
        Toast.fire({
            icon: "error",
            title: "Oops...",
            text: "Please select a payment method and agree to the terms and conditions",
        });
    }
}

const initiateTransaction = async () => {
    if (course.value.is_free) {
        grandTotal.value = 0;
    } else if (course.value.regular_price && !course.value.price) {
        grandTotal.value = course.value.regular_price - discountAmount.value;
    } else {
        grandTotal.value = course.value.price - discountAmount.value;
    }


    if (selectedPaymentMethod.value == "stripe" || selectedPaymentMethod.value == "paypal" || selectedPaymentMethod.value == "razorpay") {

        if (masterStore?.masterData?.currency != "USD" && grandTotal.value < masterStore?.masterData?.minimum_amount) {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
            });
            Toast.fire({
                icon: "error",
                title: "Oops...",
                text: `Please note that a minimum of grand total of ${masterStore?.masterData?.minimum_amount} is required for transaction.`,
            });
            return;
        }

        if (masterStore?.masterData?.currency == "BDT") {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
            });
            Toast.fire({
                icon: "error",
                title: "Oops...",
                text: `BDT is not supported at the moment`,
            });
            return;
        }
    }

    try {
        const response = await axios.get(`/enroll/${course_id}`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
            params: {
                payment_gateway: selectedPaymentMethod.value,
                coupon_code: couponCode.value,
                total_amount: grandTotal.value,
            },
        });

        openPaymentPopupWindow(response.data.data.payment_webview_url);
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text:
                error.response?.data?.message ||
                "Something went wrong. Please try again.",
        });
    }
};

const freeEnrollment = async () => {
    try {
        const response = await axios.get(`/free/enroll/${course_id}`, {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${authStore.authToken}`,
            },
        });

        if (response?.data?.data?.status == "success") {
            let status = response?.data?.data?.status;
            router.push(
                `/enroll_status?status=${status}&course_id=${course_id}`
            );
        }
    } catch (error) {

        Swal.fire({
            icon: "error",
            title: "Oops...",
            text:
                error.response?.data?.message ||
                "Something went wrong. Please try again.",
        });
    }
};

onMounted(async () => {
    await fetchCourseData();
});

const openPaymentPopupWindow = (url) => {
    console.log(url);

    let winWidth = 700;
    let winHeight = 700;
    let left = screen.width / 2 - winWidth / 2;
    let top = screen.height / 2 - winHeight / 2;

    let options = `popup,resizable,height=${winHeight},width=${winWidth},top=${top},left=${left}`;
    let win = window.open(url, "_blank");
    win.title = "Payment Window Screen - Make Payment";

    const handleWindowClose = (status) => {
        if (win && !win.closed) {
            win.close();
        }
        router.push(
            `/enroll_status?course_id=${course_id}&status=${status}`
        );
    };

    const trackURLChanges = () => {
        try {
            if (!win || win.closed) {
                clearInterval(intervalID);
                handleWindowClose("fail");
                return;
            }
            const pathname = win.location.pathname.replace(/\/payment\/\d+/, "");

            if (pathname.includes("payment/cancel")) {
                clearInterval(intervalID);
                handleWindowClose("cancel");
                win.close();
            } else if (pathname.includes("payment/success")) {
                clearInterval(intervalID);
                handleWindowClose("success");
                win.close();
            } else if (pathname.includes("payment/fail")) {
                clearInterval(intervalID);
                handleWindowClose("fail");
                win.close();
            }
        } catch (e) { }
    };

    const intervalID = setInterval(trackURLChanges, 1000);
    win.focus();

    setTimeout(() => {
        clearInterval(intervalID);
        if (win && !win.closed) {
            handleWindowClose("timeout");
            win.close();
        }
    }, 180000);
};
</script>

<style lang="scss" scoped>
.checkout-wizard {
    border-radius: 2rem;
    box-shadow: 0px 16px 64px 0px #9747ff14;

    .price-breakdown {
        border-bottom: 2px dashed #ccc;
    }

    .payment-method {
        cursor: pointer;

        &:hover {
            border-color: #9e4aed !important;
        }
    }

    .payment-method.selected {
        border-color: #9e4aed !important;
    }
}
</style>
