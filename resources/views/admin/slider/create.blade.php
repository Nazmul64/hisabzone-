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

            <form action="{{ route('slider.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Title (Fallback - যেকোনো ভাষায় দিতে পারেন) --}}
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">
                        Title <span class="text-danger">*</span>
                        <small class="text-muted fw-normal">(fallback - যেকোনো ভাষায়)</small>
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

                {{-- Title Key (Translation Key) --}}
                <div class="mb-3">
                    <label for="title_key" class="form-label fw-semibold">
                        Title Key
                        <small class="text-muted fw-normal">(translation key - 30 ভাষায় auto translate হবে)</small>
                    </label>
                    <select
                        id="title_key"
                        name="title_key"
                        class="form-select @error('title_key') is-invalid @enderror"
                    >
                        <option value="">-- Select Translation Key (Optional) --</option>
                        <optgroup label="🌟 Premium / App Features">
                            <option value="premium_features" {{ old('title_key') == 'premium_features' ? 'selected' : '' }}>premium_features → Premium Features</option>
                            <option value="premium_plan" {{ old('title_key') == 'premium_plan' ? 'selected' : '' }}>premium_plan → Premium Plan</option>
                            <option value="premium_subtitle" {{ old('title_key') == 'premium_subtitle' ? 'selected' : '' }}>premium_subtitle → Unlock all features</option>
                            <option value="unlock_features" {{ old('title_key') == 'unlock_features' ? 'selected' : '' }}>unlock_features → Unlock all features</option>
                            <option value="buy_premium" {{ old('title_key') == 'buy_premium' ? 'selected' : '' }}>buy_premium → Buy Premium</option>
                        </optgroup>
                        <optgroup label="📊 Reports / Finance">
                            <option value="financial_analysis" {{ old('title_key') == 'financial_analysis' ? 'selected' : '' }}>financial_analysis → Financial Analysis</option>
                            <option value="income_expense_report" {{ old('title_key') == 'income_expense_report' ? 'selected' : '' }}>income_expense_report → Income-Expense Report</option>
                            <option value="total_balance" {{ old('title_key') == 'total_balance' ? 'selected' : '' }}>total_balance → Total Balance</option>
                        </optgroup>
                        <optgroup label="⚙️ Settings / App">
                            <option value="backup_restore" {{ old('title_key') == 'backup_restore' ? 'selected' : '' }}>backup_restore → Backup & Restore</option>
                            <option value="app_settings" {{ old('title_key') == 'app_settings' ? 'selected' : '' }}>app_settings → App Settings</option>
                            <option value="help_support" {{ old('title_key') == 'help_support' ? 'selected' : '' }}>help_support → Help & Support</option>
                            <option value="notifications" {{ old('title_key') == 'notifications' ? 'selected' : '' }}>notifications → Notifications</option>
                            <option value="security" {{ old('title_key') == 'security' ? 'selected' : '' }}>security → Security</option>
                        </optgroup>
                        <optgroup label="📦 Stock">
                            <option value="stock_management" {{ old('title_key') == 'stock_management' ? 'selected' : '' }}>stock_management → Stock Management</option>
                            <option value="products" {{ old('title_key') == 'products' ? 'selected' : '' }}>products → Products</option>
                        </optgroup>
                        <optgroup label="🏷️ Categories">
                            <option value="categories" {{ old('title_key') == 'categories' ? 'selected' : '' }}>categories → Categories</option>
                            <option value="food" {{ old('title_key') == 'food' ? 'selected' : '' }}>food → Food</option>
                            <option value="shopping" {{ old('title_key') == 'shopping' ? 'selected' : '' }}>shopping → Shopping</option>
                            <option value="salary" {{ old('title_key') == 'salary' ? 'selected' : '' }}>salary → Salary</option>
                            <option value="education" {{ old('title_key') == 'education' ? 'selected' : '' }}>education → Education</option>
                            <option value="health" {{ old('title_key') == 'health' ? 'selected' : '' }}>health → Health</option>
                        </optgroup>
                        <optgroup label="ℹ️ General">
                            <option value="tagline" {{ old('title_key') == 'tagline' ? 'selected' : '' }}>tagline → Your Financial Partner</option>
                            <option value="coming_soon" {{ old('title_key') == 'coming_soon' ? 'selected' : '' }}>coming_soon → Coming Soon</option>
                            <option value="overview" {{ old('title_key') == 'overview' ? 'selected' : '' }}>overview → Overview</option>
                            <option value="app_name" {{ old('title_key') == 'app_name' ? 'selected' : '' }}>app_name → App Name</option>
                        </optgroup>
                    </select>
                    <div class="form-text text-info">
                        💡 Title Key সিলেক্ট করলে user-এর language অনুযায়ী 30 ভাষায় auto translate হবে।
                        না করলে Title field-এর text দেখাবে।
                    </div>
                    @error('title_key')
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
                        <img id="photo-preview" src="" alt="Preview" width="150"
                            style="border-radius: 6px; border: 1px solid #dee2e6;">
                    </div>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select id="status" name="status"
                        class="form-select @error('status') is-invalid @enderror" required>
                        <option value="" disabled {{ old('status') === null ? 'selected' : '' }}>-- Select Status --</option>
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
                    <a href="{{ route('slider.index') }}" class="btn btn-secondary">Cancel</a>
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
