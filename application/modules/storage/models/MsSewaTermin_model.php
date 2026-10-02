<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class MsSewaTermin_model extends Conf{
    public $table = 'ms_sewa_termin';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /*
     * Pembayaran SEWA di Realisasi Pembayaran: 1 baris realisasi_pembayaran_det
     * = 1 termin. Kuncinya disimpan di realisasi_pembayaran_det.no_bayar (varchar 25)
     * dengan format "<no_sewa>-T<no_termin>", mis. "SW/KDG/2026/10/0001-T0".
     */
    public static function buatNoBayar($no_sewa, $no_termin)
    {
        return $no_sewa.'-T'.$no_termin;
    }

    /* @return array|null  [no_sewa, no_termin] atau null kalau format tidak cocok */
    public static function parseNoBayar($no_bayar)
    {
        if ( preg_match('/^(.+)-T(\d+)$/', trim($no_bayar), $m) ) {
            return array($m[1], (int) $m[2]);
        }

        return null;
    }

    /* Ambil semua detail SEWA milik 1 realisasi pembayaran */
    public static function detailSewa($id_header)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select rpd.no_bayar, rpd.bayar
            from realisasi_pembayaran_det rpd
            where
                rpd.id_header = ".((int) $id_header)." and
                rpd.transaksi = 'SEWA'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        return $d_conf->count() > 0 ? $d_conf->toArray() : array();
    }

    /* true kalau SEMUA detail realisasi ini bertransaksi SEWA (bukan campuran) */
    public static function hanyaSewa($id_header)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                sum(case when rpd.transaksi = 'SEWA' then 1 else 0 end) as sewa,
                count(*) as semua
            from realisasi_pembayaran_det rpd
            where rpd.id_header = ".((int) $id_header)."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $row = $d_conf->toArray()[0];

            return ((int) $row['semua']) > 0 && ((int) $row['sewa']) == ((int) $row['semua']);
        }

        return false;
    }

    /*
     * Terapkan pembayaran realisasi ke termin sewa.
     * $arah =  1 : dibayar  -> nominal_terbayar bertambah sebesar rpd.bayar
     * $arah = -1 : batal bayar -> nominal_terbayar berkurang sebesar rpd.bayar
     * status termin: 0 = Belum, 1 = Lunas (nominal_terbayar >= nominal)
     */
    public static function terapkanDariRealisasi($id_header, $arah = 1)
    {
        $arah = ($arah < 0) ? -1 : 1;

        foreach ( self::detailSewa($id_header) as $v_det ) {
            $kunci = self::parseNoBayar($v_det['no_bayar']);
            if ( empty($kunci) ) {
                continue;
            }

            $m_termin = new self();
            $d_termin = $m_termin->where('no_sewa', $kunci[0])->where('no_termin', $kunci[1])->first();
            if ( !$d_termin ) {
                continue;
            }

            $terbayar = (float) $d_termin->nominal_terbayar + ($arah * (float) $v_det['bayar']);
            if ( $terbayar < 0 ) {
                $terbayar = 0;
            }

            $lunas = ((float) $d_termin->nominal > 0 && $terbayar >= (float) $d_termin->nominal) ? 1 : 0;

            $m_update = new self();
            $m_update->where('id', $d_termin->id)->update(
                array(
                    'nominal_terbayar' => $terbayar,
                    'status' => $lunas
                )
            );
        }
    }
}
