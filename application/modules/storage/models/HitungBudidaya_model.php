<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class HitungBudidaya_model extends Conf{
  protected $table = 'hitung_budidaya';
  protected $primaryKey = 'id';
  protected $docNum = 'nomor';
  protected $status = 'g_status';

  public function details()
  {
    return $this->hasMany('\Model\Storage\HitungBudidayaItem_model', 'id_hitbud', 'id')->with(['pola_kerjasama']);
  }

  public function wilayah()
  {
	return $this->hasOne('\Model\Storage\Wilayah_model', 'id', 'perwakilan');
  }

  public function getDashboard_HitungBDY($status)
	{
		$table_name = $this->table;
		$column_name_key = $this->primaryKey;
		$column_name_status = $this->status;

		$sql = <<<QUERY

				select
					lt.nama_user aktor
				,case
						when(t2.$column_name_status = 4) then 'Reject'
						when(t2.$column_name_status = 3) then 'Approve'
						when(t2.$column_name_status = 2) then 'Ack'
						when(t2.$column_name_status = 1) then 'Submit'
					end as status_data
				, case
						when(t2.$column_name_status = 4) then 'Ubah'
						when(t2.$column_name_status = 1) then 'Approve'
					end as next_state
				, count(*) jumlah
				from
					(select 
						top 100 *
					from $table_name
					order by 
						id desc
					) t2
				join
					(select
							log.id
						, log.tbl_id
						, usr.nama_user
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
					) lt
				on lt.tbl_id = t2.id and t2.$column_name_status = $status
				where t2.$column_name_status = $status
				group by
				lt.nama_User
				, t2.$column_name_status
QUERY;

		return $this->hydrateRaw ( $sql );
	}
}
