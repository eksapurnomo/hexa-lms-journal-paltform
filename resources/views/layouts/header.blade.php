<div class="app-header header-shadow">
    <div class="app-header-logo"></div>
    <div class="app-header-mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header-menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="app-header-content">
        <!-- Header-left-Section -->
        <div class="app-header-left">
            <div class="header-pane ">
                <div>
                    <button type="button" class="hamburger close-sidebar-btn hamburger--elastic"
                        data-class="closed-sidebar">
                        <span class="hamburger-box">
                            <span class="hamburger-inner"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <!-- Header-welcome-Section -->
        <div class="app-header-welcome">
            <h3 class="title">{{ __('Welcome Back') }}, {{ auth()->user()?->name ?? 'User Name' }}</h3>
            <p class="subtitle">{{ __('Monitor your business analytics and statistics') }}.</p>
        </div>
        {{-- End-Header-welcome-Section --}}

        <!-- End-Header-Left-section -->

        <!-- Header-Rignt-Section -->
        <div class="app-header-right">
            <!-- Notification Section -->

            <div class="modeButton me-3">
                <button id="modeChange" type="button" class="emailBadge position-relative" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-moon" id="modeIcon"></i>
                </button>
            </div>

            <div class="badgeButtonBox me-4">
                <div class="notifactionIcon">
                    <button type="button" class="emailBadge dropdown-toggle position-relative"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('assets/images/menu/bell-on.svg') }}" alt="notification">
                        <span class="position-absolute notificationCount"
                            id="totalNotify">{{ $notificationMessages->where('is_read', 0)->count() }}</span>
                    </button>
                    <div class="dropdown-menu p-0 emailNotifactionSection">
                        <div class="dropdown-item emailNotifaction">
                            <div class="emailHeader">
                                <h6 class="massTitel">
                                    {{ __('Notifications') }}
                                </h6>
                                <a href="{{ route('notification.read.all') }}" class="text-dark"
                                    style="cursor: pointer">
                                    {{ __('Marks all as read') }}
                                </a>
                            </div>
                            <div class="messege-section" id="notifications">
                                @foreach ($notificationMessages as $notification)
                                    @php
                                        $metadata = json_decode($notification->metadata, true);
                                    @endphp
                                    <a href="{{ $notification->is_read ? 'javascript:void(0)' : route('notification.read', $notification->id) }}"
                                        class="item d-flex gap-2 align-items-center">
                                        <div class="iconBox {{ $notification->is_read ? '' : 'pdfIcon' }}">
                                            <i class="bi bi-chat-left-text-fill"></i>
                                        </div>
                                        <div class="notification w-100 {{ $notification->is_read ? '' : 'unread' }}">
                                            <div class="userName">
                                                <p class="massTitel">
                                                    {{ $notification->notification->heading }}
                                                </p>
                                                <span
                                                    class="time">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div>
                                                <p class="description">{{ $notification->content }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="emailFooter">
                                <a class="massPera text-dark">
                                    {{ __('View All Notifications') }}
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>



            <!-- Language Dropdown -->

            <div class="user-profile-box dropdown me-4">
                <div class="nav-profile-box dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    @php
                        $selectedLang = app()->getLocale();
                    @endphp
                    <div class="lang">
                        <img src="{{ asset('assets/images/menu/Launguage.svg') }}" alt="icon" loading="lazy" />
                        <span>{{ ucfirst($selectedLang ?? __('English')) }}</span>
                        <i class="fa-solid fa-angle-down dropIcon"></i>
                    </div>
                </div>

                @php
                    $languageLabels = [
                        'en' => __('English'),
                        'fr' => __('français'),
                        'bn' => __('বাংলা'),
                        'ar' => __('العربية'),
                    ];
                @endphp

                <div class="dropdown-menu profile-item">
                    @foreach ($languages as $language)
                        <a href="{{ route('change.language', 'language=' . $language->name) }}"
                            class="dropdown-item {{ $language->name == app()->getLocale() ? 'language-active' : '' }}">
                            <i class="fa fa-language mr-3"></i>
                            {{ $languageLabels[$language->title] ?? $language->title }}
                        </a>
                    @endforeach
                </div>
            </div>



            {{-- <div class="notification-icon bg-white p-2 rounded-circle">
                <img src="{{ asset('assets/images/menu/bell-on.svg') }}" alt="notification">
            </div> --}}
            <div class="user-profile-box dropdown">
                <div class="nav-profile-box dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-content text-end">
                        <h4 class="admin-name">{{ auth()->user()?->name ?? 'User Name' }}</h4>
                        <p class="admin-role text-capitalize">
                            {{ auth()->user() ? auth()->user()->getRoleNames()->first() : 'guest' }}
                            <i class="fa-solid fa-angle-down dropIcon"></i>
                        </p>
                    </div>
                    <div class="profile-image">
                        <a href="">
                            <img class="profilepic" src="{{ auth()->user()?->profilePicturePath ?? asset('assets/images/avatars/MD.png') }}" alt="">
                        </a>
                    </div>
                </div>

                <div class="dropdown-menu profile-item">
                    <a href="{{ route('user.edit', auth()->user()?->id) }}" class="dropdown-item">
                        <i class="fa-solid fa-gear me-2"></i>
                        {{ __('Update Profile') }}
                        </a>
                    <button onclick="toggleFullScreen(document.body)" type="button" class="dropdown-item">
                        <i class="fa-solid fa-expand me-2"></i>
                        {{ __('Full Screen') }}
                    </button>
                    <a href="{{ route('admin.logout') }}" class="dropdown-item">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>
                        {{ __('Logout') }}
                    </a>
                </div>
            </div>

        </div>
        <!-- End-Header-Right-Section -->

    </div>
</div>

{{-- dark mode and light mode script --}}
<script>
    const modeIcon = document.getElementById('modeIcon');
    const modeChange = document.getElementById('modeChange');

    modeChange.addEventListener('click', () => {
        if (modeIcon.classList.contains('bi-moon')) {
            modeIcon.classList.remove('bi-moon');
            modeIcon.classList.add('bi-moon-fill');
            setThemeMode('app-theme-dark');
        } else {
            modeIcon.classList.remove('bi-moon-fill');
            modeIcon.classList.add('bi-moon');
            setThemeMode('app-theme-light');
        }
    });
</script>
