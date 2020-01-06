@extends('layouts.master')
@section('title')
    {{ trans('global.period.create') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item "><a href="/">{{ trans('global.home') }}</a></li>
    <li class="breadcrumb-item active">{{ trans('global.period.create') }}</li>
    <li class="breadcrumb-item "><a href="/documentation" class="text-black-50"><i class="fas fa-question-circle"></i></a></li>
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ route("periods.store") }}" 
              method="POST" 
              enctype="multipart/form-data">
            @include ('periods.form', [
                'period' => new App\Period,
                'buttonText' => trans('global.period.create')
            ])
        </form>
    </div>
</div>
@endsection