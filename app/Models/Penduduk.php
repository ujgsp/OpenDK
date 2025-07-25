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

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penduduk extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $table = 'das_penduduk';

    protected $fillable = [];

    protected $guarded = [];

    /**
     * Relation Methods
     * */
    public function getPendudukAktif($did, $year)
    {
        $penduduk = $this
            ->where('status_dasar', 1)
            ->whereYear('created_at', '<=', $year);

        if ($did != 'Semua') {
            $penduduk->where('desa_id', $did);
        }

        return $penduduk;
    }

    public function scopeHidup($query)
    {
        return $query->where('status_dasar', 1);
    }

    public function pekerjaan()
    {
        return $this->hasOne(Pekerjaan::class, 'id', 'pekerjaan_id');
    }

    public function kawin()
    {
        return $this->hasOne(Kawin::class, 'id', 'status_kawin');
    }

    public function pendidikan_kk()
    {
        return $this->hasOne(PendidikanKK::class, 'id', 'pendidikan_kk_id');
    }

    public function keluarga()
    {
        return $this->hasOne(Keluarga::class, 'no_kk', 'no_kk');
    }

    public function suplemen_terdata()
    {
        return $this->hasMany(SuplemenTerdata::class, 'penduduk_id', 'id');
    }

    public function desa()
    {
        return $this->hasOne(DataDesa::class, 'desa_id', 'desa_id');
    }

    // Relasi ke DasLembaga
    public function lembaga()
    {
        return $this->hasOne(Lembaga::class, 'penduduk_id');
    }

    // Relasi ke model LembagaAnggota (One-to-Many)
    public function lembagaAnggota()
    {
        return $this->hasMany(LembagaAnggota::class, 'penduduk_id');
    }

    // Definisikan relasi belongsTo ke das_penduduk_sex tanpa model
    public function pendudukSex()
    {
        return $this->belongsTo(PendudukSex::class, 'sex', 'id');
    }
}
