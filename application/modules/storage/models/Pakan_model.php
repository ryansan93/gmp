<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Pakan_model extends Conf{
// 	protected $table = 'pakan';
// 	protected $primaryKey = 'id';
// 	protected $docNum = 'nomor';
//   	protected $status = 'g_status';

//   	public function lampiran()
// 	{
// 		return $this->hasOne('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', 'pakan')->with('d_nama_lampiran');
// 	}

// 	public function logs()
// 	{
// 		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
// 	}

// 	public function getDashboard_Pakan($status)
// 	{
// 		$table_name = $this->table;
// 		$column_name_key = $this->primaryKey;
// 		$column_name_status = $this->status;

// 		$sql = <<<QUERY
// 				select
// 					lt.nama_detuser aktor
// 				,case
// 						when(t2.$column_name_status = 3) then 'Approve'
// 						when(t2.$column_name_status = 2) then 'Ack'
// 						when(t2.$column_name_status = 1) then 'Submit'
// 					end as status_data
// 				, case
// 						when(t2.$column_name_status = 1) then 'Ack'
// 						when(t2.$column_name_status = 2) then 'Approve'
// 					end as next_state
// 				, count(*) jumlah
// 				from
// 					(select 
// 						top 1 *
// 					from $table_name
// 					order by 
// 						id desc
// 					) t2
// 				join
// 					(select
// 							log.id
// 						, log.tbl_id
// 						, d_usr.nama_detuser
// 						, log.deskripsi
// 						, log.waktu
// 					from ( select
// 								l.tbl_id
// 								, max(l.id) as id
// 							from
// 								log_tables l
// 							where l.tbl_name = '$table_name'
// 							group by
// 								l.tbl_id
// 							) mx
// 						join log_tables log
// 							on log.id = mx.id
// 						join ms_user usr
// 							on usr.id_user = log.user_id
// 						join detail_user d_usr
// 							on d_usr.id_user = usr.id_user and d_usr.nonaktif_detuser is null
// 					) lt
// 				on lt.tbl_id = t2.id and t2.$column_name_status = $status
// 				where t2.$column_name_status = $status
// 				group by
// 				lt.nama_detuser
// 				, t2.$column_name_status
// QUERY;

// 		return $this->hydrateRaw ( $sql );
// 	}
}
