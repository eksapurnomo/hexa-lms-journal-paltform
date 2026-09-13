<template>
    <section class="row">
        <section class="col-lg-6">
            <span class="d-block text-primary fw-bold mb-2">{{ $t('Find Your Answers') }}</span>
            <h3 class="fw-bold">{{ $t('Have any thought') }}?</h3>
            <h3 class="fw-bold mb-4">{{ $t('Look here') }}.</h3>
            <p class="col-9 mb-5">
                {{ $t('Completely plagiarize fully researched collaboration and idea-sharing for covalent') }}.
            </p>
            <router-link to="/page/faq" class="btn rounded-pill fw-bold read-more-btn">{{ $t('Read More') }} <i
                    class="bi bi-arrow-right ms-1"></i></router-link>
        </section>
        <section class="col-lg-6 mt-5 mt-lg-0">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <div class="accordion-item p-2 mb-3">
                    <h2 class="accordion-header" id="flush-headingOne">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            {{ $t('How to delete my account') }}?
                        </button>
                    </h2>
                    <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne"
                        data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            To delete your account permanently,
                             please contact our support team and request account deletion. Once verified,
                              your account and all associated data will be removed from our system. Please note, this action is irreversible.
                        </div>
                    </div>
                </div>
                <div class="accordion-item p-2 mb-3">
                    <h2 class="accordion-header" id="flush-headingTwo">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                            {{ $t('How can I deactivate my account') }}?
                        </button>
                    </h2>
                    <div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo"
                        data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            You can temporarily deactivate your account from the Account Settings page.
                             Simply navigate to the Privacy & Security section and click on <code>“Deactivate Account.”</code>
                             While your account is deactivated, your profile will be hidden, and you won’t receive any notifications.
                        </div>
                    </div>
                </div>
                <div class="accordion-item p-2 mb-3">
                    <h2 class="accordion-header" id="flush-headingThree">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#flush-collapseThree" aria-expanded="false"
                            aria-controls="flush-collapseThree">
                            {{ $t('How can I reactive my account') }}?
                        </button>
                    </h2>
                    <div id="flush-collapseThree" class="accordion-collapse collapse"
                        aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            To reactivate your account, simply log back in using your email and password.
                             Your account will be restored with all your previous data and progress intact.
                              If you face any issues, feel free to reach out to our support team.
                        </div>
                    </div>
                </div>
                <div class="accordion-item p-2 mb-3">
                    <h2 class="accordion-header" id="flush-headingFour">
                        <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#flush-collapseFour" aria-expanded="false"
                            aria-controls="flush-collapseFour">
                            {{ $t('What happens to my course progress if I deactivate my account') }}?
                        </button>
                    </h2>
                    <div id="flush-collapseFour" class="accordion-collapse collapse" aria-labelledby="flush-headingFour"
                        data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            Your course progress is safely stored even if you deactivate your account.
                             Once you reactivate or log back in, you can continue from where you left off without losing any data.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</template>

<style scoped lang="scss">
.read-more-btn {
    border: 1px solid #777;

    &:hover {
        background-color: #9e4aed;
        color: #fff;
    }
}

.accordion-item {
    box-shadow: 0px 12px 48px 0px #0000000d;
    border: 0;
    border-radius: 10px;

    .accordion-header {
        .accordion-button {
            border-radius: 10px !important;
        }
    }
}
</style>
