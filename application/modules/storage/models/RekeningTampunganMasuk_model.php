<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RekeningTampunganMasuk_model extends Conf{
    protected $table = 'rekening_tampungan_masuk';
    protected $primaryKey = 'kode';
    protected $kodeTable = 'RTM';

    public function d_perusahaan()
    {
        return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'desc');
    }
}
