{!! Form::open(['url'=> url()->current()  ,'method'=>'get','class'=>'form','id' => 'form-search-container']) !!}
@foreach ($inputs as $input)
<div class="col-md-{{$loop->last  ? $col -1 : $col }}">
    {!! $input !!}
</div>
@endforeach
<div class="col-md-1">
  <button class="btn btn-primary" name="search" value="true" type="submit"><i class="{{config('generator.view.search.submit.icon')}}"></i>@lang('generator.search.submit.text')</button>
</div>
{!! Form::close() !!}