<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Wilayah_model extends Conf{
  protected $table = 'wilayah';
  protected $primaryKey = 'id';

  public function scopePerwakilan($query)
  {
    return $query->where('jenis','PW');
  }
  public function unit()
  {
    return $this->hasMany('\Model\Storage\Wilayah_model','induk', 'id');
  }

  public function mitra_mapping()
  {
    return $this->hasMany('\Model\Storage\MitraMapping_model','perwakilan', 'id')->with(['dMitra']);
  }

  public function hitung_budidaya()
  {
    return $this->hasMany('\Model\Storage\HitungBudidaya_model','perwakilan', 'id')->where('g_status', 3)->with(['details']);
  }

  public function dPerwakilanMapping()
  {
    return $this->hasMany('\Model\Storage\PerwakilanMaping_model','id_pwk', 'id')->with(['dHitungBudidayaItem']);
  }
}
