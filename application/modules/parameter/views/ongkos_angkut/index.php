<div class="row content-panel detailed">
    <!-- <h4 class="mb">Master Ongkos Angkut</h4> -->
    <div class="col-lg-12 detailed">
        <form role="form" class="form-horizontal">
            <div class="panel-heading">
                <ul class="nav nav-tabs nav-justified">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#history" data-tab="history">Riwayat Tarif OA</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#action" data-tab="action">Tarif OA Pakan & DOC</a>
                    </li>
                </ul>
            </div>
            <div class="panel-body">
                <div class="tab-content">
                    <div id="history" class="tab-pane fade show active" role="tabpanel">
                        <div class="col-lg-8 search left-inner-addon no-padding">
                            <i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_oa" placeholder="Search" onkeyup="filter_all(this)">
                        </div>
                        <div class="col-lg-4 action no-padding">
                            <?php if ( $akses['a_submit'] == 1 ) { ?>
                                <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="oa.changeTabActive(this)"> 
                                    <i class="fa fa-plus" aria-hidden="true"></i> ADD
                                </button>
                            <?php // } else if ( $akses['a_ack'] == 1 ) { ?>
                                <!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="doc.ack(this)"> 
                                    <i class="fa fa-check" aria-hidden="true"></i> ACK
                                </button> -->
                            <?php // } else if ( $akses['a_approve'] == 1 ) { ?>
                                <!-- <button id="btn-add" type="button" class="btn btn-primary cursor-p pull-right" title="APPROVE" onclick="doc.approve(this)"> 
                                    <i class="fa fa-check" aria-hidden="true"></i> APPROVE
                                </button> -->
                            <?php } else { ?>
                                <div class="col-lg-2 action no-padding pull-right">
                                    &nbsp
                                </div>
                            <?php } ?>
                        </div>
                        <table id="tb_lists_oa" class="table table-hover table-bordered tbl_oa">
                            <thead>
                                <tr>
                                    <th class="col-sm-2">Tanggal Berlaku</th>
                                    <th class="col-sm-2">Nomor Dokumen</th>
                                    <th class="col-sm-2">Jenis OA</span></th>
                                    <th class="">Status</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="action" class="tab-pane fade" role="tabpanel">
                        <?php if ( $akses['a_submit'] == 1 ): ?>
                            <div class="row new-line">
                                <div class="col-sm-6 pull-left">
                                    <div class="col-md-2 text-left no-padding">
                                        <h5>Tanggal Berlaku</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group date" id="datetimepicker1" name="tanggal-berlaku">
                                            <input type="text" class="form-control text-center" data-required="1" />
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="col-md-3 pull-right no-padding">
                                        <select class="form-control jns_oa" data-required="1" onchange="oa.loadHeader(this)">
                                            <option value="pakan">OA Pakan</option>
                                            <option value="doc">OA DOC</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row new-line">
                            </div>
                            <div class="row new-line attachement">
                                <div class="col-sm-12">
                                    <div class="col-md-6 no-padding" style="margin-top: 10px; margin-bottom: 10px;">
                                        <div class="col-sm-2 no-padding">Lampiran OA</div>
                                        <label class="col-sm-1 text-left" data-idnama="">
                                            <input required="required" type="file" onchange="showNameFile(this)" class="file_lampiran oa" data-required="1" name="lampiran_dds" data-allowtypes="doc|pdf|docx|jpg|jpeg|png" style="display: none;" placeholder="Lampiran OA">
                                            <i class="glyphicon glyphicon-paperclip cursor-p" title="Lampiran OA"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row new-line loading">
                                <div class="cssload-container">
                                    <div class="cssload-speeding-wheel"></div>
                                </div>
                            </div>
                            <div class="row new-line data_oa">
                                <div class="col-sm-12">
                                    <table class="table table-bordered custom_table oa">
                                        <thead>
                                            <tr>
                                                <th class="text-center" rowspan="3">Kabupaten / Kota</th>
                                                <th class="text-center" rowspan="3">Kecamatan</th>
                                                <th class="head text-center" colspan="4">Tarif / Kg</th>
                                                <th class="text-center" rowspan="3"></th>
                                            </tr>
                                            <tr>
                                                <th class="text-center" colspan="2">Lama</th>
                                                <th class="text-center" colspan="2">Baru</th>
                                            </tr>
                                            <tr>
                                                <th class="head1 text-center">Kediri</th>
                                                <th class="head2 text-center">Pasuruan</th>
                                                <th class="head1 text-center">Kediri</th>
                                                <th class="head2 text-center">Pasuruan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="data">
                                                <td class="col-sm-2 kab">
                                                    <select class="form-control chosen-kab" onchange="oa.loadDataKec(this)">
                                                        <?php foreach ($lokasi_kb_kt as $key => $val): ?>
                                                            <option value="<?php echo $val['id']; ?>"><?php echo strtoupper($val['nama']); ?></option>
                                                        <?php endforeach ?>
                                                    </select>
                                                </td>
                                                <td class="col-sm-2 kec">
                                                    <select class="form-control chosen-kec" data-required="1" onchange="oa.loadContentKec(this)">
                                                        <option value="-1">&nbsp</option>
                                                        <option value="0">ALL</option>
                                                    </select>
                                                    <select class="hide kec">
                                                    </select>
                                                </td>
                                                <td class="col-sm-1">
                                                    <input type="text" class="form-control text-right lama1" data-tipe="decimal" maxlength="6" />
                                                </td>
                                                <td class="col-sm-1">
                                                    <input type="text" class="form-control text-right lama2" data-tipe="decimal" maxlength="6" />
                                                </td>
                                                <td class="col-sm-1">
                                                    <input type="text" class="form-control text-right baru1" data-tipe="decimal" maxlength="6" data-required="1" />
                                                </td>
                                                <td class="col-sm-1">
                                                    <input type="text" class="form-control text-right baru2" data-tipe="decimal" maxlength="6" data-required="1" />
                                                </td>
                                                <td class="action text-center col-sm-1">
                                                    <button type="button" class="btn btn-sm btn-danger remove hide" onclick="oa.removeRowTable(this)"><i class="fa fa-minus"></i></button>
                                                    <button type="button" class="btn btn-sm btn-default add" onclick="oa.addRowTable(this)"><i class="fa fa-plus"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-12">
                                    <button type="button" class="btn btn-primary save" href='#oa' onclick="oa.save(this)"><i class="fa fa-save"></i> Simpan</button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $akses['a_ack'] == 1 ): ?>
                            <h5>Detail data Tarif OA Pakan & DOC</h5>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- <div id="for_more_change_tab_activity" class="hidden"></div>

