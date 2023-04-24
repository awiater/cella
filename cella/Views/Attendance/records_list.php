<div class="card border child-full">
    <div class="card-header">
        <div class="d-flex">
            <h4><?= lang('attendance.records_list_title') ?></h4>	
        </div>
    </div>
    <div class="card-body">
        <div id="id_attendance_list_filters" class="d-flex">
            <div class="form-group">
                <div class="input-group">
                    <input type="hidden"  class1="text-white border-0" style="width:0px;" id="id_attendance_list_filters_date_val" value="<?= $start ?>" readonly="true">
                    <input type="hidden"  id="id_attendance_list_filters_date_val_read" value="<?= convertDate($start,'Ymd','d M Y'); ?>">
                    <div class="form-control form-control-sm" id="id_attendance_list_filters_date" style="min-width: 120px">
                        <?= convertDate($start,'Ymd','d M Y'); ?>
                    </div>
                    <div class="input-group-append">
                        <span class="input-group-text btn" id="id_attendance_list_filters_date_btn">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-sm btn-secondary ml-2" id="id_attendance_list_filters_btn">
                    <i class="fas fa-filter"></i>
                </button>
                
                <button type="button" class="btn btn-sm btn-secondary ml-2" onclick="$('.buttons-print').trigger('click')">
                    <i class="fas fa-print mr-1"></i><?= ucwords(lang('system.buttons.printbtn')) ?>
                </button>
                
                <button type="button" class="btn btn-sm btn-warning" onclick="$('.buttons-pdf').trigger('click')">
                    <i class="far fa-file-pdf mr-1"></i><?= ucwords(lang('system.buttons.exportpdfbtn')) ?>
                </button>
                
                <button type="button" class="btn btn-sm btn-primary" onclick="$('.buttons-excel').trigger('click')">
                   <i class="far fa-file-excel mr-1"></i><?= lang('attendance.btn_export_excel') ?>
                </button>
                
                <a href="<?= $url_fetch ?>" class="btn btn-sm btn-danger ml-3" >
                   <i class="fas fa-user-clock mr-1"></i><?= lang('attendance.btn_fetch_data') ?>
                </a>
            </div>
        </div>
        <table class="table" id="id_attendance_list">
            <thead>
                <tr>
                    <th scope="col"><?= lang('attendance.att_name') ?></th>
                    <?php for($i=0;$i<=4;$i++) :?>
                    <th scope="col">
                        <?= convertDate(formatDate($start,'+ '.$i.' days','Ymd'),'Ymd','D, d M Y') ?>
                    </th>
                    <?php endfor;?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user) :?>
                <tr>
                    <td><?= $user ?></td>
                    <?php for($i=0;$i<=4;$i++) :?>
                        <?php $cdate=formatDate($start,'+ '.$i.' days','Ymd');if (array_key_exists($user, $records) && array_key_exists($cdate, $records[$user])) :?>
                        <td>
                            <?= $records[$user][$cdate]['ati_start'] ?>&nbsp;-&nbsp;<?= $records[$user][$cdate]['ati_end'] ?>
                            <?php if (intval($records[$user][$cdate]['att_earlyfinish']) > 0 || intval($records[$user][$cdate]['att_latestart']) > 0) :?>
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <?php endif ?>
                        </td>
                        <?php else :?>
                        <td></td>
                        <?php endif ?>
                        
                    <?php endfor;?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
$(function(){
    $('#id_attendance_list_filters_date_val').datepicker({ dateFormat: "yymmdd" });
    $('#id_attendance_list_filters_date_val_read').datepicker({ dateFormat: "d M yy" });
    $('#id_attendance_list').DataTable({
        'searching':false,
        'ordering':false,
        'paging':false,
        dom:'Bfrtip',
    });
    $('.dt-buttons').addClass('d-none');
});

$('#id_attendance_list_filters_date_val').on('change',function(){
    $('#id_attendance_list_filters_date_val_read').datepicker("setDate",$(this).datepicker("getDate"));
    $('#id_attendance_list_filters_date').text($('#id_attendance_list_filters_date_val_read').val());
    $('#id_attendance_list_filters_date_btn').removeClass('d-none');
});

$('#id_attendance_list_filters_date_btn').on('click',function(){
        $('#id_attendance_list_filters_date_val').datepicker('show');
        $(this).addClass('d-none');
});

$('#id_attendance_list_filters_btn').on('click',function(){
    var url='<?= $url?>';
    url=url.replace('-date-',$('#id_attendance_list_filters_date_val').val());
    window.location=url;
});
</script>

