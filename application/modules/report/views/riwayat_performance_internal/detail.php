<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail LHK</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: #f4f6f9; color: #1c2733; font-size: 13px; }
        .topbar {
            background: #ff9d00; color: #3a2a00; padding: 12px 16px; font-size: 16px; font-weight: 700;
            position: sticky; top: 0; z-index: 3; display: flex; align-items: center; gap: 10px;
        }
        .topbar a.back { color: #3a2a00; text-decoration: none; font-size: 24px; line-height: 1; }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back" href="<?php echo base_url('report/RiwayatPerformanceInternal') . (isset($tgl) ? '?tgl=' . urlencode($tgl) : ''); ?>">&#8249;</a>
        <span>Detail LHK</span>
    </div>

    <?php echo $body; ?>
</body>
</html>
