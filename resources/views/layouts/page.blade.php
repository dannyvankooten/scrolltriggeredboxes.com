@extends('layouts.master')

@section('content')
    @include('parts.masthead')

    <div class="container content">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                @yield('content.main')
            </div>
        </div>
    </div>
@endsection

