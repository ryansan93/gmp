<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class JurnalTrans_model extends Conf{
    // public $incrementing = false;

    protected $table = 'jurnal_trans';
    protected $primaryKey = 'id';
    protected $kodeTable = 'TRJ';

    public function getNextId(){
        $id = $this->whereRaw("SUBSTRING(kode,4,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
                    ->selectRaw("'".$this->kodeTable."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'000'),8,3)+1,3), ' ', '0') as nextId")
                    ->first();
        return $id->nextId;
    }

    public function detail()
    {
      	return $this->hasMany('\Model\Storage\DetJurnalTrans_model', 'id_header', 'id');
    }

    public function sumber_tujuan()
    {
      	return $this->hasMany('\Model\Storage\JurnalTransSumberTujuan_model', 'id_header', 'id');
    }
}
