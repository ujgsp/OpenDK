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
                @include('forms.btn-social', ['import_url' => route('data.fasilitas-paud.import')])
            </div>
            <div class="box-body">
                @include('layouts.fragments.list-desa')
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover dataTable" id="fasilitas-table">
                        <thead>
                            <tr>
                                <th style="max-width: 100px;">Aksi</th>
                                <th>Desa</th>
                                <th>Jumlah PAUD/RA</th>
                                <th>Jumlah Guru PAUD/RA</th>
                                <th>Jumlah Siswa PAUD/RA</th>
                                <th>Semester</th>
                                <th>Tahun</th>
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
            var data = $('#fasilitas-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! route('data.fasilitas-paud.getdata') !!}",
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
                        data: 'desa.nama',
                        name: 'desa.nama'
                    },
                    {
                        data: 'jumlah_paud',
                        name: 'jumlah_paud'
                    },
                    {
                        data: 'jumlah_guru_paud',
                        name: 'jumlah_guru_paud'
                    },
                    {
                        data: 'jumlah_siswa_paud',
                        name: 'jumlah_siswa_paud'
                    },
                    {
                        data: 'semester',
                        name: 'semester'
                    },
                    {
                        data: 'tahun',
                        name: 'tahun'
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
    @include('forms.datatable-vertical')
    @include('forms.delete-modal')
@endpush
