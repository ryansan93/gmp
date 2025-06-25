<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KonfirmasiPembayaranPakanDet_model extends Conf{
	protected $table = 'konfirmasi_pembayaran_pakan_det';

	public function d_unit()
	{
		return $this->hasOne('\Model\Storage\Wilayah_model', 'kode', 'kode_unit')->orderBy('id', 'desc');
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\KonfirmasiPembayaranPakanDet2_model', 'id_header', 'id')->with(['d_gudang', 'd_barang']);
	}
}