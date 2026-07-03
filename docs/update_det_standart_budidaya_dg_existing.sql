/*
 * Mengisi kolom `dg` (Daily Gain) untuk data existing di det_standart_budidaya.
 * DG = BB[n] - BB[n-1] (urut berdasarkan umur per id_budidaya).
 * Untuk baris pertama (umur terendah), DG = BB (asumsi starting BB = 0).
 *
 * Jalankan SETELAH kolom `dg` ditambahkan.
 * Idempotent — aman dijalankan berulang.
 */
WITH cte AS (
    SELECT
        id,
        bb,
        LAG(bb) OVER (PARTITION BY id_budidaya ORDER BY umur) AS prev_bb
    FROM det_standart_budidaya
)
UPDATE d
SET dg = ISNULL(c.bb - c.prev_bb, c.bb)
FROM det_standart_budidaya d
INNER JOIN cte c ON c.id = d.id
WHERE d.dg IS NULL;
GO
