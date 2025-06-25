<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KonfirmasiPembayaranPakan_model extends Conf{
	protected $table = 'konfirmasi_pembayaran_pakan';

	protected $primaryKey = 'id';
	protected $nomor = 'nomor';
	protected $kodeTable = 'BYP';

	public function getNextNomor(){
		$id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$this->kodeTable."')+1+6)) = '".$this->kodeTable."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+cast(right(year(current_timestamp),2) as char(2))")
				   ->selectRaw("'". $this->kodeTable ."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+right(year(current_timestamp),2)+'/'+replace(str(substring(coalesce(max(". $this->nomor ."),'00000'),(LEN('".$this->kodeTable."')+1+7),5)+1,5), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'supplier')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc');
	}

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('version', 'DESC');
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\KonfirmasiPembayaranPakanDet_model', 'id_header', 'id')->with(['d_unit', 'detail']);
	}

	public function d_realisasi()
	{
		return $this->hasMany('\Model\Storage\RealisasiPembayaran_model', 'nomor', 'nomor');
	}
}