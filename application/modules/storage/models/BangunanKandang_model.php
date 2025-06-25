<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class BangunanKandang_model extends Conf{
  public $incrementing = false;
  
  protected $table = 'bangunan_kandang';
  protected $primaryKey = 'id';
}
