<header class="fixed-top">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <!-- Đổi vị trí button toggle lên đầu -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand d-flex align-items-center" href="/">
                <i class="fas fa-graduation-cap me-2"></i>
                Edudex Quiz
            </a>

            <!-- User dropdown cho mobile -->
            @auth
            <div class="d-lg-none">
                <div class="dropdown">
                    <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->accountInfo->avatar 
                            ? asset('storage/avatars/' . Auth::user()->accountInfo->avatar) 
                            : asset('images/default-avatar.jpg') }}" 
                             class="rounded-circle" width="32" height="32">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="fas fa-user me-2"></i> Thông tin cá nhân
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            @endauth

            <!-- Menu chính -->
            <div class="collapse navbar-collapse" id="navbarNav">
                @auth
                <ul class="navbar-nav">
                    @if(Auth::user()->role == 2)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('accounts*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-2"></i> Tài khoản
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('accounts.index') }}">
                                    <i class="fas fa-users me-2"></i> Danh sách tài khoản
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('accounts.create') }}">
                                    <i class="fas fa-user-plus me-2"></i> Thêm tài khoản
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('accounts.tools') }}">
                                    <i class="fas fa-file-excel me-2"></i> Import/Export
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('facilities*') ? 'active' : '' }}" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-building me-2"></i> Cơ sở vật chất
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('facilities.index') }}">
                                    <i class="fas fa-warehouse me-2"></i> Quản lý cơ sở
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('rooms.index') }}">
                                    <i class="fas fa-door-open me-2"></i> Quản lý phòng
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    @if(Auth::user()->role == 1 || Auth::user()->role == 2)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-graduation-cap me-2"></i> Đào tạo
                        </a>
                        <ul class="dropdown-menu">
                            @if(Auth::user()->role == 2)
                            <li>
                                <a class="dropdown-item" href="{{ route('faculties.index') }}">
                                    <i class="fas fa-university me-2"></i> Quản lý khoa
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('majors.index') }}">
                                    <i class="fas fa-book me-2"></i> Quản lý ngành
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('subjects.index') }}">
                                    <i class="fas fa-book-open me-2"></i> Quản lý môn học
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="{{ route('questions.index') }}">
                                    <i class="fas fa-question-circle me-2"></i> Ngân hàng câu hỏi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('exams.index') }}">
                                    <i class="fas fa-file-alt me-2"></i> Danh sách đề thi
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    <!-- Quản lý kỳ thi -->
                    @if(Auth::user()->role == 2)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-alt me-1"></i> Tổ chức kỳ Thi
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('exam-periods.*') ? 'active' : '' }}" 
                                   href="{{ route('exam-periods.index') }}">
                                    <i class="fas fa-calendar-alt me-2"></i> Danh sách kỳ thi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item
                                {{-- {{ request()->routeIs('exam-periods.*') ? 'active' : '' }} --}}
                                 " 
                                   href="{{ route('exam-periods.index') }}">
                                    <i class="fas fa-file-alt me-2"></i> Trình hướng dẫn tổ chức kỳ thi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('edudex-files.*') ? 'active' : '' }}" 
                                   href="{{ route('edudex-files.index') }}">
                                    <i class="fas fa-calendar-alt me-2"></i> Đọc file .edudex
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>
                @endauth

                <!-- User dropdown cho desktop -->
                <ul class="navbar-nav ms-auto d-none d-lg-flex">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-2"></i> Đăng ký
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown user-dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="{{ Auth::user()->accountInfo->avatar 
                                    ? asset('storage/avatars/' . Auth::user()->accountInfo->avatar) 
                                    : asset('images/default-avatar.jpg') }}" 
                                     class="rounded-circle me-2" width="32" height="32">
                                <div>
                                    <div class="fw-bold text-light">{{ Auth::user()->username }}</div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="fas fa-user me-2"></i> Thông tin cá nhân
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item" onclick="setTheme('light')">
                                        <i class="fas fa-sun me-2"></i> Giao diện sáng
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" onclick="setTheme('dark')">
                                        <i class="fas fa-moon me-2"></i> Giao diện tối
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" onclick="setTheme('system')">
                                        <i class="fas fa-laptop me-2"></i> Theo hệ thống
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            @auth
            <!-- User Info -->
            <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                <img src="{{ Auth::user()->accountInfo->avatar 
                    ? asset('storage/avatars/' . Auth::user()->accountInfo->avatar) 
                    : asset('images/default-avatar.jpg') }}" 
                     class="rounded-circle me-3" width="48" height="48">
                <div>
                    <div class="fw-bold">{{ Auth::user()->username }}</div>
                    <div class="text-muted small">
                        @switch(Auth::user()->role)
                            @case(0) CBCT @break
                            @case(1) Giáo viên @break
                            @case(2) Admin @break
                        @endswitch
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <div class="list-group list-group-flush">
                <a href="/" class="list-group-item list-group-item-action {{ request()->is('/') ? 'active' : '' }}">
                    <i class="fas fa-home me-2"></i> Trang chủ
                </a>
                
                @if(Auth::user()->role == 2)
                <div class="sidebar-item">
                    <a href="#adminSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog me-2"></i> Tài khoản
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </a>
                    <div class="collapse {{ request()->is('accounts*') ? 'show' : '' }}" id="adminSubmenu">
                        <a href="{{ route('accounts.index') }}" 
                           class="list-group-item list-group-item-action ps-5 {{ request()->is('accounts') ? 'active' : '' }}">
                            <i class="fas fa-users me-2"></i> Danh sách tài khoản
                        </a>
                        <a href="{{ route('accounts.index') }}" 
                        class="list-group-item list-group-item-action ps-5 {{ request()->is('accounts') ? 'active' : '' }}">
                         <i class="fas fa-user-plus me-2"></i> Thêm tài khoản
                     </a>
                        <a href="{{ route('accounts.tools') }}" 
                           class="list-group-item list-group-item-action ps-5 {{ request()->is('accounts/tools*') ? 'active' : '' }}">
                            <i class="fas fa-file-excel me-2"></i> Import/Export
                        </a>
                    </div>
                </div>
                <div class="sidebar-item">
                    <a href="#adminSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog me-2"></i> Cơ sở vật chất
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </a>
                    <div class="collapse {{ request()->is('facilities*', 'rooms*') ? 'show' : '' }}" id="adminSubmenu">
                        <div class="border-top my-2"></div>
                        <a href="{{ route('facilities.index') }}" 
                           class="list-group-item list-group-item-action ps-5 {{ request()->is('facilities*') ? 'active' : '' }}">
                            <i class="fas fa-building me-2"></i> Quản lý cơ sở
                        </a>
                        <a href="{{ route('rooms.index') }}" 
                           class="list-group-item list-group-item-action ps-5 {{ request()->is('rooms*') ? 'active' : '' }}">
                            <i class="fas fa-door-open me-2"></i> Quản lý phòng
                        </a>
                    </div>
                </div>
                @endif
                <div class="sidebar-item">
                    <a href="#themeSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-palette me-2"></i> Giao diện
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </a>
                    <div class="collapse" id="themeSubmenu">
                        <button onclick="setTheme('light')" 
                                class="list-group-item list-group-item-action ps-5">
                            <i class="fas fa-sun me-2"></i> Sáng
                        </button>
                        <button onclick="setTheme('dark')" 
                                class="list-group-item list-group-item-action ps-5">
                            <i class="fas fa-moon me-2"></i> Tối
                        </button>
                        <button onclick="setTheme('system')" 
                                class="list-group-item list-group-item-action ps-5">
                            <i class="fas fa-laptop me-2"></i> Hệ thống
                        </button>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</header> 