<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PerformancePeternak extends Public_Controller {

    private $url;

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
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/chart/chart.js',
                'assets/report/performance_peternak/js/performance-peternak.js',
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/report/performance_peternak/css/performance-peternak.css',
            ));

            $data = $this->includes;

            $isMobile = true;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;

            $data['title_menu'] = 'Performance Peternak';
            $data['view'] = $this->load->view('report/performance_peternak/index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    /**
     * Kode unit yang jadi tanggung jawab user login, ditarik dari mapping karyawan -> unit_karyawan
     * (dicocokkan by nama, sama seperti pola di PenerimaanPakanMobile::get_mitra()). Penyuluh cuma
     * boleh lihat siklus di unit dia sendiri; kalau tidak ketemu mapping-nya, fallback ke semua unit.
     */
    private function getKodeUnitKaryawan()
    {
        $kode_unit = array();

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $d_karyawan = null;
        if ( $d_duser ) {
            $m_karyawan = new \Model\Storage\Karyawan_model();
            $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->orderBy('id', 'desc')->first();
        }

        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get();

            if ( $d_ukaryawan->count() > 0 ) {
                foreach ($d_ukaryawan->toArray() as $v_uk) {
                    if ( stristr($v_uk['unit'], 'all') === false ) {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('id', $v_uk['unit'])->first();
                        if ( $d_wil ) {
                            $kode_unit[] = $d_wil->kode;
                        }
                    } else {
                        $kode_unit = $this->getAllKodeUnit();
                        break;
                    }
                }
            }
        }

        if ( empty($kode_unit) ) {
            $kode_unit = $this->getAllKodeUnit();
        }

        return $kode_unit;
    }

    private function getAllKodeUnit()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "select kode from wilayah where kode is not null group by kode";
        $d_wil = $m_conf->hydrateRaw($sql);

        $kode_unit = array();
        if ( $d_wil->count() > 0 ) {
            foreach ($d_wil->toArray() as $v) {
                $kode_unit[] = $v['kode'];
            }
        }

        return $kode_unit;
    }

    /**
     * Pencarian siklus (noreg) buat dropdown, dibatasi unit tanggung jawab user login dan cuma
     * yang belum tutup siklus (fokusnya "kontrol stok" siklus yang lagi berjalan).
     */
    public function getPlasma()
    {
        $search = $this->input->get('search');
        $kode_unit = $this->getKodeUnitKaryawan();

        if ( empty($kode_unit) ) {
            echo json_encode(array());
            return;
        }

        $sql_search = "";
        if ( !empty($search) ) {
            $sql_search = "and (rs.noreg like '%".$search."%' or m.nama like '%".$search."%')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rs.noreg as id,
                UPPER(w.kode+' | '+m.nama+' (KDG:'+cast(k.kandang as varchar(2))+')') as text
            from rdim_submit rs
            left join
                tutup_siklus ts
                on
                    ts.noreg = rs.noreg
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
                kandang k
                on
                    rs.kandang = k.id
            left join
                wilayah w
                on
                    w.id = k.unit
            where
                ts.id is null and
                w.kode in ('".implode("', '", $kode_unit)."')
                ".$sql_search."
            order by
                w.kode asc,
                m.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        echo json_encode($data);
    }

    /**
     * Posisi stok TERKINI per barang untuk 1 siklus (noreg). Logikanya sama persis dengan
     * PosisiStokSiklus::mappingDataReport (Saldo Awal + Debet - Kredit, termasuk pemakaian LHK
     * per umur), cuma start_date dibuat sangat awal supaya "saldo awal" mencakup seluruh histori
     * siklus itu, jadi hasil akhirnya (jumlah/total) = posisi stok saat ini, bukan pergerakan 1 bulan.
     * DOC dikecualikan sama seperti versi desktop (bukan barang yang "disisakan").
     */
    public function mappingDataReport( $_noreg, $_end_date )
    {
        $_start_date = '2000-01-01 00:00:00.000';

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.kode_barang,
                data.jenis_barang,
                data.nama_barang,
                sum(jml_sa) as jml_sa,
                sum(jml_debet) as jml_debet,
                sum(jml_kredit) as jml_kredit,
                (sum(jml_sa)+sum(jml_debet))-sum(jml_kredit) as jumlah,
                (sum(sa)+sum(debet))-sum(kredit) as total
            from
            (
                select
                    data.*,
                    brg.nama as nama_barang
                from
                (
                    /* SALDO AWAL */
                    select
                        data.noreg,
                        data.kode_barang,
                        data.jenis_barang,
                        sum(isnull(data.jml_debet, 0)-isnull(data.jml_kredit, 0)) as jml_sa,
                        sum(isnull(data.debet, 0) - isnull(data.kredit, 0)) as sa,
                        0 as jml_debet,
                        0 as debet,
                        0 as jml_kredit,
                        0 as kredit
                    from
                    (
                        select
                            dss.noreg,
                            dss.tgl_trans as tanggal,
                            dss.kode_barang,
                            dss.jenis_barang,
                            dss.jumlah as jml_debet,
                            (dss.jumlah * dss.hrg_beli) as debet,
                            0 as jml_kredit,
                            0 as kredit
                        from det_stok_siklus dss

                        union all

                        select
                            dss.noreg,
                            dsts.tgl_trans as tanggal,
                            dsts.kode_barang,
                            dss.jenis_barang,
                            0 as jml_debet,
                            0 as debet,
                            dsts.jumlah as jml_kredit,
                            (dsts.jumlah * dss.hrg_beli) as kredit
                        from det_stok_trans_siklus dsts
                        left join
                            det_stok_siklus dss
                            on
                                dsts.id_header = dss.id
                    ) data
                    where
                        data.noreg = '".$_noreg."' and
                        data.tanggal < '".$_start_date."'
                    group by
                        data.noreg,
                        data.kode_barang,
                        data.jenis_barang
                    having
                        sum(isnull(data.jml_debet, 0)-isnull(data.jml_kredit, 0)) <> 0 and
                        sum(isnull(data.debet, 0) - isnull(data.kredit, 0)) <> 0
                    /* END - SALDO AWAL */

                    union all

                    /* MASUK */
                    select
                        dss.noreg,
                        dss.kode_barang,
                        dss.jenis_barang,
                        0 as jml_sa,
                        0 as sa,
                        dss.jumlah as jml_debet,
                        (dss.jumlah * dss.hrg_beli) as debet,
                        0 as jml_kredit,
                        0 as kredit
                    from det_stok_siklus dss
                    where
                        dss.noreg = '".$_noreg."' and
                        dss.tgl_trans between '".$_start_date."' and '".$_end_date."'
                    /* END - MASUK */

                    union all

                    /* KELUAR */
                    select
                        dss.noreg,
                        dsts.kode_barang,
                        dss.jenis_barang,
                        0 as jml_sa,
                        0 as sa,
                        0 as jml_debet,
                        0 as debet,
                        dsts.jumlah as jml_kredit,
                        (dsts.jumlah * dss.hrg_beli) as kredit
                    from det_stok_trans_siklus dsts
                    left join
                        det_stok_siklus dss
                        on
                            dsts.id_header = dss.id
                    where
                        dss.noreg = '".$_noreg."' and
                        dsts.tgl_trans between '".$_start_date."' and '".$_end_date."'
                    /* END - KELUAR */
                ) data
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (
                                select max(id) as id, kode from barang group by kode
                            ) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        data.kode_barang = brg.kode
                where
                    data.jenis_barang <> 'doc'
            ) data
            group by
                data.kode_barang,
                data.jenis_barang,
                data.nama_barang
            order by
                data.jenis_barang asc,
                data.nama_barang asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getSiklusInfo( $_noreg )
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rs.noreg,
                m.nama as nama_plasma,
                k.kandang,
                w.kode as unit,
                rs.tgl_docin,
                case
                    when ts.id is not null then
                        'SUDAH TUTUP SIKLUS'
                    else
                        'BELUM TUTUP SIKLUS'
                end as status
            from rdim_submit rs
            left join
                tutup_siklus ts
                on
                    ts.noreg = rs.noreg
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
                kandang k
                on
                    rs.kandang = k.id
            left join
                wilayah w
                on
                    w.id = k.unit
            where
                rs.noreg = '".$_noreg."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        return $data;
    }

    /**
     * Sisa ekor & tonase ayam TERKINI untuk 1 siklus (noreg). Pola sama persis dengan
     * SisaStokAyam::getData, cuma difilter 1 noreg saja: populasi = terima_doc.jml_ekor
     * (realisasi chick-in) + adjin_doc (susulan), dikurangi lhk.ekor_mati (kumulatif,
     * diambil dari baris LHK dengan umur terakhir s/d tanggal cutoff), dikurangi hasil
     * panen (sum real_sj.netto_ekor/netto_kg, bisa panen bertahap).
     */
    public function getStokAyam( $_noreg, $_end_date )
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                od.noreg,
                td.datang as tgl_chick_in,
                lhk.tanggal as tgl_datang_ppl,
                lhk.umur,
                (td.jml_ekor+isnull(ad.jumlah, 0)) as populasi,
                isnull(lhk.ekor_mati, 0) as ekor_mati_real,
                lhk.bb as bw_rata_lhk,
                panen.tgl_panen_terakhir as tgl_panen_terakhir,
                isnull(panen.ekor, 0) as ekor_jual,
                isnull(panen.tonase, 0) as tonase_jual,
                (td.jml_ekor+isnull(ad.jumlah, 0)) - isnull(lhk.ekor_mati, 0) - isnull(panen.ekor, 0) as sisa_ekor,
                (((td.jml_ekor+isnull(ad.jumlah, 0)) - isnull(lhk.ekor_mati, 0)) * lhk.bb) - isnull(panen.tonase, 0) as sisa_tonase
            from
            (
                select td1.* from terima_doc td1
                right join
                    (select max(id) as id, no_order from terima_doc td group by no_order) td2
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
            left join
                (
                    select l1.tanggal, l1.noreg, l1.umur, l1.ekor_mati, l1.bb from lhk l1
                    right join
                        (select max(umur) as umur, noreg from lhk where noreg = '".$_noreg."' and tanggal <= '".$_end_date."' group by noreg) l2
                        on
                            l1.umur = l2.umur and
                            l1.noreg = l2.noreg
                ) lhk
                on
                    lhk.noreg = od.noreg
            left join
                (
                    select noreg, sum(netto_ekor) as ekor, sum(netto_kg) as tonase, max(tgl_panen) as tgl_panen_terakhir from real_sj where noreg = '".$_noreg."' and tgl_panen <= '".$_end_date."' group by noreg
                ) panen
                on
                    panen.noreg = od.noreg
            left join
                (
                    select noreg, sum(jumlah) as jumlah from adjin_doc where noreg = '".$_noreg."' group by noreg
                ) ad
                on
                    ad.noreg = od.noreg
            where
                od.noreg = '".$_noreg."' and
                td.datang <= '".$_end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        return $data;
    }

    /**
     * Deret harian BW, FCR, ADG, FI, dan Deplesi (mortalitas kumulatif %) untuk 1 siklus, sumbernya
     * kolom lhk.bb/lhk.fcr/lhk.adg yang SUDAH dihitung & disimpan tiap submit LHK (lihat LHK.php,
     * rumusnya identik di semua titik submit), jadi di sini tinggal dibaca per umur, bukan
     * dihitung ulang. adg disimpan dalam KG/hari (sama satuan dgn lhk.bb) makanya dikonversi
     * ke gram/hari biar familiar buat PPL. Deplesi = ekor_mati kumulatif / populasi awal.
     *
     * FI (Feed Intake) belum ada rumusnya di codebase manapun, jadi dibikin sendiri di sini
     * mengikuti pola pembagi yang SUDAH dipakai FCR (LHK.php): ekor_sisa = populasi - ekor_mati
     * (bukan populasi awal). lhk.pakai_pakan kumulatif dalam ZAK (sama seperti fcr), *50 => KG,
     * dibagi ekor_sisa & umur => rata-rata gram/ekor/hari sejak awal siklus (rate kumulatif,
     * satu gaya dengan ADG yang juga rate kumulatif, biar dua-duanya sepadan saat dibandingkan).
     */
    public function mappingPerforma( $_noreg, $_end_date, $_populasi )
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                umur,
                isnull(bb, 0) as bb,
                isnull(fcr, 0) as fcr,
                isnull(adg, 0) as adg,
                isnull(ip, 0) as ip,
                isnull(ekor_mati, 0) as ekor_mati,
                isnull(pakai_pakan, 0) as pakai_pakan
            from lhk
            where
                noreg = '".$_noreg."' and
                tanggal <= '".$_end_date."'
            order by umur asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 && $_populasi > 0 ) {
            $rows = $d_conf->toArray();

            $data = array(
                'umur' => array(),
                'bw' => array(),
                'fcr' => array(),
                'adg' => array(),
                'ip' => array(),
                'fi' => array(),
                'deplesi' => array(),
                'ekor_mati' => array(),
            );

            foreach ($rows as $row) {
                $umur = (int) $row['umur'];
                $ekor_sisa = $_populasi - (float) $row['ekor_mati'];
                $kg_konsumsi = ((float) $row['pakai_pakan']) * 50;

                $fi = 0;
                if ( $ekor_sisa > 0 && $umur > 0 ) {
                    $fi = ($kg_konsumsi * 1000) / $ekor_sisa / $umur;
                }

                $data['umur'][] = $umur;
                $data['bw'][] = round((float) $row['bb'], 3);
                $data['fcr'][] = round((float) $row['fcr'], 3);
                $data['adg'][] = round(((float) $row['adg']) * 1000, 1);
                $data['ip'][] = round((float) $row['ip'], 1);
                $data['fi'][] = round($fi, 1);
                $data['deplesi'][] = round(((float) $row['ekor_mati'] / $_populasi) * 100, 2);
                $data['ekor_mati'][] = (int) $row['ekor_mati'];
            }
        }

        return $data;
    }

    public function getData()
    {
        $params = $this->input->post('params');

        try {
            $noreg = trim($params['noreg']);

            $end_date = date('Y-m-d').' 23:59:59.999';

            $siklus = $this->getSiklusInfo( $noreg );
            $ayam = $this->getStokAyam( $noreg, $end_date );
            $data = $this->mappingDataReport( $noreg, $end_date );
            $performa = ( !empty($ayam['populasi']) ) ? $this->mappingPerforma( $noreg, $end_date, $ayam['populasi'] ) : null;

            $content['siklus'] = $siklus;
            $content['ayam'] = $ayam;
            $content['data'] = $data;
            $content['performa'] = $performa;
            $html = $this->load->view('report/performance_peternak/list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
            $this->result['performa'] = $performa;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Riwayat transaksi ekor ayam (chick-in, susulan DOC, mati harian, panen) untuk 1 siklus,
     * diurutkan tanggal + saldo berjalan. ekor_mati di tabel lhk itu kumulatif (lihat catatan di
     * getStokAyam), jadi mortalitas per event dihitung dari selisih terhadap baris umur sebelumnya.
     */
    public function mappingDetailAyam( $_noreg, $_end_date )
    {
        $m_conf = new \Model\Storage\Conf();

        $sql_chickin = "
            select
                td.datang as tanggal,
                td.jml_ekor as jumlah
            from
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
            where
                od.noreg = '".$_noreg."' and
                td.datang <= '".$_end_date."'
        ";
        $d_chickin = $m_conf->hydrateRaw( $sql_chickin );

        $sql_adjin = "
            select tanggal, jumlah, keterangan
            from adjin_doc
            where
                noreg = '".$_noreg."' and
                tanggal <= '".$_end_date."'
            order by tanggal asc
        ";
        $d_adjin = $m_conf->hydrateRaw( $sql_adjin );

        $sql_lhk = "
            select tanggal, umur, isnull(ekor_mati, 0) as ekor_mati
            from lhk
            where
                noreg = '".$_noreg."' and
                tanggal <= '".$_end_date."'
            order by umur asc
        ";
        $d_lhk = $m_conf->hydrateRaw( $sql_lhk );

        $sql_panen = "
            select tgl_panen as tanggal, netto_ekor as jumlah
            from real_sj
            where
                noreg = '".$_noreg."' and
                tgl_panen <= '".$_end_date."'
            order by tgl_panen asc
        ";
        $d_panen = $m_conf->hydrateRaw( $sql_panen );

        $events = array();

        if ( $d_chickin->count() > 0 ) {
            foreach ($d_chickin->toArray() as $v) {
                $events[] = array(
                    'tanggal' => $v['tanggal'],
                    'jenis' => 'CHICK IN',
                    'keterangan' => 'DOC Masuk',
                    'masuk' => (float) $v['jumlah'],
                    'keluar' => 0,
                );
            }
        }

        if ( $d_adjin->count() > 0 ) {
            foreach ($d_adjin->toArray() as $v) {
                $jumlah = (float) $v['jumlah'];
                $events[] = array(
                    'tanggal' => $v['tanggal'],
                    'jenis' => 'SUSULAN DOC',
                    'keterangan' => !empty($v['keterangan']) ? $v['keterangan'] : '-',
                    'masuk' => ($jumlah >= 0) ? $jumlah : 0,
                    'keluar' => ($jumlah < 0) ? abs($jumlah) : 0,
                );
            }
        }

        if ( $d_lhk->count() > 0 ) {
            $prev_mati = 0;
            foreach ($d_lhk->toArray() as $v) {
                $mati = (float) $v['ekor_mati'];
                $delta = $mati - $prev_mati;
                $prev_mati = $mati;

                if ( $delta > 0 ) {
                    $events[] = array(
                        'tanggal' => $v['tanggal'],
                        'jenis' => 'MATI',
                        'keterangan' => 'LHK Umur '.$v['umur'],
                        'masuk' => 0,
                        'keluar' => $delta,
                    );
                }
            }
        }

        if ( $d_panen->count() > 0 ) {
            foreach ($d_panen->toArray() as $v) {
                $events[] = array(
                    'tanggal' => $v['tanggal'],
                    'jenis' => 'PANEN',
                    'keterangan' => 'Realisasi SJ',
                    'masuk' => 0,
                    'keluar' => (float) $v['jumlah'],
                );
            }
        }

        usort($events, function($a, $b) {
            return strtotime($a['tanggal']) <=> strtotime($b['tanggal']);
        });

        $saldo = 0;
        foreach ($events as $key => $value) {
            $saldo += $value['masuk'] - $value['keluar'];
            $events[$key]['saldo'] = $saldo;
        }

        return array_reverse($events);
    }

    public function getDetailAyam()
    {
        $params = $this->input->post('params');

        try {
            $noreg = trim($params['noreg']);

            $end_date = date('Y-m-d').' 23:59:59.999';

            $data = $this->mappingDetailAyam( $noreg, $end_date );

            $content['data'] = $data;
            $html = $this->load->view('report/performance_peternak/detail_ayam', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Riwayat transaksi 1 barang (pakan/ovk) untuk 1 siklus, versi UNGROUPED dari
     * mappingDataReport (yang di-GROUP BY per barang), diurutkan tanggal + saldo berjalan.
     * Untuk baris MUTASI/RETUR, ikut resolve asal & tujuan (lewat kirim_pakan/kirim_voadip
     * utk mutasi, retur_pakan/retur_voadip utk retur) — id_header di det_stok_trans_siklus
     * mengarah ke det_stok_siklus (bukan ke header transaksi), jadi joinnya lewat kode_trans
     * (= no_order/no_retur dokumen sumbernya), bukan id_header.
     */
    public function mappingDetailBarang( $_noreg, $_kode_barang, $_end_date )
    {
        $_start_date = '2000-01-01 00:00:00.000';

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from
            (
                select
                    dss.tgl_trans as tanggal,
                    case
                        when dss.jenis_trans like '%order%' then 'DISTRIBUSI'
                        else dss.jenis_trans
                    end as jenis_trans,
                    dss.kode_trans,
                    dss.jumlah as masuk,
                    0 as keluar,
                    isnull(kp_in.jenis_kirim, kv_in.jenis_kirim) as mut_jenis_kirim,
                    isnull(kp_in.asal, kv_in.asal) as mut_asal,
                    isnull(kp_in.jenis_tujuan, kv_in.jenis_tujuan) as mut_jenis_tujuan,
                    isnull(kp_in.tujuan, kv_in.tujuan) as mut_tujuan,
                    isnull(rp_in.asal, rv_in.asal) as ret_asal,
                    isnull(rp_in.id_asal, rv_in.id_asal) as ret_id_asal,
                    isnull(rp_in.tujuan, rv_in.tujuan) as ret_tujuan,
                    isnull(rp_in.id_tujuan, rv_in.id_tujuan) as ret_id_tujuan
                from det_stok_siklus dss
                left join
                    kirim_pakan kp_in
                    on
                        dss.jenis_trans = 'MUTASI' and kp_in.no_order = dss.kode_trans
                left join
                    kirim_voadip kv_in
                    on
                        dss.jenis_trans = 'MUTASI' and kv_in.no_order = dss.kode_trans
                left join
                    retur_pakan rp_in
                    on
                        dss.jenis_trans = 'RETUR' and rp_in.no_retur = dss.kode_trans
                left join
                    retur_voadip rv_in
                    on
                        dss.jenis_trans = 'RETUR' and rv_in.no_retur = dss.kode_trans
                where
                    dss.noreg = '".$_noreg."' and
                    dss.kode_barang = '".$_kode_barang."' and
                    dss.tgl_trans between '".$_start_date."' and '".$_end_date."'

                union all

                select
                    dsts.tgl_trans as tanggal,
                    case
                        when dsts.tbl_name like '%terima%' then 'MUTASI'
                        when dsts.tbl_name like '%retur%' then 'RETUR'
                        else 'PEMAKAIAN'
                    end as jenis_trans,
                    case
                        when dsts.tbl_name = 'lhk' then 'LHK UMUR '+cast(l.umur as varchar(5))
                        else dsts.kode_trans
                    end as kode_trans,
                    0 as masuk,
                    dsts.jumlah as keluar,
                    isnull(kp.jenis_kirim, kv.jenis_kirim) as mut_jenis_kirim,
                    isnull(kp.asal, kv.asal) as mut_asal,
                    isnull(kp.jenis_tujuan, kv.jenis_tujuan) as mut_jenis_tujuan,
                    isnull(kp.tujuan, kv.tujuan) as mut_tujuan,
                    isnull(rp.asal, rv.asal) as ret_asal,
                    isnull(rp.id_asal, rv.id_asal) as ret_id_asal,
                    isnull(rp.tujuan, rv.tujuan) as ret_tujuan,
                    isnull(rp.id_tujuan, rv.id_tujuan) as ret_id_tujuan
                from det_stok_trans_siklus dsts
                left join
                    det_stok_siklus dss
                    on
                        dsts.id_header = dss.id
                left join
                    lhk l
                    on
                        cast(l.id as varchar(20)) = dsts.kode_trans
                left join
                    kirim_pakan kp
                    on
                        dsts.tbl_name = 'terima_pakan' and kp.no_order = dsts.kode_trans
                left join
                    kirim_voadip kv
                    on
                        dsts.tbl_name = 'terima_voadip' and kv.no_order = dsts.kode_trans
                left join
                    retur_pakan rp
                    on
                        dsts.tbl_name = 'retur_pakan' and rp.no_retur = dsts.kode_trans
                left join
                    retur_voadip rv
                    on
                        dsts.tbl_name = 'retur_voadip' and rv.no_retur = dsts.kode_trans
                where
                    dss.noreg = '".$_noreg."' and
                    dsts.kode_barang = '".$_kode_barang."' and
                    dsts.tgl_trans between '".$_start_date."' and '".$_end_date."'
            ) data
            order by
                tanggal asc,
                jenis_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();

            $saldo = 0;
            foreach ($data as $key => $value) {
                $saldo += $value['masuk'] - $value['keluar'];
                $data[$key]['saldo'] = $saldo;

                $data[$key]['asal'] = null;
                $data[$key]['tujuan'] = null;

                if ( $value['jenis_trans'] === 'MUTASI' ) {
                    $at = $this->resolveMutasiAsalTujuan( $value['mut_jenis_kirim'], $value['mut_asal'], $value['mut_jenis_tujuan'], $value['mut_tujuan'] );
                    $data[$key]['asal'] = $at['asal'];
                    $data[$key]['tujuan'] = $at['tujuan'];
                } else if ( $value['jenis_trans'] === 'RETUR' ) {
                    $at = $this->resolveReturAsalTujuan( $value['ret_asal'], $value['ret_id_asal'], $value['ret_tujuan'], $value['ret_id_tujuan'] );
                    $data[$key]['asal'] = $at['asal'];
                    $data[$key]['tujuan'] = $at['tujuan'];
                }
            }

            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * Resolve asal & tujuan mutasi (kirim_pakan/kirim_voadip) jadi nama yang enak dibaca.
     * Sama persis pola resolusinya dgn PengirimanPenerimaanPakan::view() / PengirimanPenerimaanOvk::view():
     * asal tergantung jenis_kirim (opkp=peternak, opkg=gudang, opks=supplier), tujuan tergantung
     * jenis_tujuan (peternak/gudang).
     */
    private function resolveMutasiAsalTujuan( $_jenis_kirim, $_asal_kode, $_jenis_tujuan, $_tujuan_kode )
    {
        $asal = null;
        $tujuan = null;

        $m_rs = new \Model\Storage\RdimSubmit_model();

        if ( $_jenis_kirim == 'opkp' && !empty($_asal_kode) ) {
            $d_rs_asal = $m_rs->where('noreg', $_asal_kode)->with(['mitra'])->orderBy('id', 'desc')->first();
            $asal = ($d_rs_asal && !empty($d_rs_asal->mitra)) ? $d_rs_asal->mitra->dMitra->nama.' ('.$_asal_kode.')' : $_asal_kode;
        } else if ( $_jenis_kirim == 'opkg' && !empty($_asal_kode) ) {
            $m_gudang = new \Model\Storage\Gudang_model();
            $d_gudang = $m_gudang->where('id', $_asal_kode)->orderBy('id', 'desc')->first();
            $asal = $d_gudang ? $d_gudang->nama : $_asal_kode;
        } else if ( $_jenis_kirim == 'opks' && !empty($_asal_kode) ) {
            $m_supplier = new \Model\Storage\Supplier_model();
            $d_supplier = $m_supplier->where('nomor', $_asal_kode)->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
            $asal = $d_supplier ? $d_supplier->nama : $_asal_kode;
        }

        if ( $_jenis_tujuan == 'peternak' && !empty($_tujuan_kode) ) {
            $d_rs_tujuan = $m_rs->where('noreg', $_tujuan_kode)->with(['mitra'])->orderBy('id', 'desc')->first();
            $tujuan = ($d_rs_tujuan && !empty($d_rs_tujuan->mitra)) ? $d_rs_tujuan->mitra->dMitra->nama.' ('.$_tujuan_kode.')' : $_tujuan_kode;
        } else if ( $_jenis_tujuan == 'gudang' && !empty($_tujuan_kode) ) {
            $m_gudang = new \Model\Storage\Gudang_model();
            $d_gudang_tujuan = $m_gudang->where('id', $_tujuan_kode)->orderBy('id', 'desc')->first();
            $tujuan = $d_gudang_tujuan ? $d_gudang_tujuan->nama : $_tujuan_kode;
        }

        return array( 'asal' => $asal, 'tujuan' => $tujuan );
    }

    /**
     * Resolve asal & tujuan retur (retur_pakan/retur_voadip) jadi nama yang enak dibaca.
     * Sama persis pola resolusinya dgn ReturPakan::getAsalTujuan()/ReturVoadip::getAsalTujuan()
     * (union mitra+gudang+pelanggan/supplier); kalau labelnya 'peternak' id-nya noreg tapi yang
     * dipakai buat lookup cuma 7 karakter pertama (nim).
     */
    private function resolveReturAsalTujuan( $_asal_label, $_id_asal, $_tujuan_label, $_id_tujuan )
    {
        $asal = null;
        $tujuan = null;

        if ( !empty($_asal_label) && !empty($_id_asal) ) {
            if ( $_asal_label == 'peternak' ) {
                $d_asal = $this->getAsalTujuanNama( substr($_id_asal, 0, 7) );
                $asal = $d_asal ? $d_asal['nama'].' ('.$_id_asal.')' : $_id_asal;
            } else {
                $d_asal = $this->getAsalTujuanNama( $_id_asal );
                $asal = $d_asal ? $d_asal['nama'] : $_id_asal;
            }
        }

        if ( !empty($_tujuan_label) && !empty($_id_tujuan) ) {
            if ( $_tujuan_label == 'peternak' ) {
                $d_tujuan = $this->getAsalTujuanNama( substr($_id_tujuan, 0, 7) );
                $tujuan = $d_tujuan ? $d_tujuan['nama'].' ('.$_id_tujuan.')' : $_id_tujuan;
            } else {
                $d_tujuan = $this->getAsalTujuanNama( $_id_tujuan );
                $tujuan = $d_tujuan ? $d_tujuan['nama'] : $_id_tujuan;
            }
        }

        return array( 'asal' => $asal, 'tujuan' => $tujuan );
    }

    /**
     * Sama persis dgn ReturPakan::getAsalTujuan() — union mitra (by nim)/gudang/pelanggan-supplier.
     */
    private function getAsalTujuanNama( $_id )
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select supplier.* from (
                select cast(mm.nim as varchar(15)) as id, m.nama as nama from mitra m
                right join
                    (
                        select max(id) as id, nomor from mitra
                        group by
                            nomor
                    ) as group_mitra
                    on
                        m.id = group_mitra.id
                right join
                    mitra_mapping mm
                    on
                        m.nomor = mm.nomor
                group by
                    m.nama, mm.nim

                UNION ALL

                select cast(g.id as varchar(15)) as id, g.nama as nama from gudang g

                UNION ALL

                select cast(p.nomor as varchar(15)) as id, p.nama as nama from pelanggan p
                right join
                    (
                        select max(id) as id, nomor from pelanggan
                        where
                            tipe = 'supplier' and
                            jenis <> 'ekspedisi'
                        group by
                            nomor
                    ) as group_pelanggan
                    on
                        p.id = group_pelanggan.id
            ) as supplier
            where
                supplier.id = '".$_id."'
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        return $data;
    }

    public function getDetailBarang()
    {
        $params = $this->input->post('params');

        try {
            $noreg = trim($params['noreg']);
            $kode_barang = trim($params['kode_barang']);
            $nama_barang = trim($params['nama_barang']);

            $end_date = date('Y-m-d').' 23:59:59.999';

            $data = $this->mappingDetailBarang( $noreg, $kode_barang, $end_date );

            $content['nama_barang'] = $nama_barang;
            $content['data'] = $data;
            $html = $this->load->view('report/performance_peternak/detail_barang', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}
