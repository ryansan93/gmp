<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class PotonganPelanggan_model extends Conf{
	protected $table = 'potongan_pelanggan';

	public function d_pelanggan()
	{
		return $this->hasOne('\Model\Storage\Pelanggan_model', 'nomor', 'no_pelanggan')->where('tipe', 'pelanggan')->orderBy('id', 'desc');
	}
}
