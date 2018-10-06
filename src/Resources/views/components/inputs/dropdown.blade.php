<div class="form-group row">
    {!! Form::label($name, $title, $attributes_label) !!}
    <div class="col-10">
        {!! Form::select($name, $options,$value, $attributes) !!}
    </div>
</div>