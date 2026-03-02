@extends('admin.master')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Ad Settings</h4>
        </div>
        <a href="{{ route('adsetting.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Ad
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">

        @foreach(\App\Models\Adsetting::adTypes() as $type => $label)

            @php
                $ad = $adsettings->firstWhere('ad_type', $type);
            @endphp

            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fw-semibold mb-2">{{ $label }}</div>
                        @if($ad)
                            <span class="badge {{ $ad->is_active ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $ad->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Not Set</span>
                        @endif
                    </div>
                </div>
            </div>

        @endforeach

    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Label</th>
                        <th>Ad Type</th>
                        <th>Ad Unit ID</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($adsettings as $index => $ad)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $ad->label }}</td>
                            <td>{{ $ad->ad_type_label }}</td>
                            <td>
                                <code>{{ \Illuminate\Support\Str::limit($ad->ad_unit_id, 30) }}</code>
                            </td>
                            <td>
                                <span class="badge {{ $ad->is_active ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $ad->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">

                                {{-- Edit Button --}}
                                <a href="{{ route('adsetting.edit', $ad->id) }}"
                                   class="btn btn-sm btn-outline-primary me-1"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- Delete Button --}}
                                <form action="{{ route('adsetting.destroy', $ad->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this ad?')">
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
                            <td colspan="6" class="text-center text-muted py-4">
                                No ad settings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>
@endsection
