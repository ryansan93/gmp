<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PembayaranPelanggan_model extends Conf{
	protected $table = 'pembayaran_pelanggan';

	public function getNextNomorAuto( $kode, $tanggal ){
		$id = $this->whereRaw("SUBSTRING(no_bukti_auto,0,(LEN('".$kode."')+1+8)) = '".$kode."'+'/'+replace(str(month('".$tanggal."'),2),' ',0)+'/'+cast(year('".$tanggal."') as char(4))")
				   ->selectRaw("'".$kode."'+'/'+replace(str(month('".$tanggal."'),2),' ',0)+'/'+cast(year('".$tanggal."') as varchar(4))+'/'+replace(str(substring(coalesce(max(no_bukti_auto),'0000'),(LEN('".$kode."')+1+9),4)+1,4), ' ', '0') as nextId")
				   ->first();
		// $id = $this->whereRaw("SUBSTRING(no_bukti_auto,0,(LEN('".$kode."')+1+8)) = '".$kode."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+cast(year(current_timestamp) as char(4))")
		// 		   ->selectRaw("'".$kode."'+'/'+replace(str(month(getdate()),2),' ',0)+'/'+cast(year(current_timestamp) as varchar(4))+'/'+replace(str(substring(coalesce(max(no_bukti_auto),'0000'),(LEN('".$kode."')+1+9),4)+1,4), ' ', '0') as nextId")
		// 		   ->first();
		return $id->nextId;
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\DetPembayaranPelanggan_model', 'id_header', 'id')->with(['data_do']);
	}

	public function perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'desc');
	}

	public function pelanggan()
	{
		return $this->hasOne('\Model\Storage\Pelanggan_model', 'nomor', 'no_pelanggan')->where('tipe', 'pelanggan')->where('jenis', '<>', 'ekspedisi')->with(['kecamatan'])->orderBy('version', 'desc');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
  	}
}
