<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TipePelanggan_model extends Conf{
  protected $table = 'tipe_pelanggan';

  public function getData() {
    return $this->orderBy('nama', 'asc')->get()->toArray();
  }
}
