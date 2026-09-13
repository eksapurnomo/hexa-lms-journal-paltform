<template>
    <section class="my-2">
        <span class="d-block mb-5">{{ $t('Home') }}/{{ $t('Profile') }}</span>
        <form @submit.prevent="updateProfile">
            <div class="row flex-column-reverse flex-md-row mb-4">
                <div class="col-md-6">
                    <div class="theme-shadow p-4 rounded">
                        <div class="mb-3">
                            <label class="form-label fs-5">{{ $t('Name') }}</label>
                            <input type="text" class="form-control bg-light border-0" :placeholder="$t('Full Name')"
                                v-model="form.name" />
                            <p v-if="errors?.name" class="my-2 text-danger">
                                {{ errors?.name[0] }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-5">{{ $t('Phone') }}</label>
                            <input type="tel" class="form-control bg-light border-0" placeholder="+8801XXXXXXXXX"
                                v-model="form.phone" />
                            <p v-if="errors?.phone" class="my-2 text-danger">
                                {{ errors?.phone[0] }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-5">{{ $t('Email') }}</label>
                            <input autocomplete="username" type="text" class="form-control bg-light border-0"
                                placeholder="user@example.com" v-model="form.email" />
                            <p v-if="errors?.email" class="my-2 text-danger">
                                {{ errors?.email[0] }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-5">{{ $t('Current Password') }}</label>
                            <input autocomplete="current-password" type="password"
                                class="form-control bg-light border-0"
                                :placeholder="$t('Enter current password to change')" v-model="form.current_password" />
                            <p v-if="current_passwordError" class="my-2 text-danger">
                                {{ current_passwordError }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-5">{{ $t('Password') }}</label>
                            <input autocomplete="new-password" type="password" class="form-control bg-light border-0"
                                :placeholder="$t('Enter new password to change')" v-model="form.password" />
                            <p v-if="errors?.password" class="my-2 text-danger">
                                {{ errors?.password[0] }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-5">{{ $t('Confirm Password') }}</label>
                            <input autocomplete="new-password" type="password" class="form-control bg-light border-0"
                                :placeholder="$t('Confirm new password')" v-model="form.password_confirmation" />
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-5 mb-md-0">
                    <div class="theme-shadow p-5 rounded d-flex h-100 align-items-center">
                        <div class="w-100">
                            <img :src="profilePhoto
                                ? profilePhoto
                                : authStore.userData.profile_picture
                                " alt="Profile picture" height="250px" width="250px"
                                class="rounded-circle mx-auto object-fit-cover d-block border border-3 border-primary mb-4" />
                            <input id="upload-pp" type="file" class="d-none" @change="changeProfilePhoto" />
                            <label for="upload-pp" class="upload-pp-btn btn btn-outline-primary border-2 w-100">
                                {{ $t('Upload image') }}
                            </label>
                            <p v-if="errors?.profile_picture" class="my-2 text-danger">
                                {{ errors?.profile_picture[0] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">{{ $t('Save') }}</button>
        </form>
    </section>
</template>

<style lang="scss" scoped>
.upload-pp-btn {
    border-style: dashed;
}
</style>

<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref, watch } from "vue";
import Swal from "sweetalert2";

// validation errors
let errors = ref("");

const authStore = useAuthStore();
let current_passwordError = ref("");

// Initialize form data
const form = ref({
    name: authStore.userData.name,
    phone: authStore.userData.phone,
    email: authStore.userData.email,
    current_password: null,
    password: null,
    password_confirmation: null,
    profile_picture: null,
});

watch(
    () => form.value.name,
    (newName) => {
        if (newName) {
            errors.value.name = "";
        }
    }
);
watch(
    () => form.value.email,
    (newEmail) => {
        if (newEmail) {
            errors.value.email = "";
        }
    }
);
watch(
    () => form.value.phone,
    (newPhone) => {
        if (newPhone) {
            errors.value.phone = "";
        }
    }
);
watch(
    () => form.value.password,
    (newPassword) => {
        if (newPassword) {
            errors.value.password = "";
        }
    }
);
watch(
    () => form.value.current_password,
    (newPassword) => {
        if (newPassword) {
            current_passwordError.value = "";
        }
    }
);

// Handle form submission
const updateProfile = async () => {
    try {
        let formData = new FormData();
        formData.append("name", form.value.name);
        formData.append("phone", form.value.phone);

        if (form.value.profile_picture)
            formData.append("profile_picture", form.value.profile_picture);
        if (
            form.value.current_password ||
            form.value.password ||
            form.value.password_confirmation
        ) {
            formData.append("current_password", form.value.current_password);
            formData.append("password", form.value.password);
            formData.append(
                "password_confirmation",
                form.value.password_confirmation
            );
        }

        if (form.value.email) formData.append("email", form.value.email);

        // API request to update the profile
        const response = await axios.post(`/profile/update`, formData, {
            headers: {
                Accept: "application/json",
                Authorization: "Bearer " + authStore.authToken,
            },
        });

        // Update user data in state
        authStore.setAuthData(
            response.data.data.token,
            response.data.data.user
        );

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Profile updated successfully",
            showConfirmButton: false,
            timer: 1500,
        });
    } catch (error) {
        if (error?.response?.data?.data?.current_password_error) {
            current_passwordError.value =
                error?.response?.data?.data?.current_password_error;
        }

        errors.value = error?.response?.data?.errors;


        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response.data.message,
            showConfirmButton: false,
            timer: 1500,
        });

        if (error?.response?.data?.access_denied) {
            Swal.fire({
                icon: "error",
                title: "Access Denied",
                text: error?.response?.data?.access_denied,
                showConfirmButton: false,
                timer: 3500,
            });
        }
    }
};

const profilePhoto = ref(false);
let changeProfile = ref(false);

const changeProfilePhoto = (event) => {
    profilePhoto.value = URL.createObjectURL(event.target.files[0]);
    form.value.profile_picture = event.target.files[0];
    changeProfile.value = true;
};
</script>
