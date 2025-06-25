<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KonfirmasiPembayaranPeternak_model extends Conf{
	protected $table = 'konfirmasi_pembayaran_peternak';

	protected $primaryKey = 'id';
	protected $nomor = 'nomor';
	protected $no_invoice = 'invoice';
	protected $kodeTable = 'BYM';

	public function getNextNomor()
	{
		$id = $this->whereRaw("SUBSTRING(". $this->nomor .",0,(LEN('".$this->kodeTable."')+1+6)) = '".$this->kodeTable."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+cast(right(year(current_timestamp),2) as char(2))")
				   ->selectRaw("'". $this->kodeTable ."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+right(year(current_timestamp),2)+'/'+replace(str(substring(coalesce(max(". $this->nomor ."),'00000'),(LEN('".$this->kodeTable."')+1+7),5)+1,5), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function getNextNoInvoice( $kode )
	{
		$id = $this->whereRaw("SUBSTRING(". $this->no_invoice .",0,((LEN('".$kode."')+1)+3)) = '".$kode."'+'/'+cast(right(year(current_timestamp),2) as char(2))")
				   ->selectRaw("'". $kode ."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(substring(coalesce(max(". $this->no_invoice ."),'0000'),(((LEN('".$kode."')+1)+3)+1),4)+1,4), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function d_mitra()
	{
		return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'mitra')->orderBy('version', 'desc');
	}

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('version', 'DESC');
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\KonfirmasiPembayaranPeternakDet_model', 'id_header', 'id')->with(['detail2']);
	}

	public function d_realisasi()
	{
		return $this->hasMany('\Model\Storage\RealisasiPembayaran_model', 'nomor', 'nomor');
	}
}