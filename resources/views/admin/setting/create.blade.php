@extends('admin.master')
@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">➕ Create Settings</h4>
            <small class="text-muted">Fill in the details below to set up your app settings</small>
        </div>
        <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>


    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">

                    {{-- Join Community URL --}}
                    <div class="col-md-6">
                        <label for="join_commuity_url" class="form-label fw-semibold">
                            <i class="fas fa-users me-1 text-primary"></i>
                            Join Community URL <span class="text-danger">*</span>
                        </label>
                        <input type="url"
                               name="join_commuity_url"
                               id="join_commuity_url"
                               class="form-control @error('join_commuity_url') is-invalid @enderror"
                               placeholder="https://t.me/yourcommunity"
                               value="{{ old('join_commuity_url') }}"
                               required>
                        @error('join_commuity_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Developer Portal URL --}}
                    <div class="col-md-6">
                        <label for="developer_portal_url" class="form-label fw-semibold">
                            <i class="fas fa-code me-1 text-success"></i>
                            Developer Portal URL <span class="text-danger">*</span>
                        </label>
                        <input type="url"
                               name="developer_portal_url"
                               id="developer_portal_url"
                               class="form-control @error('developer_portal_url') is-invalid @enderror"
                               placeholder="https://developer.yourapp.com"
                               value="{{ old('developer_portal_url') }}"
                               required>
                        @error('developer_portal_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Rate App URL --}}
                    <div class="col-md-6">
                        <label for="rate_app_url" class="form-label fw-semibold">
                            <i class="fas fa-star me-1 text-warning"></i>
                            Rate App URL <span class="text-danger">*</span>
                        </label>
                        <input type="url"
                               name="rate_app_url"
                               id="rate_app_url"
                               class="form-control @error('rate_app_url') is-invalid @enderror"
                               placeholder="https://play.google.com/store/apps/..."
                               value="{{ old('rate_app_url') }}"
                               required>
                        @error('rate_app_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1 text-info"></i>
                            Support Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="support@yourapp.com"
                               value="{{ old('email') }}"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Video Tutorial URL --}}
                    <div class="col-md-12">
                        <label for="video_tutorial_video_url" class="form-label fw-semibold">
                            <i class="fas fa-video me-1 text-danger"></i>
                            Video Tutorial URL
                            <small class="text-muted fw-normal">(optional)</small>
                        </label>
                        <input type="url"
                               name="video_tutorial_video_url"
                               id="video_tutorial_video_url"
                               class="form-control @error('video_tutorial_video_url') is-invalid @enderror"
                               placeholder="https://youtube.com/watch?v=..."
                               value="{{ old('video_tutorial_video_url') }}">
                        @error('video_tutorial_video_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- App Photo --}}
                    <div class="col-md-12">
                        <label for="photo" class="form-label fw-semibold">
                            <i class="fas fa-image me-1 text-purple"></i>
                            App Photo <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               name="photo"
                               id="photo"
                               accept="image/*"
                               class="form-control @error('photo') is-invalid @enderror"
                               onchange="previewPhoto(event)">
                        <div class="form-text">Accepted: jpeg, png, jpg, gif, webp. Max: 2MB</div>
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- Photo Preview --}}
                        <div id="photoPreviewWrapper" class="mt-3 d-none">
                            <p class="small text-muted mb-1">Preview:</p>
                            <img id="photoPreview"
                                 src="#"
                                 alt="Preview"
                                 class="rounded-3 shadow-sm"
                                 style="max-height: 180px; max-width: 300px; object-fit: cover;">
                        </div>
                    </div>

                </div>

                {{-- Submit --}}
                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photoPreview').src = e.target.result;
                document.getElementById('photoPreviewWrapper').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush

@endsection
