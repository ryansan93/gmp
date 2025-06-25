<div class="row content-panel detailed">
    <!-- <h4 class="mb">Rencana Chick In Mingguan</h4> -->
    <div class="col-lg-12 detailed">
        <input type="hidden" data-noreg="">

        <form role="form" class="form-horizontal">
            <div class="panel-heading">
                <ul class="nav nav-tabs nav-justified">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#riwayat" data-tab="riwayat">Riwayat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Kredit Kendaraan</a>
                    </li>
                </ul>
            </div>
            <div class="panel-body" style="padding-top: 0px;">
                <div class="tab-content">
                    <div id="riwayat" class="tab-pane fade show active">
                        <?php echo $riwayat; ?>
                    </div>
                    <div id="action" class="tab-pane fade">
                        <?php echo $add_form; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>