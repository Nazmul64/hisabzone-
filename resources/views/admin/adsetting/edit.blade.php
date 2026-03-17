@extends('admin.master')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('adsetting.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold">Edit Ad Setting</h4>
            <small class="text-muted">
                {{ $adsetting->label ?: $adsetting->ad_type_label }} edit করুন
            </small>
        </div>
    </div>

    {{-- ✅ trigger_frequency = 0 হলে warning দেখাও --}}
    @if($adsetting->trigger_frequency == 0)
        <div class="alert alert-danger d-flex align-items-center mb-4" style="max-width: 860px;" role="alert">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <div>
                <strong>সমস্যা!</strong>
                এই ad এর <code>trigger_frequency = 0</code> আছে।
                Flutter এ এই ad <strong>কখনো show হবে না</strong>।
                নিচে <strong>কমপক্ষে 1</strong> দিয়ে Update করুন।
            </div>
        </div>
    @endif

    <div class="card shadow-sm" style="max-width: 860px;">
        <div class="card-body p-4">
            <form action="{{ route('adsetting.update', $adsetting->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">

                    {{-- ── Left Column ──────────────────────────────────── --}}
                    <div class="col-md-6">

                        {{-- Ad Type --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Ad Type <span class="text-danger">*</span>
                            </label>
                            <select name="ad_type"
                                    class="form-select @error('ad_type') is-invalid @enderror"
                                    required>
                                <option value="">-- Ad Type বেছে নিন --</option>
                                @foreach($adTypes as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('ad_type', $adsetting->ad_type) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ad_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">AdMob console এর সাথে মিলিয়ে বেছে নিন।</div>
                        </div>

                        {{-- Label --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Label / নাম</label>
                            <input type="text" name="label"
                                   class="form-control @error('label') is-invalid @enderror"
                                   value="{{ old('label', $adsetting->label) }}"
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
                                   value="{{ old('ad_unit_id', $adsetting->ad_unit_id) }}"
                                   placeholder="ca-app-pub-XXXXXXXXXXXXXXXX/XXXXXXXXXX"
                                   required>
                            @error('ad_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">AdMob console থেকে Ad Unit ID কপি করুন।</div>
                        </div>

                        {{-- AdMob App ID --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                AdMob App ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="admob_app_id"
                                   class="form-control font-monospace @error('admob_app_id') is-invalid @enderror"
                                   value="{{ old('admob_app_id', $adsetting->admob_app_id) }}"
                                   placeholder="ca-app-pub-XXXXXXXXXXXXXXXX~XXXXXXXXXX"
                                   required>
                            @error('admob_app_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">AdMob console থেকে App ID কপি করুন।</div>
                        </div>

                    </div>

                    {{-- ── Right Column ─────────────────────────────────── --}}
                    <div class="col-md-6">

                        {{-- Trigger --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Trigger — কখন show করবে</label>
                            <select name="trigger"
                                    class="form-select @error('trigger') is-invalid @enderror">
                                <option value="">-- Trigger বেছে নিন --</option>
                                @foreach($triggers as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('trigger', $adsetting->trigger) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('trigger')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">App এ কোন event এ এই ad দেখাবে।</div>
                        </div>

                        {{-- Trigger Frequency --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Trigger Frequency
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">প্রতি</span>
                                {{-- ✅ min=1 — 0 দেওয়া যাবে না --}}
                                {{-- ✅ DB তে 0 থাকলে input এ 1 দেখাবে --}}
                                <input type="number"
                                       name="trigger_frequency"
                                       min="1"
                                       max="9999"
                                       class="form-control @error('trigger_frequency') is-invalid @enderror {{ $adsetting->trigger_frequency == 0 ? 'is-invalid border-danger' : '' }}"
                                       value="{{ old('trigger_frequency', max(1, $adsetting->trigger_frequency)) }}"
                                       required>
                                <span class="input-group-text">action পর</span>
                            </div>
                            @error('trigger_frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($adsetting->trigger_frequency == 0 && !$errors->has('trigger_frequency'))
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    DB তে 0 ছিল — Flutter এ ad show হচ্ছিল না!
                                    এখন 1 সেট করা হয়েছে। Update করুন।
                                </div>
                            @else
                                <div class="form-text">
                                    <i class="fas fa-info-circle text-primary"></i>
                                    কত action পর ad দেখাবে।
                                    <strong>1</strong> = প্রতিটা action এ,
                                    <strong>3</strong> = প্রতি ৩ action এ।
                                    <span class="text-danger fw-semibold">0 দিলে Flutter এ ad show হবে না!</span>
                                </div>
                            @endif
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Admin notes (optional)...">{{ old('notes', $adsetting->notes) }}</textarea>
                        </div>

                        {{-- Is Active --}}
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $adsetting->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">
                                Active
                                <small class="text-muted fw-normal">
                                    (এই ad এখনই চালু থাকবে)
                                </small>
                            </label>
                        </div>

                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('adsetting.index') }}"
                       class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
