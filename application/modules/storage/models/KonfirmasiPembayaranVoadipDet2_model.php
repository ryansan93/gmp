<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KonfirmasiPembayaranVoadipDet2_model extends Conf{
	protected $table = 'konfirmasi_pembayaran_voadip_det2';

	public function d_gudang()
	{
		return $this->hasOne('\Model\Storage\Gudang_model', 'id', 'id_gudang');
	}

	public function d_barang()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_brg')->orderBy('version', 'desc');
	}
}