<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RekeningTampunganKeluar_model extends Conf{
    protected $table = 'rekening_tampungan_keluar';
    protected $primaryKey = 'kode';
    protected $kodeTable = 'RTK';

    public function d_perusahaan()
    {
        return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'desc');
    }

    public function d_pelanggan()
    {
        return $this->hasOne('\Model\Storage\Pelanggan_model', 'nomor', 'no_pelanggan')->where('tipe', 'pelanggan')->where('mstatus', 1)->orderBy('id', 'desc');
    }
}
