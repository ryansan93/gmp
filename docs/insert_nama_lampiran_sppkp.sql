/* ============================================================================
   SEED DATA: jenis lampiran baru "SPPKP" (Surat Pengukuhan Pengusaha Kena
   Pajak) untuk 4 modul: Supplier, Pelanggan, Ekspedisi, Peternak (MITRA).
   ============================================================================
   Konteks: aturan pajak baru -- kalau Badan Usaha = PT/CV/Koperasi/Firma/
   Yayasan, wajib upload SPPKP. Kalau Perorangan/UD, tidak wajib (NPWP-nya pun
   opsional, cukup NIK).

   Kolom `required` di sini dibiarkan 0 (default) karena wajib/tidaknya
   bersifat KONDISIONAL tergantung Badan Usaha yang dipilih -- logikanya
   diatur di sisi JS (lihat *.js per modul), bukan statis dari tabel ini.
   Kolom ini cuma relevan langsung utk Peternak (loop dinamis di
   edit_form.php), untuk Supplier/Pelanggan/Ekspedisi murni referensi id.

   Aman dijalankan kapan saja, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM nama_lampiran WHERE jenis = 'SUPPLIER' AND nama = 'SPPKP Supplier')
    INSERT INTO nama_lampiran (sequence, jenis, nama, required) VALUES (3, 'SUPPLIER', 'SPPKP Supplier', 0);

IF NOT EXISTS (SELECT 1 FROM nama_lampiran WHERE jenis = 'PELANGGAN' AND nama = 'SPPKP Pelanggan')
    INSERT INTO nama_lampiran (sequence, jenis, nama, required) VALUES (3, 'PELANGGAN', 'SPPKP Pelanggan', 0);

IF NOT EXISTS (SELECT 1 FROM nama_lampiran WHERE jenis = 'EKSPEDISI' AND nama = 'SPPKP Ekspedisi')
    INSERT INTO nama_lampiran (sequence, jenis, nama, required) VALUES (3, 'EKSPEDISI', 'SPPKP Ekspedisi', 0);

IF NOT EXISTS (SELECT 1 FROM nama_lampiran WHERE jenis = 'MITRA' AND nama = 'FC SPPKP')
    INSERT INTO nama_lampiran (sequence, jenis, nama, required) VALUES (12, 'MITRA', 'FC SPPKP', 0);

-- Verifikasi
SELECT id, sequence, jenis, nama, required
FROM nama_lampiran
WHERE nama LIKE '%SPPKP%'
ORDER BY jenis;
