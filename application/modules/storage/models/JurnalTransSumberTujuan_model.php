<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class JurnalTransSumberTujuan_model extends Conf{
    // public $incrementing = false;

    protected $table = 'jurnal_trans_sumber_tujuan';
    protected $primaryKey = 'id';

    public function jurnal_trans()
    {
      	return $this->hasOne('\Model\Storage\JurnalTrans_model', 'id', 'id_header');
    }

    public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}
}
