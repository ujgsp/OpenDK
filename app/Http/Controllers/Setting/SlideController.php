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

namespace App\Http\Controllers\Setting;

use App\Models\Slide;
use Yajra\DataTables\DataTables;
use App\Traits\HandlesFileUpload;
use App\Http\Requests\SlideRequest;
use App\Http\Controllers\Controller;

class SlideController extends Controller
{
    use HandlesFileUpload;

    public function index()
    {
        $page_title = 'Slide';
        $page_description = 'Daftar Slide';

        return view('setting.slide.index', compact('page_title', 'page_description'));
    }

    public function getData()
    {
        return DataTables::of(Slide::all())
            ->addColumn('aksi', function ($row) {
                $data['edit_url'] = route('setting.slide.edit', $row->id);
                $data['delete_url'] = route('setting.slide.destroy', $row->id);

                return view('forms.aksi', $data);
            })
            ->editColumn('judul', function ($row) {
                return $row->judul;
            })->make();
    }

    public function create()
    {
        $slide = null;
        $page_title = 'Slide';
        $page_description = 'Tambah Slide';

        return view('setting.slide.create', compact('page_title', 'page_description', 'slide'));
    }

    public function store(SlideRequest $request)
    {
        try {
            $input = $request->validated();
            $this->handleFileUpload($request, $input, 'gambar', 'slide');

            Slide::create($input);
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Slide gagal ditambah!');
        }

        return redirect()->route('setting.slide.index')->with('success', 'Slide berhasil ditambah!');
    }

    public function show(Slide $slide)
    {
        $page_title = 'Detail Slide :' . $slide->judul;

        return view('setting.slide.show', compact('page_title', 'page_description', 'slide'));
    }

    public function edit(Slide $slide)
    {
        $page_title = 'Slide';
        $page_description = 'Ubah Slide : ' . $slide->judul;

        return view('setting.slide.edit', compact('page_title', 'page_description', 'slide'));
    }

    public function update(SlideRequest $request, Slide $slide)
    {
        try {
            $input = $request->validated();
            $this->handleFileUpload($request, $input, 'gambar', 'slide');

            $slide->update($input);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Data Slide gagal disimpan!');
        }

        return redirect()->route('setting.slide.index')->with('success', 'Data Slide berhasil disimpan!');
    }

    public function destroy(Slide $slide)
    {
        try {
            $slide->delete();
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Slide gagal dihapus!');
        }

        return redirect()->route('setting.slide.index')->with('success', 'Slide berhasil dihapus!');
    }
}
