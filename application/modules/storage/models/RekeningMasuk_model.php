<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RekeningMasuk_model extends Conf {
  	protected $table = 'rekening_masuk';
  	protected $primaryKey = 'kode';
  	protected $kodeTable = 'RKM';
}