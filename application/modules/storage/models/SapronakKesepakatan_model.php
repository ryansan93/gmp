<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class SapronakKesepakatan_model extends Conf{
	protected $table = 'sapronak_kesepakatan';
	protected $primaryKey = 'id';
	protected $docNum = 'nomor';
  	protected $status = 'g_status';

  	public function lampiran()
	{
		return $this->hasMany('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', $this->table)->with(['d_nama_lampiran']);
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table);
	}

	public function pola_kerjasama()
	{
		return $this->hasOne('\Model\Storage\PolaKerjasama_model', 'id', 'pola');
	}

	public function harga_sapronak()
	{
		// return $this->hasMany('\Model\Storage\HargaSapronak_model', 'id_sk', 'id')->with(['d_pakan1', 'd_pakan2', 'd_pakan3']);
		return $this->hasMany('\Model\Storage\HargaSapronak_model', 'id_sk', 'id')->with(['d_supplier', 'detail']);
	}

	public function harga_performa()
	{
		return $this->hasMany('\Model\Storage\HargaPerforma_model', 'id_sk', 'id')->with(['d_pakan1', 'd_pakan2', 'd_pakan3']);
	}

	public function harga_sepakat()
	{
		return $this->hasMany('\Model\Storage\HargaSepakat_model', 'id_sk', 'id');
	}

	public function hitung_budidaya_item()
	{
		return $this->hasMany('\Model\Storage\HitungBudidayaItem_model', 'id_sk', 'id');
	}

	public function hitung_budidaya()
	{
		return $this->hasMany('\Model\Storage\HitungBudidayaItem_model', 'id_sk', 'id')
					->with(['perwakilan_maping', 'pola_kerjasama']);
	}

	public function standar_pakan()
	{
		return $this->hasMany('\Model\Storage\StandarPakan_model', 'id_sk', 'id')->orderBy('id', 'ASC');
	}

	public function selisih_pakan()
	{
		return $this->hasMany('\Model\Storage\SelisihPakan_model', 'id_sk', 'id')->orderBy('id', 'ASC');
	}

	public function data_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->with(['d_kota'])->orderBy('id', 'DESC');
	}

	public function bonus_insentif_listrik()
	{
		return $this->hasMany('\Model\Storage\BonusInsentifListrik_model', 'id_sk', 'id')->orderBy('id', 'ASC');
	}

	public function getDashboard($status)
	{
		$table_name = $this->table;
		$column_name_key = $this->primaryKey;
		$column_name_status = $this->status;

		$sql = <<<QUERY

				select
						lt.nama_detuser aktor
					,case
							when(t2.g_status = 2) then 'Ack'
							when(t2.g_status = 1) then 'Submit'
						end as status_data
					, case
							when(t2.g_status = 2) then 'Approve'
							when(t2.g_status = 1) then 'Ack'
						end as next_state
					, count( distinct(nomor) ) jumlah
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
					on lt.tbl_id = t2.id and t2.g_status = $status
					join (
						select max(z.id) as id from $table_name z group by z.nomor
					) x
					on
						lt.tbl_id = x.id
					where t2.g_status = $status
					group by
					lt.nama_detuser
					, t2.g_status
QUERY;

		return $this->hydrateRaw ( $sql );
	}
}
