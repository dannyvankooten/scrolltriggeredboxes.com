@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')
    @include('parts.masthead')

    <div class="container marketing">

        <!-- START THE FEATURETTES -->

        <hr class="featurette-divider" style="margin-top: 0; border-top-color: transparent;">

        <div class="row featurette">
            <div class="col-md-5">
                <h2 class="featurette-heading">Attention grabbing boxes. <br /><span class="text-muted">At just the right time.</span></h2>
                <p class="lead">Using Scroll Triggered Boxes, you can ask your visitors to perform a certain action at just the right time.</p>
                <p class="lead">For example, slide in a sign-up form box once a visitor reaches the end of your post. Already engaged visitors are very likely to want more of your goodness.</p>
            </div>
            <div class="col-md-7">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/box.png') }}">
            </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
            <div class="col-md-5 col-md-push-7">
                <h2 class="featurette-heading">Your own call to actions. <span class="text-muted">With ease.</span></h2>
                <p class="lead">Use any content you like for your boxes. Whatever you would like to offer or ask your visitors.</p>
                <p class="lead">Buttons, related posts, forms, even third-party plugin shortcodes. You will be able to see the final result straight from your WordPress visual editor.</p>
            </div>
            <div class="col-md-7 col-md-pull-5">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/editor.png') }}">
            </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
            <div class="col-md-5">
                <h2 class="featurette-heading">Blend in with your theme. <span class="text-muted">Make your boxes pretty.</span></h2>
                <p class="lead">Finish off your boxes with a pretty design sauce, so the box is truly your own.</p>
                <p class="lead">A color here, a border there. A sure way to catch the attention of your visitor.</p>
            </div>
            <div class="col-md-7">
                <img class="featurette-image img-responsive center-block" alt="500x500" src="{{ asset('img/screenshots/appearance-settings.png') }}">
            </div>
        </div>

        <!-- /END THE FEATURETTES -->
    </div>

    <div class="medium-margin"></div>

    @include('parts.cta')

    <div class="container medium-margin">

        <div class="row testimonials">
            <div class="col-lg-12">

                <h3 class="section-title">What Users Are Saying</h3>

                <blockquote class="testimonial">
                    <header>
                        <img class="avatar" src="{{ asset('img/testimonials/signe-dean.png') }}" height="120" width="120" />
                    </header>
                    <p class="copy">
                        I was looking around for a solution to recruit more newsletter subscribers, and knew I didn't want one of those annoying, obtrusive pop-ups, but a corner scroll-triggered box instead. Man, are those things hard to find. Each plugin that claims to offer this functionality has some fatal flaw. Not this one, though.
                        <br /><br />
                        This plugin works perfectly, it is fast, accurate, with just enough customisation, and the shortcode content of my MailPoet newsletter form renders perfectly.
                    </p>
                    <footer>
                        <span class="author-name">Signe Dean</span>, <span class="author-title">freelance science writer & journalist</span>
                    </footer>
                </blockquote>

                <blockquote class="testimonial">
                    <header>
                        <img class="avatar" src="{{ asset('img/testimonials/graeme.jpeg') }}" height="120" width="120" />
                    </header>
                    <p class="copy">
                        Get this plugin quickly. It's responsible for a huge lift in my conversions. Simply brilliant!
                    </p>
                    <footer>
                        <span class="author-name">Graeme Taylor-Warne</span>, <span class="author-title">internet marketer and co-owner of The Online Dog Trainer</span>
                    </footer>
                </blockquote>

                <blockquote class="testimonial">
                    <header>
                        <img class="avatar" src="{{ asset('img/testimonials/jerry-davis.jpg') }}" height="120" width="120" />
                    </header>
                    <p class="copy">
                        I have been testing CTA's for a week. One would think that a solid, simple CTA would be common in the plugin environment, but they are not. After a week, and reduced to selecting products to try by working doggedly through a compiled list, I came to this one. It had 64 five-star ratings, and no other ratings of lesser stature. So I tried it and in less than five minutes I had a superior CTA that met all of my needs and more. I am the 65th five-star, and I am grateful.
                    </p>
                    <footer>
                        <span class="author-name">Jerry Davis</span>, <span class="author-title">Managing Director @ BioTech</span>
                    </footer>
                </blockquote>

                <blockquote class="testimonial">
                    <header>
                        <img class="avatar" src="{{ asset('img/testimonials/vivek.jpeg') }}" height="120" width="120" />
                    </header>
                    <p class="copy">
                        I tested many pop-up plugins, this plugin is the only one that worked easily while not slowing down my site. I highly recommended it!
                    </p>
                    <footer>
                        <span class="author-name">Vivek Srivastava</span>, <span class="author-title">Business Analyst @ Monkshouts</span>
                    </footer>
                </blockquote>

            </div>
        </div><!-- .row -->
    </div><!-- .container -->
@stop
