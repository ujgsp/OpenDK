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

namespace App\Http\Controllers\Informasi;

use App\Models\Prosedur;
use Yajra\DataTables\DataTables;
use App\Traits\HandlesFileUpload;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProsedurRequest;

class ProsedurController extends Controller
{
    use HandlesFileUpload;

    public function index()
    {
        $page_title = 'Prosedur';
        $page_description = 'Daftar Prosedur';

        return view('informasi.prosedur.index', compact('page_title', 'page_description'));
    }

    public function getDataProsedur()
    {
        return DataTables::of(Prosedur::select('id', 'judul_prosedur'))
            ->addColumn('aksi', function ($row) {
                $data['show_url'] = route('informasi.prosedur.show', $row->id);

                if (! auth()->guest()) {
                    $data['edit_url'] = route('informasi.prosedur.edit', $row->id);
                    $data['delete_url'] = route('informasi.prosedur.destroy', $row->id);
                }

                $data['download_url'] = route('informasi.prosedur.download', $row->id);

                return view('forms.aksi', $data);
            })
            ->editColumn('judul_prosedur', function ($row) {
                return $row->judul_prosedur;
            })->make();
    }

    public function create()
    {
        $page_title = 'Prosedur';
        $page_description = 'Tambah Prosedur';

        return view('informasi.prosedur.create', compact('page_title', 'page_description'));
    }

    public function store(ProsedurRequest $request)
    {
        try {
            $input = $request->all();
            $input['slug'] = str_slug($request->input('judul_prosedur'));
            $this->handleFileUpload($request, $input, 'file_prosedur', 'regulasi');

            $input['mime_type'] = $request->file('file_prosedur')->getClientMimeType();
            Prosedur::create($input);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Prosedur gagal disimpan!');
        }

        return redirect()->route('informasi.prosedur.index')->with('success', 'Prosedur berhasil disimpan!');
    }

    public function show(Prosedur $prosedur)
    {
        $page_title = 'Prosedur';
        $page_description = 'Detail Prosedur : '.$prosedur->judul_prosedur;

        return view('informasi.prosedur.show', compact('page_title', 'page_description', 'prosedur'));
    }

    public function edit(Prosedur $prosedur)
    {
        $page_title = 'Prosedur';
        $page_description = 'Ubah Prosedur : '.$prosedur->judul_prosedur;

        return view('informasi.prosedur.edit', compact('page_title', 'page_description', 'prosedur'));
    }

    public function update(Prosedur $prosedur, ProsedurRequest $request)
    {
        try {
            $input = $request->all();
            $this->handleFileUpload($request, $input, 'file_prosedur', 'regulasi');

            if ($request->hasFile('file_prosedur')) {
                $input['mime_type'] = $request->file('file_prosedur')->getClientMimeType();
            }
            
            $prosedur->update($input);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Prosedur gagal disimpan!');
        }

        return redirect()->route('informasi.prosedur.index')->with('success', 'Prosedur berhasil disimpan!');
    }

    public function destroy(Prosedur $prosedur)
    {
        try {
            $prosedur->delete();
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Prosedur gagal dihapus!');
        }

        return redirect()->route('informasi.prosedur.index')->with('success', 'Prosedur berhasil disimpan!');
    }

    public function download(Prosedur $prosedur)
    {
        try {
            return response()->download($prosedur->file_prosedur);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Dokumen prosedur tidak ditemukan');
        }
    }
}
