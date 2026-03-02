@extends('admin.master')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('adsetting.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold">New Ad Setting</h4>
            <small class="text-muted">নতুন ad যোগ করুন</small>
        </div>
    </div>

    <div class="card shadow-sm" style="max-width: 860px;">
        <div class="card-body p-4">
            <form action="{{ route('adsetting.store') }}" method="POST">
                @csrf

                <div class="row g-4">

                    {{-- ── Left Column ──────────────────────────────────── --}}
                    <div class="col-md-6">

                        {{-- Ad Type --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Ad Type <span class="text-danger">*</span>
                            </label>
                            <select name="ad_type" class="form-select @error('ad_type') is-invalid @enderror" required>
                                <option value="">-- Ad Type বেছে নিন --</option>
                                <option value="banner"                {{ old('ad_type') === 'banner' ? 'selected' : '' }}>Banner</option>
                                <option value="interstitial"          {{ old('ad_type') === 'interstitial' ? 'selected' : '' }}>Interstitial</option>
                                <option value="rewarded_interstitial" {{ old('ad_type') === 'rewarded_interstitial' ? 'selected' : '' }}>Rewarded Interstitial (Beta)</option>
                                <option value="rewarded"              {{ old('ad_type') === 'rewarded' ? 'selected' : '' }}>Rewarded</option>
                                <option value="native_advanced"       {{ old('ad_type') === 'native_advanced' ? 'selected' : '' }}>Native Advanced</option>
                                <option value="app_open"              {{ old('ad_type') === 'app_open' ? 'selected' : '' }}>App Open</option>
                            </select>
                            @error('ad_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">AdMob console-এর সাথে মিলিয়ে বেছে নিন।</div>
                        </div>

                        {{-- Label --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Label / নাম</label>
                            <input type="text" name="label"
                                   class="form-control @error('label') is-invalid @enderror"
                                   value="{{ old('label') }}"
                                   placeholder="e.g. Home Screen Banner">
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ad Unit ID --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Ad Unit ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ad_unit_id"
                                   class="form-control font-monospace @error('ad_unit_id') is-invalid @enderror"
                                   value="{{ old('ad_unit_id') }}"
                                   placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/XXXXXXXXXX"
                                   required>
                            @error('ad_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">AdMob console থেকে কপি করুন।</div>
                        </div>
                         {{-- Ad Unit ID --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                AdMob App ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="admob_app_id"
                                   class="form-control font-monospace @error('admob_app_id') is-invalid @enderror"
                                   value="{{ old('admob_app_id') }}"
                                   placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/XXXXXXXXXX"
                                   required>
                            @error('admob_app_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">AdMob console থেকে কপি করুন।</div>
                        </div>

                        {{-- AdMob App ID --}}

                    </div>

                    {{-- ── Right Column ─────────────────────────────────── --}}
                    <div class="col-md-6">

                        {{-- Trigger --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Trigger — কখন শো করবে</label>
                            <select name="trigger" class="form-select @error('trigger') is-invalid @enderror">
                                <option value="">-- Trigger বেছে নিন --</option>
                                <option value="app_open"        {{ old('trigger') === 'app_open' ? 'selected' : '' }}>App Open / Launch</option>
                                <option value="level_complete"  {{ old('trigger') === 'level_complete' ? 'selected' : '' }}>Level Complete</option>
                                <option value="every_x_actions" {{ old('trigger') === 'every_x_actions' ? 'selected' : '' }}>Every X Actions</option>
                                <option value="page_change"     {{ old('trigger') === 'page_change' ? 'selected' : '' }}>Page / Screen Change</option>
                                <option value="manual"          {{ old('trigger') === 'manual' ? 'selected' : '' }}>Manual (Code-controlled)</option>
                            </select>
                            @error('trigger')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">App-এ কোন ইভেন্টে এই Ad দেখাবে।</div>
                        </div>

                        {{-- Frequency --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Trigger Frequency</label>
                            <div class="input-group">
                                <span class="input-group-text">প্রতি</span>
                                <input type="number" name="trigger_frequency" min="0" max="9999"
                                       class="form-control @error('trigger_frequency') is-invalid @enderror"
                                       value="{{ old('trigger_frequency', 0) }}">
                                <span class="input-group-text">বার পর</span>
                            </div>
                            @error('trigger_frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text"><code>0</code> = সবসময় দেখাবে।</div>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Admin notes...">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Is Active --}}
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_active" id="is_active" value="1"
                                   {{ old('is_active') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">
                                Active
                                <small class="text-muted fw-normal">(এই ad এখন চালু থাকবে)</small>
                            </label>
                        </div>

                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                    <a href="{{ route('adsetting.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
