@foreach (['danger', 'warning'] as $key)
    @if(Session::has($key))
        <div class="callout callout-{{ $key }} qry-alert">
            <h4>Errors !</h4>

            <p>{{ Session::get($key) }}</p>
        </div>
    @endif
@endforeach

@foreach (['success', 'info'] as $key)
    @if(Session::has($key))
        <div class="callout callout-{{ $key }} qry-alert">
            <h4>Success !</h4>

            <p>{{ Session::get($key) }}</p>
        </div>
    @endif
@endforeach

@if($errors->any())
    <div class="callout callout-danger qry-alert">
        <h4>Validate Errors !</h4>

        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif