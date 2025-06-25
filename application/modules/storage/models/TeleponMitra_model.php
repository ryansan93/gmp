<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TeleponMitra_model extends Conf{
  protected $table = 'telepon_mitra';
  protected $primaryKey = 'id';
  public $incrementing = false;
}
