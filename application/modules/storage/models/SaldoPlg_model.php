<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SaldoPlg_model extends Conf{
    protected $table = 'saldo_plg';
    protected $nomor = 'nomor';

    public function getNextNomor($kode){
        $id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$kode."')+1+6)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
                    ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->nomor ."),'0000'),(LEN('".$kode."')+1+6),4)+1,4), ' ', '0') as nextId")
                    ->first();
        return $id->nextId;
    }
}
