<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetJurnalTrans_model extends Conf{
    // public $incrementing = false;

    protected $table = 'det_jurnal_trans';
    protected $primaryKey = 'id';

    public function getNextIdDJT( $kode ){
        $id = $this->whereRaw("SUBSTRING(kode,0,".(strlen( $kode )+1).") = '".$kode."'")
                    ->selectRaw("'".$kode."'+'-'+replace(str(substring(coalesce(max(kode),'000'),12,3)+1,3), ' ', '0') as nextId")
                    ->first();
        return $id->nextId;
    }

    public function jurnal_trans()
    {
      	return $this->hasOne('\Model\Storage\JurnalTrans_model', 'id', 'id_header');
    }
}
