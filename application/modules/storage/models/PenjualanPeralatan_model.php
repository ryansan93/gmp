<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PenjualanPeralatan_model extends Conf {
	protected $table = 'penjualan_peralatan';
	protected $primaryKey = 'id';
	protected $nomor = 'nomor';
	protected $kodeTable = 'JPR';

	public function getNextNomor(){
		$id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$this->kodeTable."')+1+6)) = '".$this->kodeTable."'+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)")
				   ->selectRaw("'". $this->kodeTable ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->nomor ."),'000'),(LEN('".$this->kodeTable."')+1+6),3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function d_mitra()
	{
		return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'mitra')->orderBy('version', 'desc');
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\PenjualanPeralatanDetail_model', 'id_header', 'id')->with(['d_barang']);
	}

	public function d_bayar()
	{
		return $this->hasMany('\Model\Storage\BayarPenjualanPeralatan_model', 'id_penjualan_peralatan', 'id')->orderBy('tanggal', 'desc');
	}
}