<div class="panel panel-default" id="index">
   <div class="panel-heading"><?php echo $title_panel ?></div>
   <div class="panel-body">

    <div id="tab-oa">
    	<ul class="nav nav-tabs" role="tablist">
    		<li class="active">
    			<a href='#riwayat' role="tab" data-toggle="tab" id="for_riwayat">Riwayat Tarif OA<span class="help"></span></a>
    		</li>
  			<li class="">
    			<a href="#oa" role="tab" data-toggle="tab" id="for_oa">Tarif OA Pakan & DOC<span class="help"></span></a>
    		</li>
    	</ul>
    </div>
    <div class="tab-content new-line">
	     <div id="riwayat" class="tab-pane active">
         <div class="row new-line">
           <div class="col-sm-12">
             <div class="col-sm-11 no-padding">
               <div class="input-group">
                 <input type="text" value="" placeholder="search" class="form-control search">
                 <span class="input-group-addon">
                   <i class="fa fa-search"></i>
                 </span>
               </div>
             </div>
              <div class="col-sm-1 text-right no-padding">
                <?php if ( $akses['a_submit'] == 1 ): ?>
                  <button type="button" class="btn btn-default" data-add="1" onclick="oa.changeTabActive(this)" href='#oa' data-toggle="tooltip" title="Tarif OA Pakan & DOC Baru"><i class="fa fa-plus"></i></button>
                <?php endif; ?>
              </div>
           </div>
         </div>

         <div class="row new-line">
           <div class="col-sm-12">
             <small>
               <table id="tb_lists_oa" class="table table-hover table-bordered">
                 <thead>
                   <tr>
                     <th class="col-sm-2"><span class="sort" data-sort="tanggal" ><a href="#" onclick="return false;">Tanggal Berlaku <i class="fa fa-sort"></i></a></span></th>
                     <th class="col-sm-2"><span class="sort" data-sort="nomor" ><a href="#" onclick="return false;">Nomor Dokumen<i class="fa fa-sort"></i></a></span></th>
                     <th class="col-sm-2"><span class="sort" data-sort="jenis" ><a href="#" onclick="return false;">Jenis OA <i class="fa fa-sort"></i></a></span></th>
                     <th class="">Status</th>
                   </tr>
                 </thead>
                 <tbody class="list">
                   <tr>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                   </tr>
                 </tbody>
               </table>
             </small>
           </div>
         </div>
       </div>

	     <div id="oa" class="tab-pane">
         <?php if ($akses['submit']): ?>
           <?php echo $content_input_oa ?>
         <?php endif; ?>
         <?php if ($akses['approve']): ?>
           <h5>Detail data Tarif OA Pakan & DOC</h5>
         <?php endif; ?>
       </div>
    </div>
   </div>
</div>
 -->