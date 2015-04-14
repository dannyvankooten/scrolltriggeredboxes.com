@extends('layouts.master')

@section('title','Scroll Triggered Boxes - unobtrusive conversion boosters')

@section('content')
<!--Portfolio start-->
<div class="container bodyContent grid-wrap">
    <ul class="list-unstyled portfolioList grid swipe-rotate" id="grid">
        <li>
            <div class="listLink">
                <div class="portfolioSlide"><img src="http://placehold.it/300x400&text=image1" class="img-responsive mainImage" alt=""></div>
                <div class="portfolioContent">
                    <h3 class="font-openBold"><a href="{{ url('/plugins/theme-pack') }}">Theme Pack</a> <span>Starting at $29</span></h3>
                </div>
            </div>
        </li>
        <li>
            <div class="listLink">
                <div class="portfolioSlide"><img src="http://placehold.it/300x400&text=image1" class="img-responsive mainImage" alt=""></div>
                <div class="portfolioContent">
                    <h3 class="font-openBold"><a href="{{ url('/plugins/mailchimp') }}">MailChimp Sign-Up</a> <span>Free</span></h3>
                </div>
            </div>
        </li>
        <li>
            <div class="listLink">
                <div class="portfolioSlide"><img src="http://placehold.it/300x400&text=image1" class="img-responsive mainImage" alt=""></div>
                <div class="portfolioContent">
                    <h3 class="font-openBold"><a href="{{ url('/plugins/related-posts') }}">Related Posts</a> <span>Free</span></h3>
                </div>
            </div>
        </li>
    </ul>
    <!-- <a href="{{ url('/add-ons') }}" class="vc">View all plugins</a> </div> -->
<!--Portfolio end-->
</div>
@stop
