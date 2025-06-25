<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Lokasi_model extends Conf{
  protected $table = 'lokasi';
  protected $primaryKey = 'id';

  public function dNegara()
  {
    return $this->hasOne('\Model\Storage\Lokasi_model', 'id', 'induk');
  }

  public function dProvinsi()
  {
    return $this->hasOne('\Model\Storage\Lokasi_model', 'id', 'induk');
    // return $this->hasOne('\Model\Storage\Lokasi', 'id', 'induk')->with('dNegara');
  }

  public function dKota()
  {
    return $this->hasOne('\Model\Storage\Lokasi_model', 'id', 'induk')->with('dProvinsi');
  }

}
