<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MsTimPanen_model extends Conf{
	protected $table = 'ms_timpanen';
	protected $primaryKey = 'nik_timpanen';

	public function scopeActive($query, $status_timpanen = TRUE)
	{
		return $query->where('status_timpanen', $status_timpanen);
	}

	public function detail()
	{
		return $this->hasMany('\Model\Storage\DetTimPanen_model', 'nik_timpanen', 'nik_timpanen')->with(['dJabatan']);
	}
}
