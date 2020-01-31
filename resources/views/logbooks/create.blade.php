@extends('layouts.master')
@section('title')
    {{ trans('global.logbook.create') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item "><a href="/">{{ trans('global.home') }}</a></li>
    <li class="breadcrumb-item active">{{ trans('global.logbook.create') }}</li>
    <li class="breadcrumb-item "><a href="/documentation" class="text-black-50"><i class="fas fa-question-circle"></i></a></li>
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ route("logbooks.store") }}" 
              method="POST" 
              enctype="multipart/form-data">
            @include ('logbooks.form', [
                'logbook' => new App\Logbook,
                'buttonText' => trans('global.logbook.create')
            ])
        </form>
    </div>
</div>
@endsection