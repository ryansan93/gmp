<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class RdimSubmit_model extends Conf{
	public $incrementing = false;
	
	protected $table = 'rdim_submit';
	protected $primaryKey = 'id';

	public function order_doc()
	{
		return $this->hasMany('\Model\Storage\OrderDoc_model','noreg', 'noreg')->with(['terima_doc', 'd_barang', 'd_supplier', 'logs'])->orderBy('version', 'DESC');
	}

	public function order_doc_rhpp()
	{
		return $this->hasOne('\Model\Storage\OrderDoc_model','noreg', 'noreg')->with(['terima_doc_rhpp', 'd_barang', 'd_supplier'])->orderBy('version', 'DESC');
	}

	public function dMitraMapping()
	{
		return $this->hasOne('\Model\Storage\MitraMapping_model','nim', 'nim')->with(['dMitra', 'dPerwakilan', 'kandangs'])->orderBy('id', 'desc');
	}

	public function dKandang()
	{
		return $this->hasOne('\Model\Storage\Kandang_model', 'id', 'kandang')->with(['dKecamatan', 'd_unit'])->orderBy('id', 'desc');
	}

	public function mitra()
	{
		return $this->hasOne('\Model\Storage\MitraMapping_model','nim', 'nim')->with(['dMitra'])->orderBy('id', 'desc');
	}

	public function kandang()
	{
		return $this->hasOne('\Model\Storage\Kandang_model','id', 'kandang')->with(['d_unit']);
	}

	public function dHitungBudidaya()
	{
		return $this->hasOne('\Model\Storage\HitungBudidaya_model', 'id', 'format_pb');
	}

	public function dPerwakilan()
	{
		return $this->hasOne('\Model\Storage\MitraMapping_model','nim', 'nim')->with(['dPerwakilan']);
	}

	public function dTimpanen()
	{
		return $this->hasOne('\Model\Storage\Karyawan_model', 'nik', 'tim_panen');
	}

	public function dPengawas()
	{
		return $this->hasOne('\Model\Storage\Karyawan_model', 'nik', 'pengawas');
	}

	public function dSampling()
	{
		return $this->hasOne('\Model\Storage\Karyawan_model', 'nik', 'sampling');
	}

	public function lampirans()
	{
		return $this->hasMany('\Model\Storage\Lampiran_model', 'tabel_id', 'id')->where('tabel', $this->table)->with('d_nama_lampiran');
	}

	public function dPerwakilanMapping()
	{
		return $this->hasOne('\Model\Storage\PerwakilanMaping_model','id', 'format_pb')->with(['dHitungBudidayaItem', 'hitung_budidaya_item_kpm']);
	}

	public function dBasttb()
	{
		return $this->hasOne('\Model\Storage\RealDocin_model', 'noreg', 'noreg')->orderBy('tgl_terima', 'desc');
	}

	public function dRealDocin()
	{
		return $this->hasOne('\Model\Storage\RealDocin_model', 'rdim_submit', 'id')->orderBy('tgl_terima', 'desc');
	}

	public function dHarianKandang()
	{
		return $this->hasMany('\Model\Storage\HarianKandang_model', 'id_rdim_submit', 'id')->orderBy('tgl_timbang', 'ASC');
	}

	public function dHarianKandangLsam()
	{
		$CI = & get_instance();
		$umurLsam = $config = $CI->config->item('umur_lsam');
		return $this->hasMany('\Model\Storage\HarianKandang_model', 'id_rdim_submit', 'id')->whereIn('umur', $umurLsam)->orderBy('tgl_timbang', 'ASC');
	}

	public function dRhk()
	{
		return $this->hasMany('\Model\Storage\HarianKandang_model', 'id_rdim_submit', 'id')->orderBy('id', 'desc');
	}

	public function data_perusahaan()
	{
		return $this->hasOne('\Model\Storage\Perusahaan_model', 'kode', 'perusahaan')->orderBy('version', 'DESC');
	}

	public function data_vaksin()
	{
		return $this->hasOne('\Model\Storage\Vaksin_model', 'id', 'vaksin')->orderBy('id', 'DESC');
	}

	public function data_konfir()
	{
		return $this->hasOne('\Model\Storage\Konfir_model', 'noreg', 'noreg')->with(['det_konfir', 'logs'])->orderBy('id', 'DESC');
	}
}
