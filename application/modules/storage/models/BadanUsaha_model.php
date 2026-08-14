<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class BadanUsaha_model extends Conf {
  public $incrementing = false;
  protected $keyType = 'string';

  protected $table = 'master_badan_usaha';
  protected $primaryKey = 'id_badan_usaha';

  public function getNextKode(){
    $id = $this->selectRaw("'BU'+replace(str(substring(coalesce(max(id_badan_usaha),'BU00'),3,2)+1,2), ' ', '0') as nextId")
                ->first();
    return $id->nextId;
  }

  public function getData() {
    return $this->orderBy('nama_badan_usaha', 'asc')->get()->toArray();
  }
}
