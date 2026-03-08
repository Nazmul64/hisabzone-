@extends('admin.master')
@section('content')
<div class="page-content">

    {{-- ══════════════════════════════════════════
         Stat Cards Row
    ══════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">

        {{-- Total Users --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card rounded-4 mb-0 border-0 shadow-sm">
                <div class="card-body">
                    <div class="hstack align-items-center gap-3">
                        <div class="widgets-icons bg-light-primary text-primary rounded-3 d-flex align-items-center justify-content-center">
                            <i class='bx bx-group fs-4'></i>
                        </div>
                        <hr class="vr">
                        <div>
                            <h4 class="mb-0 fw-bold">{{ number_format($total_users) }}</h4>
                            <p class="mb-0 text-muted small">Total Users</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Sliders --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card rounded-4 mb-0 border-0 shadow-sm">
                <div class="card-body">
                    <div class="hstack align-items-center gap-3">
                        <div class="widgets-icons bg-light-warning text-warning rounded-3 d-flex align-items-center justify-content-center">
                            <i class='bx bx-slideshow fs-4'></i>
                        </div>
                        <hr class="vr">
                        <div>
                            <h4 class="mb-0 fw-bold">{{ number_format($total_sliders) }}</h4>
                            <p class="mb-0 text-muted small">Total Sliders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Income --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card rounded-4 mb-0 border-0 shadow-sm">
                <div class="card-body">
                    <div class="hstack align-items-center gap-3">
                        <div class="widgets-icons bg-light-success text-success rounded-3 d-flex align-items-center justify-content-center">
                            <i class='bx bx-dollar-circle fs-4'></i>
                        </div>
                        <hr class="vr">
                        <div>
                            <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                $84,256
                                <span class="dash-lable d-flex align-items-center gap-1 rounded mb-0 bg-light-danger text-danger small">
                                    <i class='bx bx-up-arrow-alt'></i>8.6%
                                </span>
                            </h4>
                            <p class="mb-0 text-muted small">Total Income</p>
                        </div>
                    </div>
                    <div id="chart1" class="mt-2"></div>
                </div>
            </div>
        </div>

        {{-- Total Clicks --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card rounded-4 mb-0 border-0 shadow-sm overflow-hidden">
                <div class="card-body pb-1">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold">87.4K</h4>
                            <p class="mb-0 text-muted small">Total Clicks</p>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle-nocaret more-options dropdown-toggle"
                               data-bs-toggle="dropdown">
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <div id="chart4"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         Active Users List + Sales Chart Row
    ══════════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- Active Users List --}}
        <div class="col-12 col-xl-4">
            <div class="card rounded-4 mb-0 border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0 fw-semibold">Active Users</h6>
                        <span class="badge bg-light-primary text-primary rounded-pill px-3">
                            {{ $active_users->count() }}
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @forelse($active_users as $user)
                            <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-light">

                                {{-- Initials Avatar --}}
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     style="width:38px; height:38px; font-size:13px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>

                                <div class="overflow-hidden flex-grow-1">
                                    <p class="mb-0 fw-semibold text-truncate">{{ $user->name }}</p>
                                    <p class="mb-0 text-muted small text-truncate">{{ $user->email }}</p>
                                </div>

                                <span class="badge bg-light-success text-success rounded-pill flex-shrink-0">
                                    <i class='bx bx-check-circle me-1'></i>Active
                                </span>

                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class='bx bx-user-x fs-1 d-block mb-2'></i>
                                No users found.
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>

        {{-- Sales & Views Chart --}}
        <div class="col-12 col-xl-8">
            <div class="card rounded-4 mb-0 border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <h6 class="mb-0 fw-semibold">Sales & Views</h6>
                        <div class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle-nocaret more-options dropdown-toggle"
                               data-bs-toggle="dropdown">
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="chart3"></div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
