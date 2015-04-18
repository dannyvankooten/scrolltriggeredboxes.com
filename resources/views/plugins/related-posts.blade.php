@extends('plugins.show')

@section('content.primary')

    <h1 class="page-title">Related Posts</h1>

    <div class="projectImage">
        <img src="{{ asset('images/plugins/theme-pack-banner.jpg') }}" class="img-responsive postImage" alt="">
    </div>

    <p>This plugin adds several beautiful preset themes which you can choose from to your Scroll Triggered Boxes.</p>
    <p>You can pick a theme, immediately see the result in your editor and then further customise the colors to your own liking, so you can truly make it your own.</p>
    <p>The plugin comes with the following themes.</p>

    <nav class="thumbs">
        <a href="{{ asset('images/plugins/theme-pack/horizontal-stripe.jpg') }}" rel="lightbox"><img src="{{ asset('images/plugins/theme-pack/horizontal-stripe.jpg') }}" alt><span class="sr-only">Horizontal Stripe</span></a>
        <a href="{{ asset('images/plugins/theme-pack/paper.jpg') }}" rel="lightbox"><img src="{{ asset('images/plugins/theme-pack/paper.jpg') }}" alt><span class="sr-only">Paper</span></a>
        <a href="{{ asset('images/plugins/theme-pack/polkadot.jpg') }}" rel="lightbox"><img src="{{ asset('images/plugins/theme-pack/polkadot.jpg') }}" alt><span class="sr-only">Polkadot</span></a>
        <a href="{{ asset('images/plugins/theme-pack/top-color.jpg') }}" rel="lightbox"><img src="{{ asset('images/plugins/theme-pack/top-color.jpg') }}" alt><span class="sr-only">Top Color</span></a>
        <a href="{{ asset('images/plugins/theme-pack/top-shadow.jpg') }}" rel="lightbox"><img src="{{ asset('images/plugins/theme-pack/top-shadow.jpg') }}" alt><span class="sr-only">Top Shadow</span></a>
        <a href="{{ asset('images/plugins/theme-pack/striped-border.jpg') }}" rel="lightbox"><img src="{{ asset('images/plugins/theme-pack/striped-border.jpg') }}" alt><span class="sr-only">Striped Border</span></a>
    </nav><br />

    <p>The themes shown above are just a few of the many possibilities, since the colors in the themes can be fully customised to your liking.</p>
    <p><img src="{{ asset('images/plugins/theme-pack/option-panel.jpg') }}" alt="" /></p>

    <p>Using this theme pack you can easily create beautiful eye-catching boxes without needing complex CSS styling rules.</p>
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