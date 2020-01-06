@extends('layouts.master')
@section('title')
    {{ trans('global.period.edit') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item "><a href="/">{{ trans('global.home') }}</a></li>
    <li class="breadcrumb-item active">{{ trans('global.period.edit') }}</li>
    <li class="breadcrumb-item "><a href="/documentation" class="text-black-50"><i class="fas fa-question-circle"></i></a></li>
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ $period->path() }}" 
              method="POST" 
              enctype="multipart/form-data">
            @method('PATCH')
            @include('periods.form', [
                'period'        => $period,
                'organizations' => $organizations,
                'buttonText'    => trans('global.period.edit')
            ])
        </form>
    </div>
</div>

@endsection