<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Jenis_model extends Conf{
  protected $table = 'jenis';
  protected $kodeTable = 'J';

  public function getNextKode(){
    $id = $this->selectRaw("'J'+replace(str(substring(coalesce(max(kode),'0000'),2,4)+1,4), ' ', '0') as nextId")
                ->first();
    return $id->nextId;
  }

  public function getData() {
    return $this->orderBy('nama', 'asc')->get()->toArray();
  }
}
