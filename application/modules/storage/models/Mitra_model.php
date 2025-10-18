<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Mitra_model extends Conf{
  public $incrementing = false;

  protected $table = 'mitra';
  protected $primaryKey = 'id';
  protected $kodeTable = 'M';
  protected $status = 'status';
  protected $nomor = 'nomor';

  public function getNextNomor()
  {
    $id = $this->selectRaw("right(year(current_timestamp),2)+". "'".$this->kodeTable."'+replace(str(substring(coalesce(max(".$this->nomor."),'0000'),4,4)+1,4), ' ', '0') as nextId")
                ->first();
    return $id->nextId;
  }

  public function d_pemilik()
  {
    return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'pemilik')->orderBy('id', 'desc');
  }

  public function telepons()
  {
    return $this->hasMany('\Model\Storage\TeleponMitra_model', 'mitra', 'id');
  }

  public function lampirans()
  {
    return $this->hasMany('\Model\Storage\Lampiran_model', 'tabel_id', 'id')
                ->whereIn('nama_lampiran', function($query){
                  $query->select('id')->from('nama_lampiran')->where('jenis', 'MITRA');
                })
                ->where('status', 1)
                ->with('d_nama_lampiran');
  }

  public function lampirans_jaminan()
  {
    return $this->hasMany('\Model\Storage\Lampiran_model', 'tabel_id', 'id')
                ->whereIn('nama_lampiran', function($query){
                  		$query->select('id')->from('nama_lampiran')->where('jenis', 'MITRA_JAMINAN');
                  	})
                ->where('status', 1)
                ->with('d_nama_lampiran');
  }

  public function dKecamatan()
  {
    return $this->hasOne('\Model\Storage\Lokasi_model', 'id', 'alamat_kecamatan')->with('dKota');
  }

  public function perwakilans()
  {
    return $this->hasMany('\Model\Storage\MitraMapping_model', 'mitra', 'id')->with(['dPerwakilan', 'kandangs']);
  }

  public function dPerwakilans()
  {
    return $this->hasMany('\Model\Storage\MitraMapping_model', 'mitra', 'id');
  }

  public function posisi()
  {
    return $this->hasOne('\Model\Storage\MitraPosisi_model', 'nomor', 'nomor')->orderBy('id', 'desc');
  }

  public function logs()
  {
    // return $this->hasMany('\Model\Storage\LogMitra', 'mitra_id', 'id');
    return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->select('waktu', 'deskripsi')->where('tbl_name', $this->table);
  }

  public function getDataMitra($with_bank = 1)
	{
		$sql_bank = null;
		if ( $with_bank == 1 ) {
			$sql_bank = "
				union all

				select
					coa as nomor,
					nama_coa as nama
				from coa
				where 
					bank = 1
			";
		}

		$sql = "
			select * from
			(
				select
					m.nomor,
					m.nama
					-- , kab_kota.nama as kab_kota
				from mitra m
				right join
					( select max(id) as id, nomor from mitra group by nomor ) m1
					on
						m.id = m1.id
				-- right join
				--     lokasi kec
				--     on
				--         kec.id = p.alamat_kecamatan
				-- right join
				--     lokasi kab_kota
				--     on
				--         kab_kota.id = kec.induk
				where
					m.mstatus = 1

				".$sql_bank."
			) data
			order by
				data.nama asc
		";
		$d_mitra = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_mitra->count() > 0 ) {
			$data = $d_mitra->toArray();
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
            count( distinct(nomor) ) jumlah,
            m.$column_name_status status_data,
            case
              when(m.$column_name_status = 'SUBMIT') then 'Ack'
              else 'Finish'
            end as next_state,
            lt.nama_detuser aktor
          from
            mitra m
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
            on lt.tbl_id = m.id and m.$column_name_status = '$status'
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
