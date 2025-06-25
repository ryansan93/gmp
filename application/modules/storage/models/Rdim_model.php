<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Rdim_model extends Conf{
	protected $table = 'rdim';
	protected $primaryKey = 'id';
	protected $docNum = 'nomor';
	protected $status = 'g_status';

	public function dRdimSubmit()
	{
		return $this->hasMany('\Model\Storage\RdimSubmit_model','id_rdim','id')->with(['dMitraMapping', 'dKandang', 'dHitungBudidaya', 'dTimpanen', 'dHarianKandang', 'dRealDocin', 'data_perusahaan', 'data_vaksin']);
	}

	public function dRdimSubmitLsam()
	{
		return $this->hasMany('\Model\Storage\RdimSubmit_model','id_rdim','id')->with(['dMitraMapping', 'dKandang', 'dHarianKandangLsam', 'dRealDocin']);
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
						m.$column_name_status status_data,
						case
							when(m.$column_name_status = 2 ) then 'Approve'
							when(m.$column_name_status = 1 ) then 'Ack'
							else 'Finish'
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
					group by
					m.$column_name_status,
					lt.nama_detuser
QUERY;

		return $this->hydrateRaw ( $sql );
	}
}
