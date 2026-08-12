<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DashboardMarketing extends Public_Controller {

    private $url;
    private $allowedUnitKodes = null;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/chart/chart.js',
                'assets/report/dashboard_marketing/js/dashboard-marketing.js',
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/report/dashboard_marketing/css/dashboard-marketing.css',
            ));

            $data = $this->includes;

            $m_wil = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['unit'] = $m_wil->getDataUnit(1, $this->userid);

            $data['title_menu'] = 'Dashboard Marketing';
            $data['view'] = $this->load->view('report/dashboard_marketing/index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    /**
     * Filter unit utk semua query dashboard. Kalau dropdown "SEMUA UNIT" (unit='all') dipilih,
     * tetap dibatasi ke unit2 yg jadi cakupan karyawan yg login (bukan benar2 semua unit di
     * perusahaan) - kecuali karyawan tsb memang py akses 'all' di unit_karyawan, yg mana
     * getAllowedUnitKodes() otomatis balikin semua kode unit.
     */
    private function sqlUnitFilter( $_unit, $_alias )
    {
        if ( empty($_unit) || stristr($_unit, 'all') !== false ) {
            $allowed = $this->getAllowedUnitKodes();

            if ( empty($allowed) ) {
                return '';
            }

            return " and ".$_alias.".kode in ('".implode("', '", $allowed)."') ";
        }

        return " and ".$_alias.".kode = '".$_unit."' ";
    }

    private function getAllowedUnitKodes()
    {
        if ( $this->allowedUnitKodes === null ) {
            $m_wil = new \Model\Storage\Wilayah_model();
            $units = $m_wil->getDataUnit(1, $this->userid);

            $this->allowedUnitKodes = array();
            if ( !empty($units) ) {
                foreach ($units as $v) {
                    $this->allowedUnitKodes[] = $v['kode'];
                }
            }
        }

        return $this->allowedUnitKodes;
    }

    /**************************************************************************************
     * BAGIAN 1 - METRIK OPERASIONAL (KESIAPAN PANEN)
     **************************************************************************************/
    /**
     * Estimasi tonase siap panen (umur $_umur_min - $_umur_max, default 28-35 hari) dari
     * siklus yang MASIH AKTIF (belum tutup_siklus). Pola query sama persis dengan
     * SisaStokAyamMinMax::getData (lhk umur terakhir per noreg + populasi realisasi
     * terima_doc/adjin_doc dikurangi panen real_sj), cuma level agregasinya per-noreg dulu
     * (bukan langsung di-group per unit+umur) supaya bisa dipakai juga utk bucket BW.
     * BW: prioritas konfir.bb_rata2 (BW aktual saat mitra submit Konfirmasi Panen), fallback ke
     * lhk.bb (BW estimasi harian terakhir) kalau siklus itu belum ada konfirmasi panen.
     */
    public function getKesiapanPanen()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $umur_min = !empty($params['umur_min']) ? (int) $params['umur_min'] : 28;
            $umur_max = !empty($params['umur_max']) ? (int) $params['umur_max'] : 35;
            $tanggal = date('Y-m-d').' 23:59:59.999';

            $data = $this->mappingKesiapanPanen( $unit, $tanggal );

            $siap_panen = array();
            $bw_kecil = array('ekor' => 0, 'tonase' => 0);
            $bw_besar = array('ekor' => 0, 'tonase' => 0);
            $bw_jumbo = array('ekor' => 0, 'tonase' => 0);

            $total_tonase_siap = 0;
            $total_ekor_siap = 0;
            $per_unit = array();

            if ( !empty($data) ) {
                foreach ($data as $v) {
                    // Distribusi BW dihitung dari siklus yg SUDAH mendekati/masuk umur panen
                    // (>= umur_min), bukan cuma yg persis di window siap panen, biar lebih
                    // kebaca trennya buat marketing (bukan cuma yg siap hari ini).
                    if ( $v['umur'] >= $umur_min ) {
                        if ( $v['bb'] >= 1.75 && $v['bb'] <= 1.99 ) {
                            $bw_kecil['ekor'] += $v['sisa_ekor'];
                            $bw_kecil['tonase'] += $v['tonase'];
                        } else if ( $v['bb'] >= 2.00 && $v['bb'] <= 2.50 ) {
                            $bw_besar['ekor'] += $v['sisa_ekor'];
                            $bw_besar['tonase'] += $v['tonase'];
                        } else if ( $v['bb'] >= 2.51 ) {
                            $bw_jumbo['ekor'] += $v['sisa_ekor'];
                            $bw_jumbo['tonase'] += $v['tonase'];
                        }
                    }

                    if ( $v['umur'] >= $umur_min && $v['umur'] <= $umur_max ) {
                        $total_tonase_siap += $v['tonase'];
                        $total_ekor_siap += $v['sisa_ekor'];
                        $siap_panen[] = $v;

                        $kode_unit = !empty($v['unit']) ? $v['unit'] : 'TIDAK DIKETAHUI';
                        if ( !isset($per_unit[ $kode_unit ]) ) {
                            $per_unit[ $kode_unit ] = array('unit' => $kode_unit, 'tonase' => 0, 'ekor' => 0);
                        }
                        $per_unit[ $kode_unit ]['tonase'] += $v['tonase'];
                        $per_unit[ $kode_unit ]['ekor'] += $v['sisa_ekor'];
                    }
                }
            }

            $per_unit = array_values($per_unit);
            usort($per_unit, function($a, $b) { return $b['tonase'] <=> $a['tonase']; });
            foreach ($per_unit as &$v_pu) {
                $v_pu['unit'] = strtoupper($v_pu['unit']);
                $v_pu['tonase'] = round($v_pu['tonase'], 1);
                $v_pu['ekor'] = (int) $v_pu['ekor'];
            }
            unset($v_pu);

            $detail = array();
            foreach ($siap_panen as $v_sp) {
                $detail[] = array(
                    'unit' => strtoupper(!empty($v_sp['unit']) ? $v_sp['unit'] : 'TIDAK DIKETAHUI'),
                    'nama_plasma' => strtoupper(!empty($v_sp['nama_plasma']) ? $v_sp['nama_plasma'] : '-'),
                    'noreg' => $v_sp['noreg'],
                    'umur' => (int) $v_sp['umur'],
                    'bb' => round($v_sp['bb'], 2),
                    'sisa_ekor' => (int) $v_sp['sisa_ekor'],
                    'tonase' => round($v_sp['tonase'], 1),
                );
            }
            usort($detail, function($a, $b) { return $b['tonase'] <=> $a['tonase']; });

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'total_tonase_siap' => round($total_tonase_siap, 1),
                'total_ekor_siap' => (int) $total_ekor_siap,
                'jml_siklus_siap' => count($siap_panen),
                'umur_min' => $umur_min,
                'umur_max' => $umur_max,
                'bw_kecil' => array('ekor' => (int) $bw_kecil['ekor'], 'tonase' => round($bw_kecil['tonase'], 1)),
                'bw_besar' => array('ekor' => (int) $bw_besar['ekor'], 'tonase' => round($bw_besar['tonase'], 1)),
                'bw_jumbo' => array('ekor' => (int) $bw_jumbo['ekor'], 'tonase' => round($bw_jumbo['tonase'], 1)),
                'per_unit' => $per_unit,
                'detail' => $detail,
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    private function mappingKesiapanPanen( $_unit, $_tanggal )
    {
        $sql_unit = $this->sqlUnitFilter( $_unit, 'w' );

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.noreg,
                data.kode as unit,
                data.nama_plasma,
                data.umur,
                data.bb,
                data.sisa_ekor,
                data.tonase
            from
            (
                select
                    w.kode,
                    l.noreg,
                    m.nama as nama_plasma,
                    l.umur,
                    isnull(kf.bb_rata2, l.bb) as bb,
                    ((td.jml_ekor+isnull(ad.jumlah, 0)) - l.ekor_mati - isnull(panen.ekor, 0)) as sisa_ekor,
                    (((td.jml_ekor+isnull(ad.jumlah, 0)) - l.ekor_mati - isnull(panen.ekor, 0))) * isnull(kf.bb_rata2, l.bb) as tonase
                from
                (
                    select l1.* from lhk l1
                    right join
                        (select max(tanggal) as tanggal, noreg from lhk where tanggal <= '".$_tanggal."' group by noreg) l2
                        on
                            l1.tanggal = l2.tanggal and
                            l1.noreg = l2.noreg
                ) l
                left join
                    rdim_submit rs
                    on
                        l.noreg = rs.noreg
                left join
                    kandang k
                    on
                        rs.kandang = k.id
                left join
                    wilayah w
                    on
                        k.unit = w.id
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        mm.nim = rs.nim
                left join
                    mitra m
                    on
                        mm.mitra = m.id
                left join
                    (
                        select k1.* from konfir k1
                        right join
                            (select max(id) as id, noreg from konfir group by noreg) k2
                            on
                                k1.id = k2.id
                    ) kf
                    on
                        kf.noreg = l.noreg
                left join
                    (select * from tutup_siklus where tgl_tutup <= '".$_tanggal."') ts
                    on
                        l.noreg = ts.noreg
                left join
                    (
                        select od.noreg, td.* from
                        (
                            select td1.* from terima_doc td1
                            right join
                                (select max(id) as id, no_order from terima_doc group by no_order) td2
                                on
                                    td1.id = td2.id
                        ) td
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, no_order from order_doc group by no_order) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                td.no_order = od.no_order
                    ) td
                    on
                        l.noreg = td.noreg
                left join
                    (select noreg, sum(jumlah) as jumlah from adjin_doc group by noreg) ad
                    on
                        l.noreg = ad.noreg
                left join
                    (select noreg, sum(netto_ekor) as ekor, sum(netto_kg) as tonase from real_sj where tgl_panen <= '".$_tanggal."' group by noreg) panen
                    on
                        l.noreg = panen.noreg
                where
                    ts.id is null and
                    td.noreg is not null
                    ".$sql_unit."
            ) data
            where
                data.sisa_ekor > 0 and
                data.tonase > 0
            order by
                data.umur desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**************************************************************************************
     * BAGIAN 2 - PRICING INTELLIGENCE
     **************************************************************************************/
    /**
     * Rata-rata harga jual realisasi (tertimbang tonase) per hari, N hari terakhir. Sumbernya
     * det_real_sj.harga (harga per kg realisasi tiap SJ) join real_sj (tanggal panen/kirim).
     */
    public function getHargaRealisasi()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $hari = !empty($params['hari']) ? (int) $params['hari'] : 14;

            $end_date = date('Y-m-d').' 23:59:59.999';
            $start_date = date('Y-m-d', strtotime('-'.($hari-1).' days')).' 00:00:00.000';

            $sql_unit = $this->sqlUnitFilter( $unit, 'w' );

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    convert(varchar(10), rs.tgl_panen, 120) as tanggal,
                    sum(drs.harga * drs.tonase) / nullif(sum(drs.tonase), 0) as harga_rata,
                    sum(drs.tonase) as tonase
                from det_real_sj drs
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                where
                    rs.tgl_panen between '".$start_date."' and '".$end_date."' and
                    drs.harga > 0
                    ".$sql_unit."
                group by
                    convert(varchar(10), rs.tgl_panen, 120)
                order by
                    tanggal asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $data = array('tanggal' => array(), 'harga' => array(), 'tonase' => array());
            if ( $d_conf->count() > 0 ) {
                foreach ($d_conf->toArray() as $v) {
                    $data['tanggal'][] = $v['tanggal'];
                    $data['harga'][] = round((float) $v['harga_rata'], 0);
                    $data['tonase'][] = round((float) $v['tonase'], 1);
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**************************************************************************************
     * BAGIAN 3 - SALES PERFORMANCE
     **************************************************************************************/
    /**
     * Volume penjualan (kg & nilai Rp) per kategori pelanggan (tipe_pelanggan), dipecah per unit
     * asal panen, N hari terakhir. Kategorinya dinamis ikut isi master tipe_pelanggan (tidak
     * di-hardcode broker/bakul/RPA/dst - biar selalu sesuai data aktual).
     */
    public function getVolumeChannel()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $hari = !empty($params['hari']) ? (int) $params['hari'] : 30;

            $end_date = date('Y-m-d').' 23:59:59.999';
            $start_date = date('Y-m-d', strtotime('-'.($hari-1).' days')).' 00:00:00.000';

            $sql_unit = $this->sqlUnitFilter( $unit, 'w' );

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    isnull(tp.nama, 'LAINNYA') as channel,
                    isnull(w.kode, '-') as unit,
                    sum(drs.tonase) as volume_kg,
                    sum(drs.harga * drs.tonase) as nilai
                from det_real_sj drs
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan group by nomor) p2
                            on
                                p1.id = p2.id
                    ) plg
                    on
                        drs.no_pelanggan = plg.nomor
                left join
                    tipe_pelanggan tp
                    on
                        plg.tipe_plg = tp.id
                where
                    rs.tgl_panen between '".$start_date."' and '".$end_date."'
                    ".$sql_unit."
                group by
                    tp.nama,
                    w.kode
                order by
                    channel asc,
                    volume_kg desc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $data = array('channel' => array(), 'unit' => array(), 'volume_kg' => array(), 'nilai' => array());
            if ( $d_conf->count() > 0 ) {
                foreach ($d_conf->toArray() as $v) {
                    $data['channel'][] = strtoupper($v['channel']);
                    $data['unit'][] = strtoupper($v['unit']);
                    $data['volume_kg'][] = round((float) $v['volume_kg'], 1);
                    $data['nilai'][] = round((float) $v['nilai'], 0);
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Volume penjualan per unit (tonase, bukan kg), N hari terakhir.
     */
    public function getVolumeUnit()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $hari = !empty($params['hari']) ? (int) $params['hari'] : 30;

            $end_date = date('Y-m-d').' 23:59:59.999';
            $start_date = date('Y-m-d', strtotime('-'.($hari-1).' days')).' 00:00:00.000';

            $sql_unit = $this->sqlUnitFilter( $unit, 'w' );

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    isnull(w.kode, 'TIDAK DIKETAHUI') as unit,
                    sum(drs.tonase) as volume_kg,
                    sum(drs.ekor) as ekor,
                    sum(drs.harga * drs.tonase) as nilai
                from det_real_sj drs
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                where
                    rs.tgl_panen between '".$start_date."' and '".$end_date."'
                    ".$sql_unit."
                group by
                    w.kode
                order by
                    volume_kg desc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $data = array('unit' => array(), 'volume_ton' => array(), 'ekor' => array(), 'nilai' => array());
            if ( $d_conf->count() > 0 ) {
                foreach ($d_conf->toArray() as $v) {
                    $data['unit'][] = strtoupper($v['unit']);
                    $data['volume_ton'][] = round((float) $v['volume_kg'] / 1000, 2);
                    $data['ekor'][] = (int) $v['ekor'];
                    $data['nilai'][] = round((float) $v['nilai'], 0);
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Top 10 bakul dgn volume ambilan (tonase) terbanyak, N hari terakhir.
     */
    public function getTopBakulVolume()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $hari = !empty($params['hari']) ? (int) $params['hari'] : 30;

            $end_date = date('Y-m-d').' 23:59:59.999';
            $start_date = date('Y-m-d', strtotime('-'.($hari-1).' days')).' 00:00:00.000';

            $sql_unit = $this->sqlUnitFilter( $unit, 'w' );

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 10
                    drs.no_pelanggan,
                    isnull(plg.nama, drs.no_pelanggan) as nama_pelanggan,
                    sum(drs.tonase) as volume_kg,
                    sum(drs.harga * drs.tonase) as nilai
                from det_real_sj drs
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan group by nomor) p2
                            on
                                p1.id = p2.id
                    ) plg
                    on
                        drs.no_pelanggan = plg.nomor
                where
                    rs.tgl_panen between '".$start_date."' and '".$end_date."'
                    ".$sql_unit."
                group by
                    drs.no_pelanggan,
                    plg.nama
                order by
                    volume_kg desc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $data = null;
            if ( $d_conf->count() > 0 ) {
                $data = array();
                foreach ($d_conf->toArray() as $v) {
                    $data[] = array(
                        'nama_pelanggan' => strtoupper($v['nama_pelanggan']),
                        'volume_ton' => round((float) $v['volume_kg'] / 1000, 2),
                        'nilai' => round((float) $v['nilai'], 0),
                    );
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Hutang bakul (sisa tagihan yg belum dibayar bakul ke perusahaan, per hari ini) per unit.
     * Pola query sama dgn SisaTagihanPerPelanggan::list_data - det_real_sj_inv (tagihan per
     * invoice) dikurangi cn/dn/penyesuaian/bayar dari det_pembayaran_pelanggan, cuma level
     * agregasinya langsung di-sum per unit (bukan per invoice/pelanggan).
     */
    public function getHutangBakul()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $tanggal = date('Y-m-d').' 23:59:59.999';

            $sql_unit = $this->sqlUnitFilter( $unit, 'w' );

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    isnull(w.kode, 'TIDAK DIKETAHUI') as unit,
                    sum((drsi.total+isnull(dpp.dn, 0))-(isnull(dpp.cn, 0)+isnull(dpp.penyesuaian, 0)+isnull(dpp.bayar, 0))) as hutang
                from det_real_sj_inv drsi
                left join
                    (select no_pelanggan, no_do, no_sj, id_header from det_real_sj group by no_pelanggan, no_do, no_sj, id_header) drs
                    on
                        drsi.no_sj = drs.no_sj
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                left join
                    (
                        select
                            dpp.no_inv,
                            sum(dpp.cn) as cn,
                            sum(dpp.dn) as dn,
                            sum(dpp.penyesuaian) as penyesuaian,
                            sum(dpp.tagihan - (dpp.penyesuaian+dpp.sisa_tagihan)) as bayar
                        from det_pembayaran_pelanggan dpp
                        left join
                            pembayaran_pelanggan pp
                            on
                                dpp.id_header = pp.id
                        where
                            pp.tgl_bayar <= '".$tanggal."'
                        group by
                            dpp.no_inv
                    ) dpp
                    on
                        dpp.no_inv = drsi.no_inv
                where
                    rs.tgl_panen <= '".$tanggal."'
                    ".$sql_unit."
                group by
                    w.kode
                having
                    sum((drsi.total+isnull(dpp.dn, 0))-(isnull(dpp.cn, 0)+isnull(dpp.penyesuaian, 0)+isnull(dpp.bayar, 0))) > 0
                order by
                    hutang desc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $data = array('unit' => array(), 'hutang' => array(), 'jml_bakul' => 0);
            if ( $d_conf->count() > 0 ) {
                foreach ($d_conf->toArray() as $v) {
                    $data['unit'][] = strtoupper($v['unit']);
                    $data['hutang'][] = round((float) $v['hutang'], 0);
                }
            }

            $d_bakul = $this->mappingHutangBakulPerPelanggan( $unit, $tanggal );
            $data['jml_bakul'] = !empty($d_bakul) ? count($d_bakul) : 0;

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Detail hutang bakul utk 1 unit (dipanggil pas klik bar di chart Hutang Bakul per Unit) -
     * daftar bakul (pelanggan) yg msh py sisa tagihan, di-sum per pelanggan + umur invoice
     * tertua (dari tgl_panen invoice yg blm lunas, biar keliatan mana yg paling lama nunggak).
     */
    public function getHutangBakulDetail()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $tanggal = date('Y-m-d').' 23:59:59.999';

            $data = $this->mappingHutangBakulPerPelanggan( $unit, $tanggal );

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Top 10 bakul dgn hutang (sisa tagihan) terbanyak per hari ini, lintas semua unit (atau
     * di-filter ke unit dropdown atas kalau dipilih). Pola sama dgn getHutangBakulDetail, cuma
     * di-limit top 10 & gak di-scope ke 1 unit tertentu.
     */
    public function getTopBakulHutang()
    {
        $params = $this->input->post('params');

        try {
            $unit = !empty($params['unit']) ? $params['unit'] : 'all';
            $show_all = !empty($params['show_all']) ? true : false;
            $tanggal = date('Y-m-d').' 23:59:59.999';

            $data = $this->mappingHutangBakulPerPelanggan( $unit, $tanggal, $show_all ? null : 10 );

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    private function mappingHutangBakulPerPelanggan( $_unit, $_tanggal, $_limit = null )
    {
        $sql_unit = $this->sqlUnitFilter( $_unit, 'w' );
        $sql_top = !empty($_limit) ? 'top '.((int) $_limit).' ' : '';

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select ".$sql_top."
                data.no_pelanggan,
                isnull(data.nama_pelanggan, '-') as nama_pelanggan,
                sum(data.sisa) as hutang,
                max(data.umur_invoice) as umur_tertua,
                count(*) as jml_invoice
            from
            (
                select
                    drs.no_pelanggan,
                    plg.nama as nama_pelanggan,
                    (DATEDIFF(day, rs.tgl_panen, '".$_tanggal."')) + 1 as umur_invoice,
                    ((drsi.total+isnull(dpp.dn, 0))-(isnull(dpp.cn, 0)+isnull(dpp.penyesuaian, 0)+isnull(dpp.bayar, 0))) as sisa
                from det_real_sj_inv drsi
                left join
                    (select no_pelanggan, no_do, no_sj, id_header from det_real_sj group by no_pelanggan, no_do, no_sj, id_header) drs
                    on
                        drsi.no_sj = drs.no_sj
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) p2
                            on
                                p1.id = p2.id
                    ) plg
                    on
                        plg.nomor = drs.no_pelanggan
                left join
                    (
                        select
                            dpp.no_inv,
                            sum(dpp.cn) as cn,
                            sum(dpp.dn) as dn,
                            sum(dpp.penyesuaian) as penyesuaian,
                            sum(dpp.tagihan - (dpp.penyesuaian+dpp.sisa_tagihan)) as bayar
                        from det_pembayaran_pelanggan dpp
                        left join
                            pembayaran_pelanggan pp
                            on
                                dpp.id_header = pp.id
                        where
                            pp.tgl_bayar <= '".$_tanggal."'
                        group by
                            dpp.no_inv
                    ) dpp
                    on
                        dpp.no_inv = drsi.no_inv
                where
                    rs.tgl_panen <= '".$_tanggal."'
                    ".$sql_unit."
            ) data
            where
                data.sisa > 0
            group by
                data.no_pelanggan,
                data.nama_pelanggan
            order by
                hutang desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**
     * Step 1 export PDF detail invoice hutang bakul: encrypt params dulu (pola sama dgn
     * exportExcel di report2 lain) supaya no_pelanggan gak lewat URL polos.
     */
    public function cekExportInvoicePdf()
    {
        $params = $this->input->post('params');

        try {
            $params_encrypt = exEncrypt( json_encode($params) );

            $this->result['status'] = 1;
            $this->result['content'] = $params_encrypt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Step 2 - stream PDF detail per-invoice yg blm lunas utk 1 bakul (dipanggil pas klik
     * "X invoice" di modal Detail Hutang Bakul).
     */
    public function exportInvoicePdf( $params_encrypt )
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $no_pelanggan = $params['no_pelanggan'];
        $unit = !empty($params['unit']) ? $params['unit'] : 'all';
        $tanggal = date('Y-m-d').' 23:59:59.999';

        $d_invoice = $this->mappingHutangBakulInvoiceDetail( $no_pelanggan, $unit, $tanggal );

        $content['data'] = $d_invoice['rows'];
        $content['nama_pelanggan'] = $d_invoice['nama_pelanggan'];
        $content['nama_perusahaan'] = $d_invoice['nama_perusahaan'];
        $content['tanggal_cetak'] = date('d-m-Y H:i');

        $res_view_html = $this->load->view('report/dashboard_marketing/export_invoice_pdf', $content, TRUE);

        $this->load->library('PDFGenerator');
        $this->pdfgenerator->download( $res_view_html, 'detail-hutang-bakul-'.preg_replace('/[^A-Za-z0-9]/', '', $no_pelanggan), 'a4', 'portrait' );
    }

    private function mappingHutangBakulInvoiceDetail( $_no_pelanggan, $_unit, $_tanggal )
    {
        $sql_unit = $this->sqlUnitFilter( $_unit, 'w' );
        $no_pelanggan_safe = str_replace("'", "''", $_no_pelanggan);

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.unit,
                data.nama_perusahaan,
                data.nama_plasma,
                data.no_kandang,
                data.no_inv,
                data.no_do,
                data.tgl_panen,
                data.nama_pelanggan,
                data.total,
                data.cn,
                data.dn,
                data.penyesuaian,
                data.bayar,
                data.sisa,
                data.umur_invoice
            from
            (
                select
                    isnull(w.kode, 'TIDAK DIKETAHUI') as unit,
                    isnull(prs.perusahaan, '-') as nama_perusahaan,
                    isnull(mitra.nama, '-') as nama_plasma,
                    isnull(k.kandang, '-') as no_kandang,
                    drsi.no_inv,
                    drs.no_do,
                    rs.tgl_panen,
                    plg.nama as nama_pelanggan,
                    drsi.total,
                    isnull(dpp.cn, 0) as cn,
                    isnull(dpp.dn, 0) as dn,
                    isnull(dpp.penyesuaian, 0) as penyesuaian,
                    isnull(dpp.bayar, 0) as bayar,
                    ((drsi.total+isnull(dpp.dn, 0))-(isnull(dpp.cn, 0)+isnull(dpp.penyesuaian, 0)+isnull(dpp.bayar, 0))) as sisa,
                    (DATEDIFF(day, rs.tgl_panen, '".$_tanggal."')) + 1 as umur_invoice
                from det_real_sj_inv drsi
                left join
                    (select no_pelanggan, no_do, no_sj, id_header from det_real_sj group by no_pelanggan, no_do, no_sj, id_header) drs
                    on
                        drsi.no_sj = drs.no_sj
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                left join
                    (select nim, noreg, kandang from rdim_submit group by nim, noreg, kandang) rdim
                    on
                        rs.noreg = rdim.noreg
                left join
                    (select max(mitra) as id_mitra, nim from mitra_mapping group by nim) mm
                    on
                        rdim.nim = mm.nim
                left join
                    mitra mitra
                    on
                        mm.id_mitra = mitra.id
                left join
                    kandang k
                    on
                        rdim.kandang = k.id
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        mitra.perusahaan = prs.kode
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) p2
                            on
                                p1.id = p2.id
                    ) plg
                    on
                        plg.nomor = drs.no_pelanggan
                left join
                    (
                        select
                            dpp.no_inv,
                            sum(dpp.cn) as cn,
                            sum(dpp.dn) as dn,
                            sum(dpp.penyesuaian) as penyesuaian,
                            sum(dpp.tagihan - (dpp.penyesuaian+dpp.sisa_tagihan)) as bayar
                        from det_pembayaran_pelanggan dpp
                        left join
                            pembayaran_pelanggan pp
                            on
                                dpp.id_header = pp.id
                        where
                            pp.tgl_bayar <= '".$_tanggal."'
                        group by
                            dpp.no_inv
                    ) dpp
                    on
                        dpp.no_inv = drsi.no_inv
                where
                    drs.no_pelanggan = '".$no_pelanggan_safe."' and
                    rs.tgl_panen <= '".$_tanggal."'
                    ".$sql_unit."
            ) data
            where
                data.sisa > 0
            order by
                data.unit asc,
                data.tgl_panen asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $rows = null;
        $nama_pelanggan = '-';
        $nama_perusahaan = '-';
        if ( $d_conf->count() > 0 ) {
            $rows = $d_conf->toArray();
            $nama_pelanggan = !empty($rows[0]['nama_pelanggan']) ? $rows[0]['nama_pelanggan'] : '-';

            $list_perusahaan = array();
            foreach ($rows as $v_row) {
                if ( !empty($v_row['nama_perusahaan']) && !in_array($v_row['nama_perusahaan'], $list_perusahaan) ) {
                    $list_perusahaan[] = $v_row['nama_perusahaan'];
                }
            }
            $nama_perusahaan = !empty($list_perusahaan) ? implode(', ', $list_perusahaan) : '-';
        }

        return array('rows' => $rows, 'nama_pelanggan' => $nama_pelanggan, 'nama_perusahaan' => $nama_perusahaan);
    }
}
