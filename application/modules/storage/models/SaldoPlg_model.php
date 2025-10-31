<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SaldoPlg_model extends Conf{
    protected $table = 'saldo_plg';

    public function getNextNomor($kode){
        $id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$kode."')+1+6)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
                    ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->nomor ."),'000'),(LEN('".$kode."')+1+6),3)+1,3), ' ', '0') as nextId")
                    ->first();
        return $id->nextId;
    }
}
