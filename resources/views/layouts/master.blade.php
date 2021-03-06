<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ trans('global.site_title') }}</title>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
        <script src="{{ asset('node_modules/tinymce/tinymce.min.js') }}"></script>
        @yield('styles')
        <script>
            window.trans = <?php
// copy all translations from /resources/lang/CURRENT_LOCALE/* to global JS variable
$lang_files = File::files(resource_path() . '/lang/' . App::getLocale());
$trans = [];
foreach ($lang_files as $f) {
    $filename = pathinfo($f)['filename'];
    $trans[$filename] = trans($filename);
}
echo json_encode($trans);
?>;
            window.Laravel = <?php
echo json_encode([
    'csrfToken' => csrf_token(),
    'userId' => Auth::user()->id,
    'permissions' => Auth::user()->permissions()->pluck('title')->toArray()
]);
?>;
        </script>
    </head>
    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
        <!-- Site wrapper -->
        <div id="app" class="wrapper">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-lime navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                    </li>
<!--                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="#" class="nav-link">Contact</a>
                    </li>-->
                </ul>

                <!-- SEARCH FORM -->
<!--                <form class="form-inline ml-3">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>-->

                <!-- Right navbar links -->
                @include('partials.navbar')
            </nav>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            @include('partials.menu')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-8 pl-0">
                                <h1> @yield('title')</h1>
                            </div>
                            <div class="col-sm-4 pr-0">
                                <ol class="breadcrumb float-sm-right">
                                    @yield('breadcrumb')
                                </ol>
                            </div>
                        </div>
                    </div><!-- /.container-fluid -->
                </section>

                <!-- Main content -->
                <section class="content">
                    @yield('content')
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            <!-- Footer -->
            @include('partials.footer')
            <!-- Logout Form -->
            <form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>

            <!-- Control Sidebar -->
            <aside class="control-sidebar control-sidebar-dark">
                <!-- Control sidebar content goes here -->
            </aside>
            <!-- /.control-sidebar -->
        </div>
        <!-- ./wrapper -->

        <script src="{{ asset('js/app.js') }}"></script>    
        <script src="{{ asset('node_modules/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('node_modules/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('node_modules/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
        <script src="{{ asset('node_modules/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('node_modules/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('node_modules/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
        <script src="{{ asset('node_modules/datatables.net-select/js/dataTables.select.min.js') }}"></script>
        <script src="{{ asset('node_modules/moment/js/moment.min.js') }}"></script>
        <script src="{{ asset('node_modules/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>   
        <script>
            $(function() {
//                let copyButtonTrans     = '{{ trans('global.datatables.copy') }}'
//                let csvButtonTrans      = '{{ trans('global.datatables.csv') }}'
//                let excelButtonTrans    = '{{ trans('global.datatables.excel') }}'
//                let pdfButtonTrans      = '{{ trans('global.datatables.pdf') }}'
//                let printButtonTrans    = '{{ trans('global.datatables.print') }}'
//                let colvisButtonTrans   = '{{ trans('global.datatables.colvis') }}'
                let languages = {
                    'de': '{{ asset("datatables/i18n/German.json") }}',
                    'en': '{{ asset("datatables/i18n/English.json") }}',
                    'fr': '{{ asset("datatables/i18n/French.json") }}', 
                };     
                $.extend(true, $.fn.dataTable.Buttons.defaults.dom.button, { className: 'btn-primary py-2 ml-1' })
                
                    $.extend(true, $.fn.dataTable.defaults, {
                    processing: true,
                    serverSide: true,
                    language: {
                        url: languages.{{ app() -> getLocale() }},
                        searchPlaceholder: '{{ trans('global.search') }}',
                        paginate: {
                            "first":      '<i class="fa fa-angle-double-left"></id>',
                            "last":       '<i class="fa fa-angle-double-right"></id>',
                            "next":       '<i class="fa fa-angle-right"></id>',
                            "previous":   '<i class="fa fa-angle-left"></id>',
                        },
                    },
                    columnDefs: [ 
                        {
                            orderable: false,
                            className: 'select-checkbox',
                            targets:   0
                        },
                        {
                            orderable: false,
                            searchable: false,
                            targets: - 1
                        }],
                    select: {
                         style:  'multi+shift',
                         selector: 'td:not(:last-child)'
                    },
                    order: [[ 1, 'asc' ]],
                    scrollX: true,
                    pageLength: 50,
                    pagingType: "full_numbers",
                    dom: '<"top"f>rt<"bottom"pl>i',
                    buttons: []
                    });
               $.fn.dataTable.ext.classes.sPageButton = '';
               });
               
               function setCurrentOrganizationAndPeriod(selectBox){
                    var op = selectBox.options[selectBox.selectedIndex];
                    var optgroup = op.parentNode;
                    $.ajax({
                        headers: {'x-csrf-token': _token},
                        method: 'POST',
                        url: "{{ route('users.setCurrentOrganizationAndPeriod') }}",
                        data: {
                            current_period_id: op.value,
                            current_organization_id: optgroup.id,
                            _method: 'PATCH',
                        }
                    })
                    .done(function () { location.reload() })
               }
               
               function destroyDataTableEntry(route, id){
                    if (confirm('{{ trans('global.areYouSure') }}')) {    
                        $.ajax({
                            headers: {'x-csrf-token': _token},
                            method: 'POST',
                            url: route+'/'+id,
                            data: { _method: 'DELETE' }
                        })
                        .done(function () { 
                            $("#"+id).hide();
                        });
                    }
                }
        </script>
        
        @yield('scripts')
    </body>
</html>
