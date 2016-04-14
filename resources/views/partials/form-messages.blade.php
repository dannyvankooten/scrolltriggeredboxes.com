@if (count($errors) > 0)
    <div class="notice notice-warning">
        <strong>Whoops!</strong> We had some trouble processing your input.<br />
        <ul>
            @foreach ($errors->all() as $error)
                <li>{!! $error !!} </li>
            @endforeach
        </ul>
    </div>
@endif
