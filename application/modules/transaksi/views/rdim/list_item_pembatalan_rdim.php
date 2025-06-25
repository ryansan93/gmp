<?php if ($lists): ?>
  <?php foreach ($lists as $rs): ?>
    <tr class="v-center" data-id="<?php echo $rs['id'] ?>" isEdit="0">
      <td>
        <button type="button" class="btn btn-xs btn-default" onclick="rdim.editRowPembatalanRdim(this)"><i class="fa fa-edit"></i></button>
        <u>
          <?php echo $rs['mitra'] ?>
        </u>
      </td>
      <td><?php echo $rs['noreg'] ?></td>
      <td>
        <small>
          <label class="control-label">
            <input type="file" style="display: none;" data-allowtypes="doc|pdf|docx" placeholder="lampiran kandang - Survey Flok/Kandang (informasi kandang)" name="lampiran" data-required="1" class="file_lampiran" onchange="showNameFile(this)" disabled>
            <i class="pull-right glyphicon glyphicon-paperclip cursor-p"></i>
          </label>
        </small>
      </td>
      <td>
        <textarea class="form-control" name="ket_alasan" rows="1" disabled data-required="1"></textarea>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td colspan="4">data rdim tidak ditemukan</td>
  </tr>
<?php endif; ?>
