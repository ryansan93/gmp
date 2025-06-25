<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Ekspedisi_model extends Conf {
	public $incrementing = false;

	protected $table = 'ekspedisi';
	protected $primaryKey = 'id';
	protected $status = 'status';
	protected $nomor = 'nomor';

	public function getNextNomor($kode_jenis)
	{
		$id = $this->selectRaw("right(year(current_timestamp),2)+". "'".$kode_jenis."'+replace(str(substring(coalesce(max(".$this->nomor."),'000'),4,3)+1,3), ' ', '0') as nextId")->first();
		return $id->nextId;
	}

	public function telepons()
	{
		return $this->hasMany('\Model\Storage\TelpEkspedisi_model', 'ekspedisi_id', 'id');
	}

	public function kecamatan()
	{
		return $this->hasOne('\Model\Storage\Lokasi_model', 'id', 'alamat_kecamatan')->with('dKota');
	}

	public function logs()
  	{
    	return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', 'ekspedisi');
  	}

  	public function lampiran()
	{
		return $this->hasOne('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', 'ekspedisi')->with('d_nama_lampiran');
	}

  	public function banks() {
  		return $this->hasMany('\Model\Storage\BankEkspedisi_model', 'ekspedisi_id', 'id')->with(['lampiran']);
  	}

  	public function potongan_pph() {
  		return $this->hasOne('\Model\Storage\EkspedisiPph23_model', 'id', 'potongan_pph_id');
  	}

  	public function aktif() {
  		return $this->hasOne('\Model\Storage\AktifEkspedisi_model', 'ekspedisi_id', 'id');
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
		when(m.$column_name_status = 'submit') then 'Ack'
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
	on lt.tbl_id = m.id and 
	   m.$column_name_status = 'submit'
	join (
		select max(z.id) as id from ekspedisi z group by z.nomor
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