<div class="form-group row">
    {!! Form::label($field['name'], $field['title'], $field['options-label'] ) !!}
    <div class="col-10">
        {!! Form::text($field['name'], $field['value'] ?? null, $field['options']) !!}
    </div>
</div>