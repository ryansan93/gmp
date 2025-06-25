<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class HitungBudidayaItem_model extends Conf{
  protected $table = 'hitung_budidaya_item';
  protected $primaryKey = 'id';
  // protected $item_budidaya = 'item_budidaya';

  public function pola_kerjasama()
  {
    return $this->hasOne('\Model\Storage\PolaKerjasama_model', 'id', 'pola_kemitraan');
  }

  public function perwakilan_maping()
  {
    return $this->hasMany('\Model\Storage\PerwakilanMaping_model', 'id_hbi', 'id');
  }

  public function dSapronakKesepakatan()
  {
    return $this->hasOne('\Model\Storage\SapronakKesepakatan_model', 'id', 'id_sk')
                  ->where(function($q) {
                        $q->whereNull('berakhir')
                          ->orWhere('berakhir', '>=', date('Y-m-d'));
                    })
                  ->with(['pola_kerjasama','harga_sapronak','harga_performa','harga_sepakat'])
                  ->orderBy('version', 'DESC');
  }

  public function dSapronakKesepakatanNoCheck()
  {
    return $this->hasOne('\Model\Storage\SapronakKesepakatan_model', 'id', 'id_sk')
                  ->with(['pola_kerjasama','harga_sapronak','harga_performa','harga_sepakat']);
  }
}
