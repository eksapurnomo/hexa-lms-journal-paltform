<template>
    <div class="row">
        <div class="col-lg-5">
            <img :src="'/assets/images/website/about.png'" class="w-100" />
        </div>
        <div class="col-1"></div>
        <div class="col-lg-6 my-auto">
            <h2 class="fw-bold mt-5 mt-lg-0 mb-4">
                {{ $t('Why Students Choose Us to Enhance Their Knowledge') }}
            </h2>
            <p class="mb-4">
                {{ $t('Workplace development often takes a back seat to business demands, but 70% of learners believe gaining new skills is essential') }}.
            </p>
            <ul class="list-unstyled">
                <li class="mb-3">
                    <i
                        class="bi bi-check text-primary fs-5 rounded-circle me-1 me-md-3 check-icon"
                    ></i>
                    <span class="lh-lg"
                        >{{ $t('Access to high-quality courses for free') }}</span
                    >
                </li>
                <li class="mb-3">
                    <i
                        class="bi bi-check text-primary fs-5 rounded-circle me-1 me-md-3 check-icon"
                    ></i>
                    <span class="lh-lg"
                        >{{ $t('Effortless course enrollment process') }}</span
                    >
                </li>
                <li>
                    <i
                        class="bi bi-check text-primary fs-5 rounded-circle me-1 me-md-3 check-icon"
                    ></i>
                    <span class="lh-lg"
                        >{{ $t('Certificates awarded upon course completion') }}</span
                    >
                </li>
            </ul>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.check-icon {
    background-color: #9e4aed46;
    padding: 0.5px 3px;
}
</style>
