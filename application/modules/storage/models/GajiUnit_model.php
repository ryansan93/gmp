<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class GajiUnit_model extends Conf{
    public $incrementing = false;

    protected $table = 'gaji_unit';

    public function unit()
    {
        return $this->hasOne('\Model\Storage\Wilayah_model', 'kode', 'unit_kode')->orderBy('id', 'desc');
    }
}
