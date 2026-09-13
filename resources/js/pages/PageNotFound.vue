<template>
    <main class="content">
        <section class="error-section d-flex justify-content-center align-items-center">
            <div class="container text-center">
                <div class="error-card">
                    <i class="ri-error-warning-fill error-icon text-danger"></i>
                    <h1 class="display-1 text-danger fw-bold">{{ $t('Sorry! Page not found') }}</h1>
                    <p class="message">
                        {{ $t('The page you are looking for could not be found. It may have been moved or deleted') }}
                    </p>
                    <router-link to="/" class="btn btn-primary btn-lg mt-4">{{ $t('Go back to the homepage') }}</router-link>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
.error-section {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    text-align: center;
    padding: 30px;
}

.error-card {
    background: transparent;
    padding: 40px;
    border-radius: 12px;
    display: inline-block;
    text-align: center;
}

.error-icon {
    font-size: 6rem;
    margin-bottom: 20px;
}

.message {
    font-size: 1.2rem;
    color: #6c757d;
    margin-bottom: 20px;
}

.btn {
    font-size: 1.2rem;
    padding: 10px 20px;
}
</style>
