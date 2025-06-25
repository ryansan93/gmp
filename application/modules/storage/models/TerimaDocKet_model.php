<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class TerimaDocKet_model extends Conf {
	public $incrementing = false;
	public $timestamps = false;

	protected $table = 'terima_doc_ket';
}