@extends('layouts.admin')

@section('title','New License - Boxzilla')

@section('content')

    <div class="container">

        <div class="breadcrumbs bordered medium-padding small-margin">
            <a href="/users">Users</a> &rightarrow;
            Create User
        </div>

        <h1>Create User</h1>

        <form method="post" action="/users">
            {!! csrf_field() !!}

            <div class="form-group">
                <label>Email address <span class="big red">*</span></label>

                <div class="form-element">
                    <input type="email" name="user[email]" value="" placeholder="Email address.." required>
                    <i class="fa fa-at form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Name <span class="big red">*</span></label>

                <div class="form-element">
                    <input type="text" name="user[name]" value="" placeholder="Name.." required>
                    <i class="fa fa-user form-element-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-group">
                <label>Country <span class="big red">*</span></label>
                <select name="user[country]" class="country-input" required>
                    <option value="" disabled {{ old('user.country','') === '' ? 'selected' : '' }}>Select country..</option>
                    <option value="US" >United States</option>
                    <option value="GB" >United Kingdom</option>
                    @foreach(Countries::all() as $code => $country)
                        <option value="{{ $code }}">{{ $country }}</option>
                    @endforeach
                </select>
                <p class="help">We need to know the user country for taxes.</p>
            </div>

            <div class="row clearfix">
                <div class="col col-3">
                    <div class="form-group">
                        <label>Password <span class="big red">*</span></label>

                        <div class="form-element">
                            <input type="password" name="password" value=""  placeholder="Password.." required minlength="6">
                            <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
                <div class="col col-3">
                    <div class="form-group">
                        <label>Confirm password <span class="big red">*</span></label>

                        <div class="form-element">
                            <input type="password" name="password_confirmation" value="" placeholder="Repeat password.." required minlength="6">
                            <i class="fa fa-lock form-element-icon" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="Create" />
            </div>

        </form>

        <p><a href="javascript:history.go(-1);">&leftarrow; Go back.</a></p>

    </div>
@stop
