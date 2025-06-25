<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class HargaSapronak_model extends Conf{
	protected $table = 'harga_sapronak';
	protected $primaryKey = 'id';

	public function detail()
	{
		return $this->hasMany('\Model\Storage\DetHargaSapronak_model', 'id_header', 'id')->with(['d_barang']);
	}

	public function d_supplier()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'kode_supl')->where('tipe', 'supplier')->orderBy('version', 'desc');
	}

	public function lampiran_oa_pakan()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'filename', 'oa_pakan_dok')->where('tabel', 'sapronak_kesepakatan');
	}

	public function lampiran_voadip()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'filename', 'voadip_dok')->where('tabel', 'sapronak_kesepakatan');
	}

	public function lampiran_oa_doc()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'filename', 'oa_doc_dok')->where('tabel', 'sapronak_kesepakatan');
	}

	public function lampiran_doc()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'filename', 'doc_dok')->where('tabel', 'sapronak_kesepakatan');
	}

	public function lampiran_pakan1()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'filename', 'pakan1_dok')->where('tabel', 'sapronak_kesepakatan');
	}

	public function d_pakan1()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_pakan1')->orderBy('id', 'desc');
	}

	public function d_pakan2()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_pakan2')->orderBy('id', 'desc');
	}

	public function d_pakan3()
	{
		return $this->hasOne('\Model\Storage\Barang_model', 'kode', 'kode_pakan3')->orderBy('id', 'desc');
	}
}
