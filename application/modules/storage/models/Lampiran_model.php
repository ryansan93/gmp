<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Lampiran_model extends Conf{
  protected $table = 'lampiran';
  protected $primaryKey = 'id';

  public function d_nama_lampiran()
  {
    return $this->hasOne('\Model\Storage\NamaLampiran_model', 'id', 'nama_lampiran');
  }
}
