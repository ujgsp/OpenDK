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

use App\Exports\ExportPenduduk;
use App\Http\Controllers\Controller;
use App\Imports\ImporPendudukKeluarga;
use App\Models\DataDesa;
use App\Models\Penduduk;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class PendudukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Penduduk $penduduk)
    {
        $page_title = 'Penduduk';
        $page_description = 'Data Penduduk';

        $view = $this->isDatabaseGabungan() ? 'data.penduduk.gabungan.index' : 'data.penduduk.index';
        
        return view($view, compact('page_title', 'page_description'));
    }

    /**
     * Return datatable Data Penduduk.
     *
     * @return DataTables
     */
    public function getPenduduk(Request $request)
    {
        if (request()->ajax()) {
            $desa = $request->input('desa');

            $query = DB::table('das_penduduk')
                ->leftJoin('das_data_desa', 'das_penduduk.desa_id', '=', 'das_data_desa.desa_id')
                ->leftJoin('ref_pendidikan_kk', 'das_penduduk.pendidikan_kk_id', '=', 'ref_pendidikan_kk.id')
                ->leftJoin('ref_kawin', 'das_penduduk.status_kawin', '=', 'ref_kawin.id')
                ->leftJoin('ref_pekerjaan', 'das_penduduk.pekerjaan_id', '=', 'ref_pekerjaan.id')
                ->select([
                    'das_penduduk.id',
                    'das_penduduk.foto',
                    'das_penduduk.nik',
                    'das_penduduk.nama',
                    'das_penduduk.no_kk',
                    'das_penduduk.sex',
                    'das_penduduk.alamat',
                    'das_data_desa.nama as nama_desa',
                    'ref_pendidikan_kk.nama as pendidikan',
                    'das_penduduk.tanggal_lahir',
                    'ref_kawin.nama as status_kawin',
                    'ref_pekerjaan.nama as pekerjaan',
                ])
                ->when($desa, function ($query) use ($desa) {
                    return $desa === 'Semua'
                        ? $query
                        : $query->where('das_data_desa.desa_id', $desa);
                })
                ->where('status_dasar', 1);

            return DataTables::of($query)
                ->addColumn('aksi', function ($row) {
                    $data['show_url'] = route('data.penduduk.show', $row->id);

                    return view('forms.aksi', $data);
                })
                ->addColumn('foto', function ($row) {
                    return '<img src="'.is_user($row->foto, $row->sex).'" class="img-rounded" alt="Foto Penduduk" height="50"/>';
                })
                ->addColumn('tanggal_lahir', function ($row) {
                    return convert_born_date_to_age($row->tanggal_lahir);
                })
                ->rawColumns(['foto'])->make();
        }
    }

    /**
     * Show the specified resource.
     *
     * @param  Penduduk  $penduduk
     * @return Response
     */
    public function show($id)
    {
        $penduduk = Penduduk::findOrFail($id);
        $page_title = 'Detail Penduduk';
        $page_description = 'Detail Data Penduduk: '.ucwords(strtolower($penduduk->nama));

        return view('data.penduduk.show', compact('page_title', 'page_description', 'penduduk'));
    }

    public function detail(Request $request)
    {
        $penduduk = json_decode($request->data);
        $page_title = 'Detail Penduduk';
        $page_description = 'Detail Data Penduduk: '.ucwords(strtolower($penduduk->nama));

        return view('data.penduduk.gabungan.show', compact('page_title', 'page_description', 'penduduk'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function import()
    {
        $page_title = 'Impor';
        $page_description = 'Impor Data Penduduk';
        $list_desa = DataDesa::all();

        return view('data.penduduk.import', compact('page_title', 'page_description', 'list_desa'));
    }

    /**
     * Impor data penduduk dari file Excel.
     * Kalau penduduk sudah ada (berdasarkan NIK), update dengan data yg diimpor
     *
     * @return Response
     */
    public function importExcel(Request $request)
    {
        $this->validate($request, [
            'file' => 'file|mimes:zip|max:51200',
        ]);

        try {
            // Upload file zip temporary.
            $file = $request->file('file');
            $file->storeAs('temp', $name = $file->getClientOriginalName());

            // Temporary path file
            $path = storage_path("app/temp/{$name}");
            $extract = storage_path('app/temp/penduduk/foto/');

            // Ekstrak file
            $zip = new \ZipArchive();
            $zip->open($path);
            $zip->extractTo($extract);
            $zip->close();

            $fileExtracted = glob($extract.'*.xlsx');

            // Proses impor excell
            (new ImporPendudukKeluarga())
                ->queue($extract.basename($fileExtracted[0]));
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Import data gagal. '.$e->getMessage());
        }

        return redirect()->route('data.penduduk.index')->with('success', 'Import data sukses.');
    }

    /**
     * Export data penduduk ke dalam file Excel.
     *
     * @return Response
     */
    public function exportExcel(Request $request)
    {
        $params = $request->all();

        try {
            if ($this->isDatabaseGabungan()) {
                return Excel::download(new ExportPenduduk(true, $params), 'data-penduduk.xlsx');
            } else {
                return Excel::download(new ExportPenduduk(false, $params), 'data-penduduk.xlsx');
            }
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Ekspor data gagal. '.$e->getMessage());
        }
    }
}
