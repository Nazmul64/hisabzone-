@extends('admin.master')
@section('content')

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Slider List</h4>
        <a href="{{ route('slider.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Slider
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="60">SL</th>
                        <th>Title</th>
                        <th>URL</th>
                        <th width="120">Photo</th>
                        <th width="100">Status</th>
                        <th width="160">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sliders as $key => $slider)
                        <tr>
                            <td class="align-middle">{{ $key + 1 }}</td>

                            <td class="align-middle">{{ $slider->title }}</td>

                            <td class="align-middle">
                                @if($slider->url)
                                    <a href="{{ $slider->url }}" target="_blank">
                                        {{ Str::limit($slider->url, 30) }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="align-middle">
                                <img
                                    src="{{ asset('uploads/slider/' . $slider->photo) }}"
                                    alt="{{ $slider->title }}"
                                    width="80"
                                    height="50"
                                    style="object-fit: cover; border-radius: 4px;"
                                >
                            </td>

                            <td class="align-middle">
                                @if($slider->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>

                            <td class="align-middle">
                                <a href="{{ route('slider.edit', $slider->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <form
                                    action="{{ route('slider.destroy', $slider->id) }}"
                                    method="POST"
                                    style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this slider?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No sliders found.
                                <a href="{{ route('slider.create') }}">Add one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
