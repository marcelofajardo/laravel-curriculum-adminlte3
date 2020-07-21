@extends('layouts.master')
@section('title')
    {{ trans('global.plan.title') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item "><a href="/">{{ trans('global.home') }}</a></li>
    <li class="breadcrumb-item active">{{ trans('global.plan.title') }}</li>
    <li class="breadcrumb-item "><a href="/documentation" class="text-black-50"><i class="fas fa-question-circle"></i></a></li>
@endsection
@section('content')
@can('plan_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a 
                id="add-plan"
                class="btn btn-success" 
                href="{{ route("plans.create") }}">
                {{ trans('global.plan.create') }}    
            </a>
        </div>
    </div>
@endcan
<table id="plans-datatable" class="table table-hover datatable">
    <thead>
        <tr>
            <th></th>
            <th>{{ trans('global.plan.fields.title') }}</th>
            <th>{{ trans('global.datatables.action') }}</th>
        </tr>
    </thead>
</table>

<plan-modal></plan-modal>


@endsection
@section('scripts')
@parent
<script>
$(document).ready( function () {
    
    let dtButtons = '';
    @can('plan_delete')
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
        let deleteButton = {
          text: deleteButtonTrans,
          url: "{{ route('plans.massDestroy') }}",
          className: 'btn-danger',
          action: function (e, dt, node, config) {
            var ids = dt.rows({ selected: true }).ids().toArray()

            if (ids.length === 0) {
              alert('{{ trans('global.datatables.zero_selected') }}')
              return
            }

            if (confirm('{{ trans('global.areYouSure') }}')) {
              $.ajax({
                headers: {'x-csrf-token': _token},
                method: 'POST',
                url: config.url,
                data: { ids: ids, _method: 'DELETE' }})
                .done(function () { location.reload() })
            }
          }
        }
        dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
        dtButtons.push(deleteButton)
    @endcan
    
    
    var table = $('#plans-datatable').DataTable({
        ajax: "{{ url('plans/list') }}",
        columns: [
                 { data: 'check'},
                 { data: 'title' },
                 { data: 'street' },
                 { data: 'postcode' },
                 { data: 'city' },
                 { data: 'status' },
                 { data: 'action' }
                ],
        columnDefs: [
            { "visible": false, "targets": 0 },
            {
                orderable: false,
                searchable: false,
                targets: - 1
            }
        ],
        buttons: dtButtons
    });
    table.on( 'select', function ( e, dt, type, indexes ) { //on select event
        window.location.href = "/plans/" + table.row({ selected: true }).data().id ;
    });
 });
</script>

@endsection