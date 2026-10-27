@if (session('status'))
    <div class="alert alert-ok" role="status"><x-icon name="check" :size="18" /> <span>{{ session('status') }}</span></div>
@endif

@if (session('error'))
    <div class="alert alert-err" role="alert"><x-icon name="alert" :size="18" /> <span>{{ session('error') }}</span></div>
@endif

@if ($errors->has('items'))
    <div class="alert alert-err" role="alert"><x-icon name="alert" :size="18" /> <span>{{ $errors->first('items') }}</span></div>
@endif
