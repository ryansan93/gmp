/* ============================================================================
   FIX DATA: singkatan BU09 (Perseroan Terbatas Terbuka) dobel "Tbk"
   ============================================================================
   Konteks: kode tampilan/compose Nama Perusahaan menambahkan sendiri suffix
   ", Tbk" berdasarkan flag is_terbuka=1, jadi kolom singkatan seharusnya cukup
   "PT" saja (bukan "PT Tbk") supaya tidak dobel jadi "PT Tbk Tbk" / "PT Tbk. ..., Tbk".

   Aman dijalankan kapan saja, idempotent.
   ============================================================================ */

IF EXISTS (SELECT 1 FROM master_badan_usaha WHERE id_badan_usaha = 'BU09' AND singkatan = 'PT Tbk')
    UPDATE master_badan_usaha SET singkatan = 'PT' WHERE id_badan_usaha = 'BU09';

-- Verifikasi
SELECT * FROM master_badan_usaha WHERE id_badan_usaha = 'BU09';
