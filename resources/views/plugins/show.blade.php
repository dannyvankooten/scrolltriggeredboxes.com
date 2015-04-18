@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')

    @include('parts.masthead')


    <div class="container">

        <div class="breadcrumb" itemprop="breadcrumb">
		<span prefix="v: http://rdf.data-vocabulary.org/#">
			<span typeof="v:Breadcrumb" class="hidden"><a href="/" rel="v:url" property="v:title">Home</a></span> <span class="sep hidden">▸</span>
            <span typeof="v:Breadcrumb"><a href="/plugins" rel="v:url" property="v:title">Plugins</a></span> <span class="sep">▸</span>
            <span typeof="v:Breadcrumb"><span class="breadcrumb_last" property="v:title">{{ $plugin->name }}</span></span>
		</span>
        </div>

        <div class="row">
            <div class="col-md-8">
                <article>
                    @yield('content.primary')
                </article>
            </div>
            <div class="col-md-4">
                <aside role="complementary">
                        <div>
                            <h4>Plugin Name</h4>
                            <p>{{ $plugin->name }}</p>
                        </div>

                        <div>
                            <h4>Requires</h4>
                            <p>Scroll Triggered Boxes v2.0 and PHP v5.3+</p>
                        </div>

                        <div>
                            <h4>Version</h4>
                            <p>{{ $plugin->version }}</p>
                        </div>

                        <div>
                            <h4>Last Updated</h4>
                            <p>{{ $plugin->updated_at->format( 'F, Y' ) }}</p>
                        </div>

                        <div>
                            <h4>Developer</h4>
                            <p>{{ $plugin->author }}<p>
                        </div>

                        @if( $plugin->type === 'premium' )
                        <div class="well">
                            <h4>Premium Plugin</h4>
                            <p>Get instant access to this plugin by <a href="/pricing">purchasing one of the premium plans</a>.</p>
                        </div>
                        @endif

                        @if( $plugin->external_url !== '' )
                        <div class="well">
                            <h4>External Plugin</h4>
                            <p>This plugin can be downloaded for free on an external site.</p>
                            <p><a href="{{ $plugin->external_url }}" class="btn btn-primary">More Info</a></p>
                        </div>
                        @endif
                </aside>
            </div>
        </div>
    </div>
@stop
