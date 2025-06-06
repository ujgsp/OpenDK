<?php

/*
 * File ini bagian dari:
 *
 * OpenDK
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2017 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package    OpenDK
 * @author     Tim Pengembang OpenDesa
 * @copyright  Hak Cipta 2017 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license    http://www.gnu.org/licenses/gpl.html    GPL V3
 * @link       https://github.com/OpenSID/opendk
 */

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Services\KeluargaService;
use Illuminate\Http\Response;
use Yajra\DataTables\DataTables;

class KeluargaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page_title = 'Keluarga';
        $page_description = 'Daftar Keluarga';
        $view = $this->isDatabaseGabungan() ? 'data.keluarga.gabungan.index' : 'data.keluarga.index';

        return view($view, compact('page_title', 'page_description'));
    }

    /**
     * Return datatable Data Keluarga
     */
    public function getKeluarga()
    {
        if (request()->ajax()) {
            $desa = request('desa');

            return DataTables::of(Keluarga::has('kepala_kk')
                ->when($desa, function ($query) use ($desa) {
                    return $desa === 'Semua'
                        ? $query
                        : $query->where('das_data_desa.desa_id', $desa);
                })
                ->get())
                ->addColumn('aksi', function ($row) {
                    $data['show_url'] = route('data.keluarga.show', $row->id);

                    return view('forms.aksi', $data);
                })
                ->addColumn('foto', function ($row) {
                    return '<img src="'.is_user($row->kepala_kk->foto, $row->kepala_kk->sex).'" class="img-rounded" alt="Foto Penduduk" height="50"/>';
                })->editColumn('tgl_cetak_kk', function ($row) {
                    return format_datetime($row->tgl_cetak_kk);
                })
                ->rawColumns(['foto'])
                ->make();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $page_title = 'Detail Keluarga';
        $page_description = 'Detail Data Keluarga';
        $penduduk = $this->isDatabaseGabungan() ? (new KeluargaService)->keluarga($id) : Penduduk::select(['nik', 'nama'])->get();
        $keluarga = $this->isDatabaseGabungan() ? (new KeluargaService)->keluarga($id) : Keluarga::findOrFail($id);

        $view = $this->isDatabaseGabungan() ? 'data.keluarga.gabungan.show' : 'data.keluarga.show';

        return view($view, compact('page_title', 'page_description', 'penduduk', 'keluarga'));
    }
}
