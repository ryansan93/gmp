<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class KreditBank_model extends Conf{
	protected $table = 'kredit_bank';
	protected $primaryKey = 'kode';
	protected $kodeTable = 'KRB';

	public function d_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('id', 'desc');
	}

  	public function detail()
	{
		return $this->hasMany('\Model\Storage\KreditBankDet_model', 'kredit_bank_kode', 'kode');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
	}
}
