@foreach($user->licenses as $license)
    <div class="toggleGroup open">

         <a class="toggleBtn"><i class="icon-arrow"></i></a>

        <strong>{{$license->license_key}}</strong>

        @if(count($license->activations) > 0 )
        <div class="toggleGroup nested">
            <a href="" class="toggleBtn"><i class="icon-arrow"></i> Active sites</a>
            <div class="toggle indent">
                <ul class="unstyled">
                @foreach($license->activations as $activation)
                    <li><a href="{{ $activation->url }}">{{ $activation->domain }}</a></li>
                @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
@endforeach
