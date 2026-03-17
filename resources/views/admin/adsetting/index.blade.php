@extends('admin.master')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Ad Settings</h4>
            <small class="text-muted">AdMob সব ad type এর configuration</small>
        </div>
        <a href="{{ route('adsetting.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Ad
        </a>
    </div>

    {{-- Success / Error Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Summary Cards — প্রতিটি ad type এর status --}}
    <div class="row g-3 mb-4">
        @foreach($adTypes as $typeKey => $typeLabel)
            @php $ad = $adsettings->firstWhere('ad_type', $typeKey); @endphp
            <div class="col-md-2">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center py-3">
                        <div class="fw-semibold small mb-2">{{ $typeLabel }}</div>
                        @if($ad)
                            @if($ad->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-warning text-dark">Inactive</span>
                            @endif
                            <div class="text-muted mt-1" style="font-size:10px">
                                freq: {{ $ad->trigger_frequency }}
                            </div>
                        @else
                            <span class="badge bg-secondary">Not Set</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Alert if any active ad has trigger_frequency = 0 --}}
    @if($adsettings->where('is_active', true)->where('trigger_frequency', 0)->count() > 0)
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>
                <strong>সতর্কতা!</strong>
                কিছু Active ad এর <code>trigger_frequency = 0</code> আছে।
                Flutter এ এই ad গুলো show হবে না।
                Edit করে কমপক্ষে <strong>1</strong> দিন।
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Label</th>
                        <th>Ad Type</th>
                        <th>Ad Unit ID</th>
                        <th class="text-center" style="width:80px">Trigger</th>
                        <th class="text-center" style="width:90px">
                            Frequency
                            <i class="fas fa-info-circle text-warning"
                               title="কত action পর ad দেখাবে। 0 হলে ad show হয় না!"></i>
                        </th>
                        <th class="text-center" style="width:80px">Status</th>
                        <th class="text-center" style="width:100px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adsettings as $index => $ad)
                        <tr class="{{ $ad->is_active && $ad->trigger_frequency == 0 ? 'table-warning' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $ad->label ?: '—' }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-75">
                                    {{ $ad->ad_type_label }}
                                </span>
                            </td>
                            <td>
                                <code class="small">{{ Str::limit($ad->ad_unit_id, 35) }}</code>
                            </td>
                            <td class="text-center small">
                                {{ $ad->trigger ?: '—' }}
                            </td>
                            <td class="text-center">
                                @if($ad->trigger_frequency == 0)
                                    <span class="badge bg-danger" title="0 মানে ad show হবে না! Edit করুন">
                                        0 ⚠️
                                    </span>
                                @else
                                    <span class="badge bg-info text-dark">
                                        {{ $ad->trigger_frequency }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($ad->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- Edit --}}
                                <a href="{{ route('adsetting.edit', $ad->id) }}"
                                   class="btn btn-sm btn-outline-primary me-1"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- Delete --}}
                                <form action="{{ route('adsetting.destroy', $ad->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('এই ad মুছে দিবেন?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-ad fa-2x mb-2 d-block"></i>
                                কোনো ad setting নেই।
                                <a href="{{ route('adsetting.create') }}">এখনই যোগ করুন</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
