@extends('layouts.master')
@section('title')
    {{ trans('global.create') }} {{ trans('global.navigator.title_singular') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item "><a href="/">{{ trans('global.home') }}</a></li>
    <li class="breadcrumb-item active">{{ trans('global.create') }} {{ trans('global.navigator.title_singular') }}</li>
    <li class="breadcrumb-item "><a href="/documentation" class="text-black-50"><i class="fas fa-question-circle"></i></a></li>
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ route("navigators.store") }}" 
              method="POST" 
              enctype="multipart/form-data">
            @include ('navigators.form', [
                'navigator' => new App\Navigator,
                'buttonText' => trans('global.navigator.create')
            ])
        </form>
    </div>
</div>
@endsection