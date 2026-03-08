@extends('admin.master')
@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">⚙️ App Settings</h4>
            <small class="text-muted">Manage your application settings</small>
        </div>
        @if(!$setting)
            <a href="{{ route('settings.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Settings
            </a>
        @else
            <a href="{{ route('settings.edit', $setting->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit Settings
            </a>
        @endif
    </div>


    {{-- No Settings Yet --}}
    @if(!$setting)
        <div class="card shadow-sm border-0 text-center py-5">
            <div class="card-body">
                <i class="fas fa-cog fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No settings found</h5>
                <p class="text-muted mb-4">Please create your app settings to get started.</p>
                <a href="{{ route('settings.create') }}" class="btn btn-primary px-4">
                    <i class="fas fa-plus me-1"></i> Create Settings
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">

            {{-- App Photo --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center py-4">
                        <p class="text-muted small fw-semibold mb-3 text-uppercase">App Photo</p>
                        @if($setting->photo)
                            <img src="{{ asset('uploads/setting/' . $setting->photo) }}"
                                 alt="App Photo"
                                 class="img-fluid rounded-3 shadow-sm"
                                 style="max-height: 180px; object-fit: cover; width: 100%;">
                        @else
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center"
                                 style="height:180px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Settings Details --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-4 border-bottom pb-2">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Settings Details
                        </h5>

                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width:200px;">
                                        <i class="fas fa-users me-2 text-primary"></i>Join Community URL
                                    </td>
                                    <td>
                                        <a href="{{ $setting->join_commuity_url }}" target="_blank"
                                           class="text-decoration-none text-break">
                                            {{ $setting->join_commuity_url }}
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">
                                        <i class="fas fa-video me-2 text-danger"></i>Video Tutorial URL
                                    </td>
                                    <td>
                                        @if($setting->video_tutorial_video_url)
                                            <a href="{{ $setting->video_tutorial_video_url }}" target="_blank"
                                               class="text-decoration-none text-break">
                                                {{ $setting->video_tutorial_video_url }}
                                                <i class="fas fa-external-link-alt ms-1 small"></i>
                                            </a>
                                        @else
                                            <span class="text-muted fst-italic">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">
                                        <i class="fas fa-code me-2 text-success"></i>Developer Portal URL
                                    </td>
                                    <td>
                                        <a href="{{ $setting->developer_portal_url }}" target="_blank"
                                           class="text-decoration-none text-break">
                                            {{ $setting->developer_portal_url }}
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">
                                        <i class="fas fa-star me-2 text-warning"></i>Rate App URL
                                    </td>
                                    <td>
                                        <a href="{{ $setting->rate_app_url }}" target="_blank"
                                           class="text-decoration-none text-break">
                                            {{ $setting->rate_app_url }}
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">
                                        <i class="fas fa-envelope me-2 text-info"></i>Email
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $setting->email }}" class="text-decoration-none">
                                            {{ $setting->email }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">
                                        <i class="fas fa-clock me-2 text-secondary"></i>Last Updated
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $setting->updated_at->format('d M Y, h:i A') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2 mt-3 pt-3 border-top">
                            <a href="{{ route('settings.edit', $setting->id) }}"
                               class="btn btn-warning btn-sm px-4">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>

                            <form action="{{ route('settings.destroy', $setting->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete settings? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm px-4">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    @endif

</div>

@endsection
