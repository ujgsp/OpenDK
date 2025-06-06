<?php

/*
 * File ini bagian dari:
 *
 * OpenDK
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2017 - 2025 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
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
 * @copyright  Hak Cipta 2017 - 2025 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license    http://www.gnu.org/licenses/gpl.html    GPL V3
 * @link       https://github.com/OpenSID/opendk
 */

namespace App\Services;

use App\Enums\LabelStatistik;
use App\Models\Penduduk;
use Illuminate\Support\Facades\DB;

class StatistikChartPendudukPerkawinanService extends BaseApiService
{
    private $colors = [1 => '#d365f8', 2 => '#c534f6', 3 => '#b40aed', 4 => '#8f08bc'];
    public function chart($did, $year)
    {
        if ($this->useDatabaseGabungan()) {
            $data = [];
            try {
                $filters = [
                    'filter[id]' => 'status-perkawinan',
                    'filter[tahun]' => $year,
                    'filter[kecamatan]' => $this->kodeKecamatan,
                ];
                if ($did != 'Semua') {
                    $filters['filter[desa]'] = $did;
                }
                $response = $this->apiRequest('/api/v1/statistik-web/penduduk', $filters);
                foreach ($response as $key => $item) {
                    if (in_array($item['id'], [LabelStatistik::Total, LabelStatistik::Jumlah, LabelStatistik::BelumMengisi])) {
                        continue;
                    }                    
                    $data[] = ['status' => ucfirst(strtolower($item['attributes']['nama'])), 'total' => $item['attributes']['jumlah'], 'color' => $this->colors[$key] ?? '#'.random_color()];
                }
            } catch (\Exception $e) {
                \Log::error('Failed get data in '.__FILE__.' function chart()'. $e->getMessage());
            }
            return $data;
        }
        return $this->localChart($did, $year);
    }

    private function localChart($did, $year)
    {
        $penduduk = new Penduduk();
        // Data Chart Penduduk By Status Perkawinan
        $data = [];
        $statusKawin = DB::table('ref_kawin')->orderBy('id')->get();        
        foreach ($statusKawin as $val) {
            $total = $penduduk->getPendudukAktif($did, $year)
                ->where('status_kawin', $val->id)
                ->count();
            $data[] = ['status' => ucfirst(strtolower($val->nama)), 'total' => $total, 'color' => $this->colors[$val->id]];
        }

        return $data;
    }
}
