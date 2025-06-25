<?php if ( !empty($data) ) { ?>
    <?php foreach ($data as $key => $value) { ?>
        <tr>
            <td><?php echo strtoupper($value['nama_perusahaan']); ?></td>
            <td><?php echo strtoupper(tglIndonesia($value['start_date'], '-', ' ').' - '.tglIndonesia($value['end_date'], '-', ' ')); ?></td>
            <td><?php echo strtoupper($value['kode_unit']); ?></td>
            <td class="text-right"><?php echo angkaRibuan($value['jumlah']); ?></td>
            <td>
                <div class="col-xs-6 no-padding" style="padding-right: 5px;">
                    <button type="button" class="btn btn-success col-xs-12" onclick="est.editForm(this)" data-id="<?php echo $value['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
                </div>
                <div class="col-xs-6 no-padding" style="padding-left: 5px;">
                <button type="button" class="btn btn-danger col-xs-12" onclick="est.delete(this)" data-id="<?php echo $value['id']; ?>"><i class="fa fa-trash"></i> Delete</button>
                </div>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="5">Data tidak ditemukan.</td>
    </tr>
<?php } ?>