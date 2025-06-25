<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Kandang_model extends Conf{
  public $incrementing = false;
  
  protected $table = 'kandang';
  protected $primaryKey = 'id';

  public function bangunans()
  {
    return $this->hasMany('\Model\Storage\BangunanKandang_model', 'kandang', 'id');
  }

  public function d_unit()
  {
    return $this->hasOne('\Model\Storage\Wilayah_model', 'id', 'unit');
  }

  public function lampirans()
  {
    return $this->hasMany('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', 'kandang')->where('status', 1)->with('d_nama_lampiran');
  }

  public function dKecamatan()
  {
    return $this->hasOne('\Model\Storage\Lokasi_model', 'id', 'alamat_kecamatan')->with('dKota');
  }
}
