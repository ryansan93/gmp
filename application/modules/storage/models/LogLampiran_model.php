<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class LogLampiran_model extends Conf{
  protected $table = 'log_lampiran';
  protected $primaryKey = 'id';

  public function d_lampiran()
  {
    return $this->hasOne('\Model\Storage\Lampiran_model', 'id', 'lampiran_id');
  }
}
