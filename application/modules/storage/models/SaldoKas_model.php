<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SaldoKas_model extends Conf {
    protected $table = 'saldo_kas';
    protected $primaryKey = 'id';

    public function getDashboard($status)
	{
    	$table_name = $this->table;
		$column_name_key = $this->primaryKey;
		$sql = "
            select
                count( distinct(m.id) ) jumlah,
                '".getStatus($status)."' as status_data,
                case
                    when(m.g_status = '".getStatus('submit')."') then 'Ack'
                    else 'Finish'
                end as next_state,
                -- lt.nama_detuser aktor,
                '' as aktor,
                w.nama as keterangan,
                w.kode as kode_unit,
                min(m.periode) as periode
            from
                $table_name m
            left join
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
                        where l.tbl_name = '".$table_name."'
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
                on 
                    lt.tbl_id = m.id
            left join
                (
                    select
                        w1.kode,
                        REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama
                    from wilayah w1
                    right join
                        (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                        on
                            w1.id = w2.id

                    union all

                    select 'pusat' as kode, 'PUSAT' as nama
                ) w
                on
                    m.unit = w.kode
            where
                m.g_status = '".getStatus('submit')."'
            group by
                m.g_status,
                w.nama,
                w.kode
            order by
                w.nama asc
        ";
        $d_conf = $this->hydrateRaw ( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $data[] = array(
                    'jumlah' => $value['jumlah'],
                    'status_data' => $value['status_data'],
                    'next_state' => $value['next_state'],
                    'aktor' => $value['aktor'],
                    'keterangan' => $value['keterangan'],
                    'params' => exEncrypt(json_encode(array(
                        'kode_unit' => $value['kode_unit'],
                        'periode' => $value['periode']
                    )))
                );
            }
        }
        

		return $data;
  	}
}
