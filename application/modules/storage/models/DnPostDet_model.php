<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DnPostDet_model extends Conf {
	protected $table = 'dn_post_det';
    public $timestamps = false;

    public function getData( $id = null ) {
		$sql_condition = null;
		if ( !empty($id) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and dp.id = ".$id."";
			} else {
				$sql_condition = "where dp.id = ".$id."";
			}
		}

		$sql = "
			select 
                dpd.*,
                REPLACE(sj.tgl_sj, '-', '/')+' | '+sj.no_sj as no_sj,
                sj.tagihan,
                sum(isnull(_dpd.tot_pakai, 0)) as tot_pakai,
                sum(isnull(rpd.tot_tf, 0)) as tot_tf,
                (sj.tagihan - (sum(isnull(_dpd.tot_pakai, 0)) + sum(isnull(rpd.tot_tf, 0)))) as sisa
            from dn_post_det dpd
            left join
                dn_post dp
                on
                    dpd.id_header = dp.id
            left join
                (
                    select
                        sj.id as id,
                        sj.total as tagihan,
                        sj.no_sj,
                        sj.tgl_sj
                    from (
                        select
                            kpdd.tgl_order as tgl_sj,
                            kpdd.no_order, 
                            td.no_sj, 
                            kpdd.total, 
                            'DOC' as jenis_dn,
                            kpd.nomor as id
                        from konfirmasi_pembayaran_doc_det kpdd
                        left join
                            konfirmasi_pembayaran_doc kpd 
                            on
                                kpdd.id_header = kpd.id
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = kpdd.no_order
                
                        union all
                
                        select
                            kppd.tgl_sj,
                            kppd.no_order, 
                            kppd.no_sj, 
                            kppd.total, 
                            'PKN' as jenis_dn,
                            kpp.nomor as id
                        from konfirmasi_pembayaran_pakan_det kppd 
                        left join
                            konfirmasi_pembayaran_pakan kpp 
                            on
                                kppd.id_header = kpp.id

                        union all
                
                        select
                            kpvd.tgl_sj,
                            kpvd.no_order, 
                            kpvd.no_sj, 
                            kpvd.total, 
                            'OVK' as jenis_dn,
                            kpv.nomor as id
                        from konfirmasi_pembayaran_voadip_det kpvd 
                        left join
                            konfirmasi_pembayaran_voadip kpv 
                            on
                                kpvd.id_header = kpv.id
                    ) sj
                ) sj
                on
                    sj.id = dpd.nomor
            left join
                (
                    select 
                        dp.id, 
                        dp.tanggal, 
                        dpd.nomor, 
                        sum(dpd.pakai) as tot_pakai
                    from dn_post_det dpd
                    left join
                        dn_post dp 
                        on
                            dpd.id_header = dp.id
                    where
                        dp.id <> '".$id."'
                    group by
                        dp.id,
                        dp.tanggal,
                        dpd.nomor
                ) _dpd
                on
                    dpd.nomor = _dpd.nomor and
                    _dpd.tanggal < dp.tanggal and
                    _dpd.id < dp.id
            left join
                (
                    select
                        rp.tgl_bayar as tanggal,
                        rpd.no_bayar as nomor, 
                        sum(rpd.transfer) as tot_tf 
                    from realisasi_pembayaran_det rpd
                    left join
                        realisasi_pembayaran rp 
                        on
                            rpd.id_header = rp.id
                    group by 
                        rp.tgl_bayar,
                        rpd.no_bayar
                ) rpd
                on
                    dpd.nomor = rpd.nomor and
                    rpd.tanggal < dp.tanggal
            ".$sql_condition."
            group by
                dpd.id_header,
                dpd.nomor,
                dpd.pakai,
                sj.tagihan,
                sj.no_sj,
                sj.tgl_sj
		";
		$d_dpd = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_dpd->count() > 0 ) {
			$data = $d_dpd->toArray();
		}

		return $data;
	}
}