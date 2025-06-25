<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MitraRekeningKoran_model extends Conf{
  protected $table = 'mitra_rekening_koran';
  protected $primaryKey = 'id';
  protected $status = 'status';


  public function perwakilan()
  {
    return $this->hasOne('\Model\Storage\MitraMapping_model', 'id', 'mitra_mapping')->with(['dMitra', 'dPerwakilan']);
  }

  public function lampiran()
  {
    return $this->hasOne('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', $this->table)->orderBy('id', 'DESC');
  }

  public function kandang()
  {
    return $this->hasOne('\Model\Storage\Kandang_model', 'id', 'kandang_id');
  }
}
