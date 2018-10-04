{!! Form::open(['url'=> url()->current()  ,'method'=>'get','class'=>'form','id' => 'form-search-container']) !!}
@php
$col = ceil(12/(count($input)));
@endphp
@foreach (collect($input)->reduce(function($out,$item){return $out->push(collect($item));},collect())->sortBy('place') as $item)
<div class="col-md-{{$loop->last  ? $col -1 : $col }}">
  @if ($item->get('type') == 'select')
  {!! Form::select($item->get('name'),$item->has('items') ? $item->get('items') : [], null, ['data-placeholder'=>$item->get('placeholder'),'id'=>$item->get('name') ]) !!}
  @endif
  @if ($item->get('type') == 'input')
  {!! Form::text($item->get('name'), null, ['placeholder'=>$item->get('placeholder'),'class'=>'form-control','id'=>'label' ]) !!}
  @endif
</div>
@endforeach
<div class="col-md-1">
  <button class="btn btn-primary" name="search" value="true" type="submit"><i class="fa fa-search"></i>@lang('generator.search.submit')</button>
</div>
{!! Form::close() !!}