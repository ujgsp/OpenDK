@extends('layouts.dashboard_template')

@section('content')
    <section class="content-header block-breadcrumb">
        <h1>
            {{ $page_title ?? 'Page Title' }}
            <small>{{ $page_description ?? '' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">{{ $page_title }}</li>
        </ol>
    </section>
    <section class="content container-fluid">

        @include('partials.flash_message')

        <div class="box box-primary">
            <div class="box-header with-border">
                @include('forms.btn-social', ['import_url' => route('data.program-bantuan.import')])
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <label>Desa</label>
                        <select class="form-control" id="list_desa">
                            <option value="">Semua Desa</option>
                            @foreach ($list_desa as $desa)
                                <option value="{{ $desa->desa_id }}">{{ $desa->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover dataTable" id="program-table">
                        <thead>
                            <tr>
                                <th style="max-width: 150px;">Aksi</th>
                                <th>Nama Program</th>
                                <th>Desa</th>
                                <th>Masa Berlaku</th>
                                <th>Sasaran</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('partials.asset_select2')
@include('partials.asset_datatables')

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#list_desa').select2();

            var data = $('#program-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('data.program-bantuan.getdata') !!}",
                    data: function(d) {
                        d.desa = $('#list_desa').val();
                    }
                },

                columns: [{
                        data: 'aksi',
                        name: 'aksi',
                        class: 'text-center',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'desa.nama',
                        name: 'desa'
                    },
                    {
                        data: 'masa_berlaku',
                        name: 'masa_berlaku'
                    },
                    {
                        data: 'sasaran',
                        name: 'sasaran'
                    },
                ],
                order: [
                    [1, 'asc']
                ]
            });
            $('#list_desa').on('select2:select', function(e) {
                data.ajax.reload();
            });
        });
    </script>
@endpush
