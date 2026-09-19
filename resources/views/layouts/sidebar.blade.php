<style>
    .countMessage {
        position: absolute;
        top: -5px;
        left: 15px;
        width: 15px;
        height: 15px;
        border-radius: 50px;
        background: #ff0000;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff;
        font-size: 8px;
        text-indent: 0;
    }
</style>

<!--Sidebar-Menu-Section-->
<div class="app-sidebar sidebar-shadow">
    <div class="scrollbar-sidebar">

        <div class="branding-logo">
            <a href="/" target="_blank"><img src="{{ $app_setting['logo'] }}" alt="logo">
            </a>
        </div>


        <div class="branding-logo-forMobile mb-4">
            <a href="/" target="_blank"><img src="{{ $app_setting['logo'] }}" alt=""></a>
        </div>
        <div class="app-sidebar-inner border-top">
            <ul class="vertical-nav-menu">

                {{-- dashboard start --}}
                <li class="menu-divider">
                    <span class="menu-title">{{ __('Dashboard') }}</span>
                </li>

                @if (Auth::user()->is_admin || Auth::user()->hasRole('admin'))
                    <li>
                        <a class="menu {{ request()->is('admin') ? 'active' : '' }}" href="/admin">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/home-roof.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Dashboard') }}
                            </span>
                        </a>
                    </li>
                @elseif(Auth::user()->hasRole('instructor'))
                    <li>
                        <a class="menu {{ request()->is('admin') ? 'active' : '' }}" href="/admin/dashboard">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/home-roof.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Dashboard') }}
                            </span>
                        </a>
                    </li>
                @endif
                {{-- dashboard start --}}

                @if (Auth::user()->is_admin || Auth::user()->hasRole('admin') || Auth::user()->hasRole('instructor'))
                    <li class="menu-divider">
                        <span class="menu-title">{{ __('Editorial') }}</span>
                    </li>
                    <li>
                        <a class="menu {{ request()->is('admin/editorial*') ? 'active' : '' }}" href="{{ route('admin.editorial') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/book-open-text.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Editorial Desk') }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a class="menu {{ request()->routeIs('admin.journal.process-flow') ? 'active' : '' }}" href="{{ route('admin.journal.process-flow') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/book-open-text.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Journal Process Flow') }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a class="menu {{ request()->routeIs('admin.journal.process-flow.users.*') ? 'active' : '' }}" href="{{ route('admin.journal.process-flow.users.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/book-open-text.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Journal User Process Monitor') }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a class="menu {{ request()->is('admin/academic-reviewers*') ? 'active' : '' }}" href="{{ route('academic-reviewers.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/book-open-text.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Academic & Reviewer Management') }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a class="menu {{ request()->is('journal/membership-verifications*') ? 'active' : '' }}" href="{{ route('admin.membership-verifications.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/book-open-text.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Membership Verification') }}
                            </span>
                        </a>
                    </li>
                @endif
                
                {{-- course start --}}

                @canany(['category.index', 'course.index', 'chapter.index'])
                    <li class="menu-divider">
                        <span class="menu-title">{{ __('Course') }}</span>
                    </li>
                    <li>
                        <a class="menu {{ request()->is('admin/category*') || request()->is('admin/course*') || request()->is('admin/chapter*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" data-bs-target="#ordersCourse" href="javascript:void(0);">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/book-open-text.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Course Management') }}
                            </span>
                            <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                                class="downIcon" />
                        </a>
                        <div class="collapse dropdownMenuCollapse {{ request()->is('admin/category*', 'admin/course*', 'admin/chapter*') ? 'show' : '' }}"
                            id="ordersCourse">
                            <div class="listBar">
                                @can('category.index')
                                    <a href="{{ route('category.index') }}"
                                        class="subMenu hasCount {{ request()->is('admin/category*') ? 'active' : '' }}">
                                        {{ __('Category') }}
                                    </a>
                                @endcan
                                @can('course.index')
                                    <a href="{{ route('course.index') }}"
                                        class="subMenu hasCount {{ request()->is('admin/course*') ? 'active' : '' }}">
                                        {{ __('Course') }}
                                    </a>
                                @endcan
                                @can('chapter.index')
                                    <a href="{{ route('chapter.select_course') }}"
                                        class="subMenu hasCount {{ request()->is('admin/chapter*') ? 'active' : '' }}">
                                        {{ __('Chapter') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </li>
                @endcanany
                {{-- course end --}}

                {{-- exam start --}}
                @canany(['exam.select_course', 'quiz.select_course'])
                    <li>
                        <a class="menu {{ request()->is('admin/exam*') || request()->is('admin/quiz*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" data-bs-target="#ordersExam" href="javascript:void(0);">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/pencil-paper.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Exam Management') }}
                            </span>
                            <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                                class="downIcon" />
                        </a>
                        <div class="collapse dropdownMenuCollapse {{ request()->is('admin/exam*', 'admin/quiz*') ? 'show' : '' }}"
                            id="ordersExam">
                            <div class="listBar">
                                @can('exam.select_course')
                                    <a href="{{ route('exam.select_course') }}"
                                        class="subMenu hasCount {{ request()->is('admin/exam*') ? 'active' : '' }}">
                                        {{ __('Exam') }}
                                    </a>
                                @endcan
                                @can('quiz.select_course')
                                    <a href="{{ route('quiz.select_course') }}"
                                        class="subMenu hasCount {{ request()->is('admin/quiz*') ? 'active' : '' }}">
                                        {{ __('Quiz') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </li>
                @endcanany
                {{-- exam end --}}

                {{-- coupon start --}}
                @canany(['coupon.index', 'coupon.create'])
                    <li>
                        <a class="menu {{ request()->is('admin/coupon*') ? 'active' : '' }}" data-bs-toggle="collapse"
                            data-bs-target="#ordersCoupon" href="javascript:void(0);">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/coupon-percent.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Coupon Management') }}
                            </span>
                            <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                                class="downIcon" />
                        </a>
                        <div class="collapse dropdownMenuCollapse {{ request()->is('admin/coupon*') ? 'show' : '' }}"
                            id="ordersCoupon">
                            <div class="listBar">
                                @can('coupon.index')
                                    <a href="{{ route('coupon.index', 2) }}"
                                        class="subMenu hasCount {{ request()->is('admin/coupon/list*') ? 'active' : '' }}">
                                        {{ __('Coupon List') }}
                                    </a>
                                @endcan
                                @can('coupon.create')
                                    <a href="{{ route('coupon.create', 3) }}"
                                        class="subMenu hasCount {{ request()->is('admin/coupon/create*') ? 'active' : '' }}">
                                        {{ __('New Coupon') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </li>
                @endcanany
                {{-- coupon end --}}

                @canany(['user.index', 'enrollment.index', 'review.index'])
                    <li class="menu-divider">
                        <span class="menu-title">{{ __('Student') }}</span>
                    </li>
                @endcanany

                {{-- enrollment start --}}
                @canany(['enrollment.index', 'review.index'])
                    <li>
                        <a class="menu {{ request()->is('admin/enrollment*') || request()->is('admin/review*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" data-bs-target="#ordersEnrollmentReview" href="javascript:void(0);">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/students.svg') }}" alt="icon"
                                    loading="lazy" />
                                {{ __('Enrollment Management') }}
                            </span>
                            <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                                class="downIcon" />
                        </a>
                        <div class="collapse dropdownMenuCollapse {{ request()->is('admin/enrollment*') || request()->is('admin/review*') ? 'show' : '' }}"
                            id="ordersEnrollmentReview">
                            <div class="listBar">
                                @can('enrollment.index')
                                    <a href="{{ route('enrollment.index') }}"
                                        class="subMenu hasCount {{ request()->is('admin/enrollment*') ? 'active' : '' }}">
                                        {{ __('Enrollment') }}
                                    </a>
                                @endcan
                                @can('review.index')
                                    <a href="{{ route('review.index') }}"
                                        class="subMenu hasCount {{ request()->is('admin/review*') ? 'active' : '' }}">
                                        {{ __('Review') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </li>
                @endcanany
                {{-- enrollment end --}}

                {{-- student start --}}
                @canany(['user.index'])
                    <li>
                        <a class="menu {{ request()->is('admin/user*') != request()->is('admin/user/edit/*') ? 'active' : '' }}"
                            href="{{ route('user.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/users-group.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Students Management') }}
                            </span>
                        </a>
                    </li>
                @endcanany
                {{-- student end --}}


                {{-- instructor start --}}
                @canany(['instructor.index', 'instructor.create'])
                    <li class="menu-divider">
                        <span class="menu-title">{{ __('Instructor') }}</span>
                    </li>
                    <li>
                        <a class="menu {{ request()->is('admin/instructor*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" data-bs-target="#ordersInstructor" href="javascript:void(0);">
                            <span>
                                <img style="text-primary" class="menu-icon"
                                    src="{{ asset('assets/images/menu/teacher 01.svg') }}" alt="icon"
                                    loading="lazy" />
                                {{ __('Instructor Management') }}
                            </span>
                            <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                                class="downIcon" />
                        </a>
                        <div class="collapse dropdownMenuCollapse {{ request()->is('admin/instructor*') ? 'show' : '' }}"
                            id="ordersInstructor">
                            <div class="listBar">
                                @can('instructor.index')
                                    <a href="{{ route('instructor.index', 2) }}"
                                        class="subMenu hasCount {{ request()->is('admin/instructor/list*') || request()->is('admin/instructor/edit*') ? 'active' : '' }}">
                                        {{ __('Instructor List') }}
                                    </a>
                                @endcan
                                @can('instructor.create')
                                    <a href="{{ route('instructor.create', 1) }}"
                                        class="subMenu hasCount {{ request()->is('admin/instructor/create') ? 'active' : '' }}">
                                        {{ __('New Instructor') }}
                                    </a>
                                @endcan
                                @can('instructor.featured')
                                    <a href="{{ route('instructor.featured', 3) }}"
                                        class="subMenu hasCount {{ request()->is('admin/instructor/featured*') ? 'active' : '' }}">
                                        {{ __('Featured Instructors') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </li>
                @endcanany
                {{-- instructor end --}}

                @canany(['transaction.index', 'payment_gateway.index'])
                    <li class="menu-divider">
                        <span class="menu-title">{{ __('Payment') }}</span>
                    </li>
                @endcanany

                {{-- trsaction start --}}
                @canany(['transaction.index'])
                    <li>
                        <a class="menu {{ request()->is('admin/transaction*') ? 'active' : '' }}"
                            href="{{ route('transaction.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/invoice.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Transaction') }}
                            </span>
                        </a>
                    </li>
                @endcanany
                {{-- trsaction end --}}

                {{-- payment gateway start --}}
                @can('payment_gateway.index')
                    <li>
                        <a class="menu {{ request()->is('admin/payment_gateway*') ? 'active' : '' }}"
                            href="{{ route('payment_gateway.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/user-change.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Payment Method') }}
                            </span>
                        </a>
                    </li>
                @endcan



                {{-- payment gateway end --}}

                <li class="menu-divider">
                    <span class="menu-title">{{ __('Query & Profile Management') }}</span>
                </li>

                {{-- testimonial start --}}
                @can('testimonial.index')
                    <li>
                        <a class="menu {{ request()->is('admin/testimonial*') ? 'active' : '' }}"
                            href="{{ route('testimonial.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/testimonial.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Testimonial') }}
                            </span>
                        </a>
                    </li>
                @endcan

                {{-- testimonial end --}}

                {{-- newsletter start --}}
                @can('newslatter.index')
                    <li>
                        <a class="menu {{ request()->is('admin/newslatter*') ? 'active' : '' }}"
                            href="{{ route('newslatter.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/subscription.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Newslatter Subscribers') }}
                            </span>
                        </a>
                    </li>
                @endcan

                {{-- newsletter end --}}

                {{-- Notification start --}}
                @canany(['notification.index'])
                    <li>
                        <a class="menu {{ request()->is('admin/notification/*') ? 'active' : '' }}"
                            data-bs-toggle="collapse" data-bs-target="#notificationManagement" href="javascript:void(0);">
                            <span>
                                <img style="text-primary" class="menu-icon"
                                    src="{{ asset('assets/images/menu/bell-on.svg') }}" alt="icon" loading="lazy" />
                                {{ __('Notification Management') }}
                            </span>
                            <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                                class="downIcon" />
                        </a>
                        <div class="collapse dropdownMenuCollapse {{ request()->is('admin/notification*') ? 'show' : '' }}"
                            id="notificationManagement">
                            <div class="listBar">
                                @can('notification.index')
                                    <a href="{{ route('notification.index') }}"
                                        class="subMenu hasCount {{ request()->is('admin/notification/list*') ? 'active' : '' }}">
                                        {{ __('Pre-defined Notification') }}
                                    </a>
                                @endcan
                                <a href="{{ route('notification.custom.index', ['user_scope_filter' => 'all']) }}"
                                    class="subMenu hasCount {{ request()->is('admin/notification/custom/*') ? 'active' : '' }}">
                                    {{ __('Custom Notification') }}
                                </a>
                            </div>
                        </div>
                    </li>
                @endcanany
                {{-- notification end --}}

                @php
                    $count = 0;
                    if (auth()->user()->is_admin || auth()->user()->hasRole('admin')) {
                        $count = \App\Models\ContactMessage::where('state', 0)->count();
                    }
                @endphp

                {{-- contact us start --}}
                @can('contact.index')
                    <li>
                        <a class="menu {{ request()->is('admin/contact*') ? 'active' : '' }}"
                            href="{{ route('contact.index') }}">
                            <span class="position-relative">
                                <img class="menu-icon" src="{{ asset('assets/images/menu/message.svg') }}"
                                    alt="icon" loading="lazy" />
                                <div class="countMessage">
                                    {{ $count }}
                                </div>
                                {{ __('Messages') }}
                            </span>
                        </a>
                    </li>
                @endcan
                {{-- contact us end --}}

                {{-- super admin start --}}
                @canany('admin.index')
                    <li>
                        <a class="menu {{ request()->is('admin/root*') ? 'active' : '' }}"
                            href="{{ route('admin.index') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/user-settings.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Super Admin') }}
                            </span>
                        </a>
                    </li>
                @endcanany
                {{-- super admin end --}}


                {{-- profile start --}}
                @canany(['admin.profile'])
                    <li>
                        <a class="menu {{ request()->is('admin/profile*') || request()->is('admin/user/edit/*') ? 'active' : '' }}"
                            href="{{ route('admin.profile') }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/icon/user-square.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Profile') }}
                            </span>
                        </a>
                    </li>
                @endcanany
                {{-- profile End --}}

                <li class="menu-divider">
                    <span class="menu-title">{{ __('Academic Identity') }}</span>
                </li>
                <li>
                    <a class="menu {{ request()->is('journal/membership-applications*') ? 'active' : '' }}"
                        href="{{ route('membership-applications.index') }}">
                        <span>
                            <img class="menu-icon" src="{{ asset('assets/images/icon/user-square.svg') }}"
                                alt="icon" loading="lazy" />
                            {{ __('Journal Applications') }}
                        </span>
                    </a>
                </li>

                {{-- Report start --}}
                @if (!auth()->user()->is_admin && !auth()->user()->hasRole('admin') && auth()->user()->hasRole('instructor'))
                    @canany(['report.index'])
                        <li>
                            <a class="menu {{ request()->is('admin/report/*') ? 'active' : '' }}"
                                href="{{ route('report.index', ['filter_type' => 'all', 'daterange' => now()->format('Y-m-d') . '_' . now()->format('Y-m-d')]) }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/icon/chart-pie.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Report') }}
                            </span>
                            </a>
                        </li>
                    @endcanany
                @elseif(auth()->user()->is_admin || auth()->user()->hasRole('admin'))
                    @canany(['report.index'])
                        <li>
                            <a class="menu {{ request()->is('admin/report/*') ? 'active' : '' }}"
                                href="{{ route('report.index', ['filter_type' => 'all', 'daterange' => now()->format('Y-m-d') . '_' . now()->format('Y-m-d')]) }}">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/icon/chart-pie.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Report') }}
                            </span>
                            </a>
                        </li>
                    @endcanany
                @endif
                {{-- Report End --}}

                {{-- general setting start --}}
                <li class="menu-divider">
                    <span class="menu-title">{{ __('General Setting') }}</span>
                </li>

                <li>
                    <a class="menu {{ request()->is('admin/setting*') || request()->is('admin/certificate*') || request()->is('admin/permission&role*') || request()->is('admin/language/*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" data-bs-target="#settingManagement" href="javascript:void(0);">
                        <span>
                            <img style="text-primary" class="menu-icon"
                                src="{{ asset('assets/images/menu/settings.svg') }}" alt="icon"
                                loading="lazy" />
                            {{ __('Settings Management') }}
                        </span>
                        <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                            class="downIcon" />
                    </a>
                    <div class="collapse dropdownMenuCollapse {{ request()->is('admin/setting*') || request()->is('admin/certificate*') || request()->is('admin/permission&role*') || request()->is('admin/language/*') ? 'show' : '' }}"
                        id="settingManagement">
                        <div class="listBar">
                            @can('setting.index')
                                <a href="{{ route('setting.index') }}"
                                    class="subMenu hasCount {{ request()->is('admin/setting*') ? 'active' : '' }}">
                                    {{ __('General Settings') }}
                                </a>
                            @endcan
                            @can('certificate.index')
                                <a href="{{ route('certificate.index') }}"
                                    class="subMenu hasCount {{ request()->is('admin/certificate*') ? 'active' : '' }}">
                                    {{ __('Certificate Configaration') }}
                                </a>
                            @endcan
                            @can('role.index')
                                <a href="{{ route('role.index') }}"
                                    class="subMenu hasCount {{ request()->is('admin/permission&role*') ? 'active' : '' }}">
                                    {{ __('Role & Permission') }}
                                </a>
                            @endcan
                            <a href="{{ route('language.index') }}"
                                class="subMenu hasCount {{ request()->is('admin/language/*') ? 'active' : '' }}">
                                {{ __('Language') }}
                            </a>
                        </div>
                    </div>
                </li>

                {{-- general setting end --}}

                {{-- legal page start --}}
                <li class="menu-divider">
                    <span class="menu-title">{{ __('Legal Pages') }}</span>
                </li>
                @canany(['page.index'])
                    <li>
                        <a class="menu {{ request()->is('admin/page*') ? 'active' : '' }}" data-bs-toggle="collapse"
                            data-bs-target="#ordersLegal" href="javascript:void(0);">
                            <span>
                                <img class="menu-icon" src="{{ asset('assets/images/menu/file-text-shield.svg') }}"
                                    alt="icon" loading="lazy" />
                                {{ __('Page Management') }}
                            </span>
                            <img src="{{ asset('assets/images/menu/angle-down-small.svg') }}" alt="icon"
                                class="downIcon" />
                        </a>
                        <div class="collapse dropdownMenuCollapse {{ request()->is('admin/page*') ? 'show' : '' }}"
                            id="ordersLegal">
                            <div class="listBar">
                                <a href="{{ route('page.edit', 4) }}"
                                    class="subMenu hasCount {{ request()->is('admin/page/edit/4*') ? 'active' : '' }}">
                                    {{ __('About Us') }}
                                </a>
                                <a href="{{ route('page.edit', 5) }}"
                                    class="subMenu hasCount {{ request()->is('admin/page/edit/5*') ? 'active' : '' }}">
                                    {{ __('Contact Us') }}
                                </a>
                                <a href="{{ route('page.edit', 6) }}"
                                    class="subMenu hasCount {{ request()->is('admin/page/edit/6*') ? 'active' : '' }}">
                                    {{ __('FAQ') }}
                                </a>
                                <a href="{{ route('page.edit', 1) }}"
                                    class="subMenu hasCount {{ request()->is('admin/page/edit/1*') ? 'active' : '' }}">
                                    {{ __('Privacy Policy') }}
                                </a>
                                <a href="{{ route('page.edit', 2) }}"
                                    class="subMenu hasCount {{ request()->is('admin/page/edit/2*') ? 'active' : '' }}">
                                    {{ __('Terms & Conditions') }}
                                </a>
                                <a href="{{ route('page.edit', 3) }}"
                                    class="subMenu hasCount {{ request()->is('admin/page/edit/3') ? 'active' : '' }}">
                                    {{ __('Refund Policy') }}
                                </a>
                            </div>
                        </div>
                    </li>
                @endcanany

                {{-- legal page end --}}


                {{-- logout start --}}
                <li class="menu-divider">
                    <span class="menu-title">{{ __('Sign Out') }}</span>
                </li>
                <li>
                    <a class="menu" href="{{ route('admin.logout') }}">
                        <span class="text-danger">
                            <img class="menu-icon" src="{{ asset('assets/images/menu/log-out.svg') }}"
                                alt="icon" loading="lazy" />
                            {{ __('Logout Account') }}
                        </span>
                    </a>
                </li>
                {{-- logout end --}}


                @if (Auth::user()->is_admin || Auth::user()->hasRole('admin'))
                    <div class="sideBarfooter">
                        <a href="{{ route('setting.index') }}" class="fullbtn"><i class="fa-solid fa-gear"></i></a>
                        <button type="button" class="fullbtn hite-icon" onclick="toggleFullScreen(document.body)"><i
                                class="fa-solid fa-expand"></i></button>

                        <a href="{{ route('cache.clear') }}" class="fullbtn hite-icon" data-bs-toggle="tooltip"
                            data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                            data-bs-title="{{ __('Website Cache Clear') }}"><i class="bi bi-radioactive"></i></a>

                        <a href="{{ route('admin.logout') }}" class="fullbtn hite-icon"><i
                                class="fa-solid fa-power-off"></i></a>
                    </div>
                @endif

        </div>
    </div>
</div>
<!-- End-Sidebar-Menu-Section -->
