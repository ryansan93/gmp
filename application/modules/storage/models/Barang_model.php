<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Barang_model extends Conf{
	protected $table = 'barang';
	protected $kode_pakan = 'PK';
	protected $kode_doc = 'CK';
	protected $kode_voadip = 'OB';
	protected $kode_peralatan = 'PR';
	protected $primary_key = 'kode';
	protected $status = 'g_status';

	public function getNextIdFeed(){
		$id = $this->whereRaw("SUBSTRING(". $this->primary_key .",3,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
				   ->whereRaw("SUBSTRING(". $this->primary_key .",0,3) = '" . $this->kode_pakan . "'")
				   ->selectRaw("'". $this->kode_pakan ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->primary_key ."),'000'),7,3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function getNextIdDoc(){
		$id = $this->whereRaw("SUBSTRING(". $this->primary_key .",3,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
				   ->whereRaw("SUBSTRING(". $this->primary_key .",0,3) = '" . $this->kode_doc . "'")
				   ->selectRaw("'". $this->kode_doc ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->primary_key ."),'000'),7,3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function getNextIdVoadip(){
		$id = $this->whereRaw("SUBSTRING(". $this->primary_key .",3,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
				   ->whereRaw("SUBSTRING(". $this->primary_key .",0,3) = '" . $this->kode_voadip . "'")
				   ->selectRaw("'". $this->kode_voadip ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->primary_key ."),'000'),7,3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function getNextIdPeralatan(){
		$id = $this->whereRaw("SUBSTRING(". $this->primary_key .",3,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
				   ->whereRaw("SUBSTRING(". $this->primary_key .",0,3) = '" . $this->kode_peralatan . "'")
				   ->selectRaw("'". $this->kode_peralatan ."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(". $this->primary_key ."),'000'),7,3)+1,3), ' ', '0') as nextId")
				   ->first();
		return $id->nextId;
	}

	public function logs()
	{
		return $this->hasMany('\Model\Storage\LogTables_model', 'tbl_id', 'id')->where('tbl_name', $this->table)->orderBy('id', 'ASC');
	}

	public function supplier()
	{
		return $this->hasMany('\Model\Storage\SupplierPakan_model', 'id_pakan', 'id')->with(['data_supplier'])->orderBy('id', 'ASC');
	}

	public function supplier_not_pakan()
	{
		return $this->hasOne('\Model\Storage\Supplier_model', 'nomor', 'kode_supplier')->where('tipe', 'supplier')->orderBy('version', 'DESC');
	}

	public function getDashboardAll($status)
	{
		$table_name = $this->table;
		$column_name_key = $this->primaryKey;
		$column_name_status = $this->status;

		$sql = <<<QUERY
				select
			count( distinct(kode) ) jumlah,
			m.$column_name_status status_data,
			case
				when(m.$column_name_status = 1) then 'Ack'
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
			   m.$column_name_status = 1
			join (
				select max(z.id) as id from $table_name z group by z.kode
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
