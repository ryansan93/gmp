<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MitraMapping_model extends Conf{
  public $incrementing = false;

  protected $table = 'mitra_mapping';
  protected $primaryKey = 'id';
  protected $nim = 'nim';

  public function getNextNim(){
    $id = $this->whereRaw("SUBSTRING(".$this->nim.",0,5) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
                ->selectRaw("right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->nim."),'000'),5,3)+1,3), ' ', '0') as nextId")
                ->first();
    return $id->nextId;
  }

  public function dPerwakilan()
  {
    return $this->hasOne('\Model\Storage\Wilayah_model', 'id', 'perwakilan')->with(['unit', 'mitra_mapping']);
  }

  public function kandangs()
  {
    return $this->hasMany('\Model\Storage\Kandang_model', 'mitra_mapping', 'id')->with(['bangunans', 'd_unit', 'dKecamatan', 'lampirans']);
  }

  public function juts()
  {
    return $this->hasMany('\Model\Storage\MitraRekeningKoran_model', 'mitra_mapping', 'id')->where('kode_akun', 'H2');
  }

  public function dMitra()
  {
    return $this->hasOne('\Model\Storage\Mitra_model', 'nomor', 'nomor')->with(['dKecamatan'])->orderBy('version', 'DESC');
  }

  public function trxRekening()
  {
    return $this->hasMany('\Model\Storage\MitraRekeningKoran_model', 'mitra_mapping', 'id');
  }

}
