
<p>
    <a href="{{ domain_url( '/users/' . $user->id, 'admin' ) }}">{{ $user->name }}</a><br /><br />
    <span class="muted">User since {{ $user->created_at->format( 'j M Y') }}.</span><br />
    <strong>Lifetime Value:</strong> ${{ $user->getLifetimeValue() }}<br />
</p>

<ul class="unstyled">
@foreach($user->licenses as $license)
    <li>
        <p>
            <a href="{{ domain_url( '/licenses/' . $license->id, 'admin' ) }}">{{$license->license_key}}</a><br />
            <span class="muted">{{ $license->created_at->format( 'j M Y H:i' ) }}</span><br />
            @if($license->isExpired())
                <span style="color:orange; font-weight:bold;">expired</span><br />
            @endif
        </p>

        @if(count($license->activations) > 0 )
        <div class="toggleGroup">
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
    </li>
@endforeach
</ul>
