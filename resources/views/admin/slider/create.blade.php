@extends('admin.master')
@section('content')

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Add Slider</h4>
        <a href="{{ route('slider.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('slider.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                {{-- Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">
                        Title <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        class="form-control @error('title') is-invalid @enderror"
                        placeholder="Enter slider title"
                        required
                    >
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- URL --}}
                <div class="mb-3">
                    <label for="url" class="form-label fw-semibold">
                        URL <small class="text-muted fw-normal">(optional)</small>
                    </label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        value="{{ old('url') }}"
                        class="form-control @error('url') is-invalid @enderror"
                        placeholder="https://example.com"
                    >
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Photo --}}
                <div class="mb-3">
                    <label for="photo" class="form-label fw-semibold">
                        Photo <span class="text-danger">*</span>
                    </label>
                    <input
                        type="file"
                        id="photo"
                        name="photo"
                        class="form-control @error('photo') is-invalid @enderror"
                        accept="image/*"
                        onchange="previewImage(event)"
                        required
                    >
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="mt-2" id="preview-wrapper" style="display: none;">
                        <img
                            id="photo-preview"
                            src=""
                            alt="Preview"
                            width="150"
                            style="border-radius: 6px; border: 1px solid #dee2e6;"
                        >
                    </div>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="form-select @error('status') is-invalid @enderror"
                        required
                    >
                        <option value="" disabled {{ old('status') === null ? 'selected' : '' }}>
                            -- Select Status --
                        </option>
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                    <a href="{{ route('slider.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
                document.getElementById('preview-wrapper').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush

@endsection
