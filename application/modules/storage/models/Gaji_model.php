<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Gaji_model extends Conf{
    // public $incrementing = false;

    protected $table = 'gaji';
    protected $primaryKey = 'id';

    public function karyawan()
    {
        return $this->hasOne('\Model\Storage\Karyawan_model', 'nik', 'nik')->orderBy('id', 'desc');
    }

    public function gaji_unit()
    {
        return $this->hasMany('\Model\Storage\GajiUnit_model', 'id_header', 'id')->with(['unit']);
    }

    public function gaji_insentif()
    {
        return $this->hasMany('\Model\Storage\GajiInsentif_model', 'id_header', 'id')->orderBy('keterangan', 'asc');
    }

    public function gaji_potongan()
    {
        return $this->hasMany('\Model\Storage\GajiPotongan_model', 'id_header', 'id')->orderBy('keterangan', 'asc');
    }
}
