<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Rpah_model extends Conf {
	protected $table = 'rpah';
	protected $primaryKey = 'id';
	protected $status = 'g_status';
    public $timestamps = false;

    public function det_rpah_without_konfir()
	{
		return $this->hasMany('\Model\Storage\DetRpah_model', 'id_rpah', 'id');
	}

    public function det_rpah()
	{
		return $this->hasMany('\Model\Storage\DetRpah_model', 'id_rpah', 'id')->with(['data_konfir']);
	}

	public function det_rpah_real_sj()
	{
		return $this->hasMany('\Model\Storage\DetRpah_model', 'id_rpah', 'id')->with(['data_real_sj']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->select('tbl_name', 'tbl_id', 'user_id', 'waktu', 'deskripsi', '_action')->where('tbl_name', $this->table);
	}

	public function notifData($userId, $status) {
        $m_user = new \Model\Storage\User_model();
        $unitUser = $m_user->getUnitUser( $userId );

        $m_conf = new \Model\Storage\Conf();
		if ( $status == getStatus('submit') ) {
			$sql = "
				select
					".$status." as gstatus,
					'".getStatus($status)."' as nama_status,
					r.tgl_panen as keterangan
				from rpah r
				left join
					wilayah w
					on
						r.id_unit = w.id
				where
					r.g_status = '".$status."' and
					w.kode in ('".implode("', '", $unitUser)."')
				group by
					r.tgl_panen
			";
		} else if ( $status == getStatus('reject') ) {
			$sql = "
				select
					lt.deskripsi,
					lt.waktu,
					".$status." as gstatus,
					'".getStatus($status)."' as nama_status,
					du.nama_detuser as nama_user,
					REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as keterangan,
					w.kode as kode_unit,
					r.tgl_panen
				from rpah r
				left join
					(
						select lt1.* from log_tables lt1
						right join
							(select max(id) as id, tbl_name, tbl_id from log_tables where tbl_name = 'rpah' group by tbl_name, tbl_id) lt2
							on
								lt1.id = lt2.id
					) lt
					on
						r.id = lt.tbl_id
				left join
					(
						select du1.* from detail_user du1
						right join
							(select max(id_detuser) as id_detuser, id_user from detail_user group by id_user) du2
							on
								du1.id_detuser = du2.id_detuser
					) du
					on
						lt.user_id = du.id_user
				left join
					wilayah w
					on
						r.id_unit = w.id
				where
					r.g_status = '".$status."' and
					w.kode in ('".implode("', '", $unitUser)."')
			";
		}
        $d_drpah = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_drpah->count() ) {
            $d_drpah = $d_drpah->toArray();

			foreach ($d_drpah as $key => $value) {
				$key = null;
				if ( isset($value['kode_unit']) && isset($value['tgl_panen']) ) {
					$key = exEncrypt(json_encode(array('kode_unit' => $value['kode_unit'], 'tgl_panen' => $value['tgl_panen'])));
				}

				$data[] = array(
					'deskripsi' => isset($value['deskripsi']) ? $value['deskripsi'] : null,
					'waktu' => isset($value['waktu']) ? $value['waktu'] : null,
					'gstatus' => $value['gstatus'],
					'nama_status' => $value['nama_status'],
					'nama_user' => isset($value['nama_user']) ? $value['nama_user'] : null,
					'keterangan' => ($status == getStatus('submit')) ? tglIndonesia($value['keterangan'], '-', ' ', true) : $value['keterangan'],
					'key' => $key
				);
			}
        }

        return $data;
    }

	public function getDashboard($status)
	{
    	$table_name = $this->table;
		$column_name_key = $this->primaryKey;
		$column_name_status = $this->status;
		$sql = <<<QUERY
					select
						count(m.id) jumlah,
						case
							when(m.$column_name_status = 1) then 'Submit'
							when(m.$column_name_status = 4) then 'Reject'
							else 'Finish'
						end as status_data,
						case
							when(m.$column_name_status = 1) then 'Approve'
							when(m.$column_name_status = 4) then 'Resubmit'
							else 'Finish'
						end as next_state,
						lt.nama_detuser aktor
					from
						$table_name m
					join
						(select
							log.id,
							log.tbl_id,
							d_usr.nama_detuser,
							log.deskripsi,
							log.waktu
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
					on lt.tbl_id = m.id and 
						m.$column_name_status = $status
					group by
						m.$column_name_status,
						lt.nama_detuser
QUERY;

		return $this->hydrateRaw ( $sql );
  	}
}