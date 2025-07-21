<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RealisasiPembayaran_model extends Conf{
	protected $table = 'realisasi_pembayaran';

	protected $primaryKey = 'id';
	protected $nomor = 'nomor';
	protected $kodeTable = 'BYR';

	public function getNextNomor(){
		$id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$this->kodeTable."')+1+6)) = '".$this->kodeTable."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+cast(right(year(current_timestamp),2) as char(2))")
				   ->selectRaw("'". $this->kodeTable ."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+right(year(current_timestamp),2)+'/'+replace(str(substring(coalesce(max(". $this->nomor ."),'00000'),(LEN('".$this->kodeTable."')+1+7),5)+1,5), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function getNextNomorAuto( $kode ){
		$id = $this->whereRaw("SUBSTRING(no_bukti_auto,0,(LEN('".$kode."')+1+8)) = '".$kode."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+cast(year(current_timestamp) as char(4))")
				   ->selectRaw("'".$kode."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+cast(year(current_timestamp) as varchar(4))+'/'+replace(str(substring(coalesce(max(no_bukti_auto),'0000'),(LEN('".$kode."')+1+9),4)+1,4), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('version', 'DESC');
	}

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'supplier')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc');
	}

	public function d_mitra()
	{
		return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'peternak')->orderBy('version', 'desc');
	}

	public function d_ekspedisi()
	{
		return $this->hasOne('\Model\Storage\Ekspedisi_model', 'nomor', 'ekspedisi')->orderBy('id', 'desc');
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\RealisasiPembayaranDet_model', 'id_header', 'id');
	}

	public function dn_realisasi_pembayaran()
	{
		return $this->hasMany('\Model\Storage\RealisasiPembayaranDn_model', 'id_header', 'id')->with(['d_dn']);
	}

	public function cn_realisasi_pembayaran()
	{
		return $this->hasMany('\Model\Storage\RealisasiPembayaranCn_model', 'id_header', 'id')->with(['d_cn']);
	}

	public function d_potongan()
	{
		return $this->hasMany('\Model\Storage\RealisasiPembayaranPotongan_model', 'id_header', 'id')->with(['det_jurnal_trans']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}