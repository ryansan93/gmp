<!DOCTYPE html>
<html lang="en">
<head>
    <!-- <title><?= $title; ?>/</title>
    <meta charset="utf-8"></meta>
    <meta name="viewport" content="width=device-width, initial-scale=1"></meta> -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <h3 style="text-align: center;">BERITA ACARA<br>SERAH TERIMA KENDARAAN</h3>
        <p></p>
        <p>Pada hari ini <?php echo $tgl_serah_terima; ?>, kami yang bertanda tangan di bawah ini :</p>
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 5%;">1. </td>
                    <td style="width: 10%;">Nama</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 82%;"><b><?php echo !empty( $data['kode_karyawan_lama'] ) ? strtoupper($data['nama_karyawan_lama']) : strtoupper($penanggung_jawab['nama']); ?></b></td>
                </tr>
                <tr>
                    <td></td>
                    <td>NIP</td>
                    <td>:</td>
                    <td><?php echo !empty( $data['kode_karyawan_lama'] ) ? strtoupper($data['kode_karyawan_lama']) : strtoupper($penanggung_jawab['nik']); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td><?php echo !empty( $data['kode_karyawan_lama'] ) ? strtoupper($data['kode_karyawan_lama']) : strtoupper($penanggung_jawab['jabatan']); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Unit</td>
                    <td>:</td>
                    <td><?php echo !empty( $data['kode_karyawan_lama'] ) ? strtoupper($data['nama_unit_lama']) : 'JEMBER'; ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Disebut</td>
                    <td>:</td>
                    <td>PIHAK PERTAMA</td>
                </tr>
                <tr>
                    <td colspan="4"><br></td>
                </tr>
                <tr>
                    <td>2. </td>
                    <td>Nama</td>
                    <td>:</td>
                    <td><b><?php echo strtoupper($data['nama_karyawan_baru']); ?></b></td>
                </tr>
                <tr>
                    <td></td>
                    <td>NIP</td>
                    <td>:</td>
                    <td><?php echo strtoupper($data['kode_karyawan_baru']); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td><?php echo strtoupper($data['jabatan_karyawan_baru']); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Unit</td>
                    <td>:</td>
                    <td><?php echo strtoupper($data['nama_unit_baru']); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Disebut</td>
                    <td>:</td>
                    <td>PIHAK KEDUA</td>
                </tr>
            </tbody>
        </table>
        <p></p>
        <p>Melakukan serah terima kendaraan dinas jenis <b><?php echo strtoupper($data['jenis']); ?></b> dengan spesifikasi sebagai berikut :</p>
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 5%;">A. </td>
                    <td style="width: 10%;">Merk</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 82%;"><?php echo strtoupper($data['merk']); ?></td>
                </tr>
                <tr>
                    <td>B. </td>
                    <td>Tipe</td>
                    <td>:</td>
                    <td><?php echo strtoupper($data['tipe']); ?></td>
                </tr>
                <tr>
                    <td>C. </td>
                    <td>No. Polisi</td>
                    <td>:</td>
                    <td><?php echo strtoupper($data['nopol']); ?></td>
                </tr>
                <tr>
                    <td>D. </td>
                    <td>Warna</td>
                    <td>:</td>
                    <td><?php echo strtoupper($data['warna']); ?></td>
                </tr>
                <tr>
                    <td>E. </td>
                    <td>Tahun</td>
                    <td>:</td>
                    <td><?php echo strtoupper($data['tahun']); ?></td>
                </tr>
            </tbody>
        </table>
        <p></p>
        <p>Demikian Berita Acara Serah Terima Kendaraan ini dibuat untuk dapat dipergunakan sebagaimana merstinya.</p>
        <p><hr></p>
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 33.3%; text-align: center;"></td>
                    <td style="width: 33.3%; text-align: center;"></td>
                    <td style="width: 33.3%; text-align: center;">Jember, <?php echo strtoupper( $tanggal ); ?></td>
                </tr>
                <tr>
                    <td style="width: 33.3%; text-align: center;">PIHAK PERTAMA</td>
                    <td style="width: 33.3%; text-align: center;">PIHAK KEDUA</td>
                    <td style="width: 33.3%; text-align: center;">MENGETAHUI</td>
                </tr>
                <tr>
                    <td style="width: 33.3%; text-align: center;"><br><br><br><br><br></td>
                    <td style="width: 33.3%; text-align: center;"><br><br><br><br><br></td>
                    <td style="width: 33.3%; text-align: center;"><br><br><br><br><br></td>
                </tr>
                <tr>
                    <td style="width: 33.3%;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 5%; text-align: left;">(</td>
                                <td style="width: 90%; text-align: center;"><b><?php echo strtoupper($penanggung_jawab['nama']); ?></b></td>
                                <td style="width: 5%; text-align: right;">)</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 33.3%;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 5%; text-align: left;">(</td>
                                <td style="width: 90%; text-align: center;"><b><?php echo strtoupper($data['nama_karyawan_baru']); ?></b></td>
                                <td style="width: 5%; text-align: right;">)</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 33.3%;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 5%; text-align: left;">(</td>
                                <td style="width: 90%; text-align: center;"><b><?php echo strtoupper($penanggung_jawab['nama']); ?></b></td>
                                <td style="width: 5%; text-align: right;">)</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>