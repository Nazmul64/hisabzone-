@extends('admin.master')
@section('content')

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Slider</h4>
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
                action="{{ route('slider.update', $slider->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">
                        Title <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $slider->title) }}"
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
                        value="{{ old('url', $slider->url) }}"
                        class="form-control @error('url') is-invalid @enderror"
                        placeholder="https://example.com"
                    >
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Current Photo --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Photo</label>
                    <br>
                    <img
                        id="photo-preview"
                        src="{{ asset('uploads/slider/' . $slider->photo) }}"
                        alt="{{ $slider->title }}"
                        width="150"
                        style="border-radius: 6px; border: 1px solid #dee2e6;"
                    >
                </div>

                {{-- New Photo --}}
                <div class="mb-3">
                    <label for="photo" class="form-label fw-semibold">
                        New Photo <small class="text-muted fw-normal">(leave empty to keep current)</small>
                    </label>
                    <input
                        type="file"
                        id="photo"
                        name="photo"
                        class="form-control @error('photo') is-invalid @enderror"
                        accept="image/*"
                        onchange="previewImage(event)"
                    >
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                        <option value="" disabled>-- Select Status --</option>
                        <option value="1" {{ old('status', $slider->status) == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $slider->status) == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sync-alt me-1"></i> Update
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
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush

@endsection
