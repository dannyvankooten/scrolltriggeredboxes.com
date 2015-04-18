@extends('plugins.show')

@section('content.primary')
    <div class="plugin">
        <h1>{{ $plugin->name }}</h1>

        @if( '' !== $plugin->long_description )
            {{ $plugin->description }}
        @else
            {{ $plugin->short_description }}
        @endif
@stop

{{--@section('content.nav')--}}
    {{--<a href="#" class="post-prev">Older Post</a>--}}
    {{--<a href="#" class="post-next">Previous Post</a>--}}
{{--@stop--}}

@section('foot')
    <script src="{{ asset('js/third-party/dialog-polyfill.js') }}"></script>
    <dialog id="lightbox">
        <img src="" alt>
        <button class="close">Close</button>
    </dialog>
    <script>

        (function() {
            'use strict';

            function showLightbox(e) {
                e.preventDefault();
                lightboxImage.setAttribute("src", this.getAttribute("href"));
                lightboxImage.setAttribute("alt", this.querySelector("img").getAttribute("alt"));
                lightbox.showModal();

                // make sure dialog is centered
                lightbox.style.marginLeft = -( lightbox.offsetWidth / 2 ) + "px";
                lightbox.style.marginTop = -( lightbox.offsetHeight / 2 ) + "px";
            }

            function hideLightbox() {
                lightboxImage.setAttribute("src", "");
                lightbox.close();
            }

            var lightboxLinks = document.querySelectorAll('a[rel="lightbox"]'),
                    lightbox = document.getElementById('lightbox'),
                    lightboxImage = lightbox.getElementsByTagName('img')[0],
                    closeIcon = lightbox.querySelector('.close'),
                    testDialog;

            // test if dialog is supported
            testDialog = document.createElement('dialog');
            testDialog.setAttribute('open','');
            if(!testDialog.open) {
                dialogPolyfill.registerDialog(lightbox);
            }

            // attach events
            for(var i=0; i<lightboxLinks.length;i++) {
                lightboxLinks[i].onclick = showLightbox;
            }

            closeIcon.onclick = hideLightbox;

        })();
    </script>
@stop