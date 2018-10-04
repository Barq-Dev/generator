<div class="form-group row">
    {!! Form::label($field['name'], $field['title'], $field['options-label'] ) !!}
    <div class="col-10">
        {!! Form::password($field['name'], $field['options']) !!}
    </div>
</div>