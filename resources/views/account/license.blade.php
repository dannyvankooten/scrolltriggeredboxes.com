@extends('layouts.master')

@section('title','License - Boxzilla')

@section('content')

    <div class="container">
        <p>
            <a href="/">Account</a> &rightarrow; <span>Licenses</span>
        </p>

        <h1>License: <small><code>{{ $license->license_key }}</code></small></h1>

        <p>This license key is currently activated on the following domains.</p>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Domain</th>
                @if( $license->activation && $license->activation[0]->plugin )<th>Plugin</th>@endif
                <th>Activated on</th>
            </tr>
            </thead>
            @foreach($license->activations as $activation)
            <tr>
                <td><a href="{{ $activation->url }}">{{ $activation->domain }}</a></td>
                @if( $activation->plugin )
                    <td>{{ $activation->plugin->name }}</td>
                    <td>
                        <form action="/licenses/{{ $license->id }}/activations/{{ $activation->id }}" method="post">
                            <input type="hidden" name="_method" value="DELETE" />
                            <input type="submit" class="btn btn-danger" data-confirm="Are you sure you want to deactivate {{ $activation->plugin->name }} on {{ $activation->domain }}?" value="Deactivate" />
                        </form>
                    </td>
                @endif
                <td>{{ $activation->updated_at->format('F j, Y') }}</td>
            </tr>
            @endforeach
            @if( count( $license->activations ) == 0 )
                <tr>
                    <td colspan="2">This license is not activated on any sites.</td>
                </tr>
            @endif
        </table>

        <p><a href="/">Back to your account.</a></p>
    </div>
@stop

@section('foot')
    <script>
        (function() {
            var actions = document.querySelectorAll('[data-confirm]');
            for( var i=0; i<actions.length; i++) {
                actions[i].onclick = function(e) {
                    return confirm(this.getAttribute('data-confirm'));
                }.bind(actions[i])
            }
        })();
    </script>
@stop