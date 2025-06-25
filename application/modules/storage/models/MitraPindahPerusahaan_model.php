<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MitraPindahPerusahaan_model extends Conf{
  public $incrementing = false;

  protected $table = 'mitra_pindah_perusahaan';
  protected $primaryKey = 'id';
}
