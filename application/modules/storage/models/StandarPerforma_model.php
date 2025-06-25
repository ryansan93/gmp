<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class StandarPerforma_model extends Conf{
	protected $table = 'standart_performa';
	protected $primaryKey = 'id';
	protected $docNum = 'nomor';
	protected $status = 'status';

	public function details()
	{
		return $this->hasMany('\Model\Storage\DetStandarPerforma_model', 'id_performa', 'id');
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
	}

	public function getDashboard($status)
	{
		$table_name = $this->table;
		$column_name_key = $this->primaryKey;
		$column_name_status = $this->status;
    	// $status = getStatus($status);
		$sql = <<<QUERY
					select
						count(*) jumlah,
						case
							when(m.status = 1 ) then 'Submit'
							when(m.status = 2 ) then 'Ack'
						end as status_data,
						case
							when(m.$column_name_status = 1 ) then 'Ack'
							when(m.$column_name_status = 2 ) then 'Approve'
						end as next_state,
						lt.nama_detuser aktor
					from
						$table_name m
					join
							(select
								log.id
								, log.tbl_id
								, d_usr.nama_detuser
								, log.deskripsi
								, log.waktu
							from ( select
											l.tbl_id
										, max(l.id) as id
									from
										log_tables l
									where l.tbl_name = '$table_name'
									group by
										l.tbl_id
									) mx
							join log_tables log
								on log.id = mx.id
							join ms_user usr
								on usr.id_user = log.user_id
							join detail_user d_usr
								on d_usr.id_user = usr.id_user and d_usr.nonaktif_detuser is null
					) lt
						on lt.tbl_id = m.id and m.$column_name_status = $status
					join (
						select max(z.id) as id from $table_name z group by z.nomor
					) x
					on
						lt.tbl_id = x.id
					group by
					m.$column_name_status,
					lt.nama_detuser
QUERY;

return $this->hydrateRaw ( $sql );
	}

}
