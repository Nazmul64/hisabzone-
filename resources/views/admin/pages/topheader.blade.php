<header>
    <div class="topbar">
        <nav class="navbar navbar-expand gap-2 align-items-center">

            {{-- Mobile sidebar toggle --}}
            <div class="mobile-toggle-menu d-flex">
                <i class='bx bx-menu'></i>
            </div>

            <div class="top-menu ms-auto"></div>

            {{-- User Dropdown --}}
            <div class="user-box px-3">
                <a class="d-flex align-items-center nav-link gap-3"
                   href="#"
                   id="userDropdownToggle">
                    <img src="{{ asset('admin') }}/assets/images/avatars/avatar-2.png"
                         class="user-img" alt="user avatar">
                    <div class="user-info">
                        <p class="user-name mb-0">{{ auth()->user()->name }}</p>
                    </div>
                    <i class='bx bx-chevron-down ms-1'></i>
                </a>

                {{-- Dropdown Menu --}}
                <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bx bx-log-out-circle"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Logout Form --}}
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                @csrf
            </form>

        </nav>
    </div>
</header>


<style>
    .user-box {
        position: relative;
    }

    #userDropdownToggle {
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }

    #userDropdownMenu {
        display: none;
        position: absolute;
        right: 12px;
        top: 110%;
        min-width: 180px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        z-index: 9999;
        padding: 6px 0;
        list-style: none;
        margin: 0;
    }

    #userDropdownMenu.show {
        display: block;
        animation: fadeDown 0.2s ease;
    }

    @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    #userDropdownMenu .dropdown-item {
        padding: 9px 16px;
        font-size: 14px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.15s;
    }

    #userDropdownMenu .dropdown-item:hover {
        background: #f0f4ff;
        color: #0d6efd;
    }

    #userDropdownMenu .dropdown-item i {
        font-size: 18px;
    }

    /* Arrow rotate on open */
    #userDropdownToggle .bx-chevron-down {
        transition: transform 0.3s;
    }
    #userDropdownToggle.open .bx-chevron-down {
        transform: rotate(180deg);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('userDropdownToggle');
        var menu   = document.getElementById('userDropdownMenu');

        // Click korle open/close
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            menu.classList.toggle('show');
            toggle.classList.toggle('open');
        });

        // Bairer jaga click korle close
        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
                toggle.classList.remove('open');
            }
        });
    });
</script>
