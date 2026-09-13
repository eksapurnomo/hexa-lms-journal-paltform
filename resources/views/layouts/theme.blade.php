<!-- Start-Theme-Section -->
<div class="ui-theme-settings">
    <button type="button" id="TooltipDemo" class="btn-open-options btn ">
        <i class="fa fa-cog fa-w-16 fa-spin fa-2x"></i>
    </button>
    <div class="theme-settings-inner">
        <div class="scrollbar-container">
            <div class="theme-settings-options-wrapper">
                <h3 class="themeoptions-heading">Theme Customizer</h3>
                <div class="p-3">
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="themeoptions-heading mb-3 borderCercle d-flex justify-content-between">
                                <div>
                                    Mode
                                </div>
                                <button type="button" id="resetModeBtn"
                                    class="btn btn-outline-dark btn-sm ressetButton">
                                    Restore Default
                                </button>
                            </div>
                            <div class="theme-box">
                                <div class="change-mode " id="defoltChangeMode"
                                    onclick="setThemeMode('app-theme-white')">
                                    <div class="change-mode-icon">
                                        <img src="assets/images/icon/light-icon.svg" alt="">
                                    </div>
                                    <span class="mode-name">Light</span>
                                    <div class="chack-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                </div>
                                <div class="change-mode" onclick="setThemeMode('app-theme-dark')" id="themeDark">
                                    <div class="change-mode-icon">
                                        <img src="assets/images/icon/Dark.svg" alt="">
                                    </div>
                                    <span class="mode-name">Dark</span>
                                    <div class="chack-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                </div>
                                <div class="change-mode" onclick="setThemeMode('app-theme-gradien')" id="themeGradien">
                                    <div class="change-mode-icon">
                                        <img src="assets/images/icon/Gradient-icon.png" alt="">
                                    </div>
                                    <span class="mode-name">Gradien</span>
                                    <div class="chack-icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="p-3">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <h5 class="pb-3 teameTa">Choose Color Scheme
                            </h5>
                            <div class="theme-settings-switches">
                                <div class="theme-box">
                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="colorDefault" data-class="" onclick="storeColorScheme('')">
                                        <div class="theme-color-holder-color bg-light"></div>
                                    </div>

                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-primary" data-class="bg-primary header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-primary')">
                                        <div class="theme-color-holder-color bg-primary"></div>
                                    </div>

                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-midnight-bloom"
                                        data-class="bg-midnight-bloom header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-midnight-bloom')">
                                        <div class="theme-color-holder-color bg-midnight-bloom"></div>
                                    </div>


                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-amy-crisp" data-class="bg-amy-crisp header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-amy-crisp')">
                                        <div class="theme-color-holder-color bg-amy-crisp"></div>
                                    </div>

                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-plum-plate"
                                        data-class="bg-plum-plate header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-plum-plate')">
                                        <div class="theme-color-holder-color bg-plum-plate"></div>
                                    </div>

                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-slick-carbon"
                                        data-class="bg-slick-carbon header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-slick-carbon')">
                                        <div class="theme-color-holder-color bg-slick-carbon"></div>
                                    </div>

                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-arielle-smile"
                                        data-class="bg-arielle-smile header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-arielle-smile')">
                                        <div class="theme-color-holder-color bg-arielle-smile"></div>
                                    </div>

                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-alternate" data-class="bg-alternate header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-alternate')">
                                        <div class="theme-color-holder-color bg-alternate"></div>
                                    </div>


                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-grow-early"
                                        data-class="bg-grow-early header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-grow-early')">
                                        <div class="theme-color-holder-color bg-grow-early"></div>
                                    </div>

                                    <div class="theme-color-holder switch-header-cs-class switch-sidebar-cs-class"
                                        id="bg-love-kiss" data-class="bg-love-kiss header-text-light sidebar-text-light"
                                        onclick="storeColorScheme('bg-love-kiss')">
                                        <div class="theme-color-holder-color bg-love-kiss"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="themeoptions-heading mt-3 borderCercle d-flex justify-content-between">
                                <div>Sidebar Options</div>
                                <button type="button"
                                    class="btn btn-outline-dark btn-sm ressetButton
                                switch-header-cs-class switch-sidebar-cs-class"
                                    data-class="" id="resetColor">
                                    Restore Default
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="p-3">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <div class="widget-content p-0">
                                <div class="widget-content-wrapper">
                                    <div class="switch has-switch switch-container-class" data-class="fixed-footer">
                                        <input type="checkbox" class="form-check-input" data-toggle="toggle"
                                            id="footerCheck" data-onstyle="success" onclick="toggleFooter()">
                                    </div>
                                    <div class="widget-content-left ms-3">
                                        <div class="widget-heading">Fixed Footer</div>
                                        <div class="widget-subheading">Makes the app footer bottom fixed, always
                                            visible!
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End-Theme-Section -->
