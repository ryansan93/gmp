<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KonfirmasiPembayaranDocDet_model extends Conf{
	protected $table = 'konfirmasi_pembayaran_doc_det';

	public function d_unit()
	{
		return $this->hasOne('\Model\Storage\Wilayah_model', 'kode', 'kode_unit')->orderBy('id', 'desc');
	}

	public function d_mitra()
	{
		return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'mitra')->orderBy('id', 'DESC');
	}
}