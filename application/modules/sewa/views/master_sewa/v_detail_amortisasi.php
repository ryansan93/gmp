<input type="hidden" id="perolehan_val" class="perolehan_val" value="0">

<div style="max-height: 400px; overflow-y: auto; overflow-x: auto;">
    <table class="table table-bordered " style="margin-bottom: 0;">
        <thead>
            <tr class="active">
                <th style="position: sticky; top: 0; background-color: #f5f5f5; z-index: 2; width: 30%;">Kode Amortisasi</th>
                <th style="position: sticky; top: 0; background-color: #f5f5f5; z-index: 2; width: 25%;">Kode Transaksi</th>
                <th style="position: sticky; top: 0; background-color: #f5f5f5; z-index: 2; width: 30%; text-align:right">Nilai (Rp)</th>
                <th style="position: sticky; top: 0; background-color: #f5f5f5; z-index: 2; width: 10%; text-align:center;">Status</th>
                <th style="position: sticky; top: 0; background-color: #f5f5f5; z-index: 2; width: 5%; text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody id="tbody-amortisasi">
            <?php if (!empty($amortisasi)): ?>
                <?php foreach($amortisasi as $index => $a): ?>
                <tr>
                    <!-- Hidden Input -->
                    <input type="hidden" name="amortisasi[<?php echo $index; ?>][id]" value="<?php echo $a['id']; ?>">
                    <input type="hidden" name="amortisasi[<?php echo $index; ?>][kode_amortisasi]" value="<?php echo htmlspecialchars($a['kode_amortisasi']); ?>">
                    <input type="hidden" name="amortisasi[<?php echo $index; ?>][kode_transaksi]" value="<?php echo htmlspecialchars($a['kode_transaksi']); ?>">
                    
                    <!-- TAMBAHAN: Input untuk menandai baris yang akan dihapus -->
                    <input type="hidden" name="amortisasi[<?php echo $index; ?>][is_deleted]" value="0" class="is-deleted-input">
                    
                    <td><?php echo htmlspecialchars($a['kode_amortisasi']); ?></td>
                    <td><?php echo htmlspecialchars($a['kode_transaksi']); ?></td>
                    
                    <!-- KOLOM NILAI: Pemicu 1 (onkeyup) -->
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input onkeyup="ms.formatDanCek(this)" 
                                <?php echo $a['status'] == 1 ? 'readonly' : '' ?> 
                                type="text" 
                                name="amortisasi[<?php echo $index; ?>][nilai]" 
                                class="form-control format-rupiah text-right"  
                                value="<?php echo number_format($a['nilai'], 0, ',', '.'); ?>"  
                                placeholder="0" 
                                autocomplete="off">
                        </div>
                    </td>

                    <td style="vertical-align: middle; text-align: center;">
                        <!-- Status badge (tetap sama) -->
                        <?php if(isset($a['status'])): ?>
                            <?php if($a['status'] == 0): ?>
                                <span style="display:inline-block; padding:4px 12px; border-radius:20px; background-color:#fff3cd; color:#856404; font-size:12px; font-weight:600;">Pending</span>
                            <?php else: ?>
                                <span style="display:inline-block; padding:4px 12px; border-radius:20px; background-color:#d4edda; color:#155724; font-size:12px; font-weight:600;">Done</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#999;">-</span>
                        <?php endif; ?>
                    </td>

                    <td style="vertical-align: middle; text-align: center;">
                        <?php if(isset($a['status'])): ?>
                            <?php if($a['status'] == 0): ?>
                                <button type="button" class="btn btn-danger btn-sm btn-delete-amort"  onclick="ms.hapusDanCek(this)"  data-index="<?php echo $index; ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">Tidak ada data amortisasi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<br>
<table class="table table-bordered">
    <tr>
        <th>Total Amortisasi</th>
        <th class="text-right">
            <span id="label_total_amortisasi">Rp 0</span>
        </th>
    </tr>
</table>