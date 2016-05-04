@foreach($user->licenses as $license)
    <div class="toggleGroup open">
        <strong>{{$license->license_key}}</strong> <a class="toggleBtn"><i class="icon-arrow"></i></a>

        <div class="toggle indent">
            <p>
                <span class="muted">{{ $license->created_at }}</span> <br />
            </p>
            @if(count($license->activations) > 0 )
            <div class="toggleGroup nested">
                <a href="" class="toggleBtn"><i class="icon-arrow"></i> Active sites ({{ count( $license->activations ) }}/{{ $license->site_limit }})</a>
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
    </div>
@endforeach
