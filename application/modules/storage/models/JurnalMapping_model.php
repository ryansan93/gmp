<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class JurnalMapping_model extends Conf{
    // public $incrementing = false;

    protected $table = 'jurnal_mapping';
    protected $primaryKey = 'id';

    public function det_jurnal_trans()
    {
        return $this->hasOne('\Model\Storage\DetJurnalTrans_model', 'id', 'det_jurnal_trans_id');
    }

    public function jurnal_report()
    {
        return $this->hasOne('\Model\Storage\JurnalReport_model', 'id', 'jurnal_report_id');
    }
}
