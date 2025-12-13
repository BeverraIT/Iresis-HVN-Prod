<?php
$user = $this->session->userdata('user');
if (!$user || !isset($user['id_user'])) {
    redirect('welcome/restricted');
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Kelola Target KPI Harian</strong></h3>
            </div>
            <div class="panel-body">
                <!-- Filter -->
                <form class="form-horizontal" method="get" action="<?= base_url('kpi/target-kpi') ?>" id="form-filter-target">
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Rentang waktu</label>
                        <div class="col-md-3 col-xs-12">
                            <input type="text" name="reportrange" id="reportrange" class="form-control" value="<?= $reportrange ?>" />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Role</label>
                        <div class="col-md-3 col-xs-12">
                            <select name="role" id="role" class="form-control">
                                <option value="PICKER" <?= $role == 'PICKER' ? 'selected' : '' ?>>PICKER</option>
                                <option value="PACKER" <?= $role == 'PACKER' ? 'selected' : '' ?>>PACKER</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Target</label>
                        <div class="col-md-3 col-xs-12">
                            <select name="target_resi" id="target_resi" class="form-control">
                                <option value="">Pilih Target</option>
                                <?php for ($i = 300; $i <= 1500; $i += 100): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label"></label>
                        <div class="col-md-6 col-xs-12">
                            <button type="submit" class="btn btn-info" id="btn-search">
                                <i class="fa fa-search"></i> Cari
                            </button>
                            <button type="button" class="btn btn-success" id="btn-set-target">
                                <i class="fa fa-plus"></i> Set Target
                            </button>
                            <button type="button" class="btn btn-warning" id="btn-copy-target">
                                <i class="fa fa-copy"></i> Copy Kemarin
                            </button>
                        </div>
                    </div>
                </form>
                <hr>
                
                <!-- Summary -->
                <?php if ($summary && isset($summary->total_users) && $summary->total_users > 0): ?>
                <div class="alert alert-info">
                    <strong>Summary:</strong> 
                    <?php if (isset($summary->total_days) && $summary->total_days > 1): ?>
                        <?= $summary->total_days ?> hari | 
                        <?= $summary->total_records ?> records | 
                    <?php endif; ?>
                    <?= $summary->total_users ?> users | 
                    Total Target: <?= number_format($summary->total_target) ?> resi | 
                    Rata-rata: <?= number_format($summary->avg_target) ?> resi | 
                    Min: <?= number_format($summary->min_target) ?> | 
                    Max: <?= number_format($summary->max_target) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Table Target -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-table"></i> <strong>Target <?= $role ?> - 
                    <?php if ($is_range ?? false): ?>
                        <?php
                        $dates = explode(' - ', $reportrange);
                        echo date('d M Y', strtotime($dates[0])) . ' s/d ' . date('d M Y', strtotime($dates[1]));
                        ?>
                    <?php else: ?>
                        <?= date('d M Y', strtotime($tanggal)) ?>
                    <?php endif; ?>
                    </strong>
                </h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead style="background-color: #337ab7; color: white;">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Tanggal</th>
                                <th>Username</th>
                                <th>Nama Pegawai</th>
                                <th class="text-center">Target Resi</th>
                                <th>Keterangan</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($targets) > 0): ?>
                                <?php foreach ($targets as $index => $target): ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td><?= date('d M Y', strtotime($target['tanggal'])) ?></td>
                                        <td><strong><?= htmlspecialchars($target['username'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($target['nama_pegawai'] ?? $target['name'] ?? 'N/A') ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-primary" style="font-size: 14px; padding: 5px 10px;">
                                                <?= number_format($target['target_resi']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($target['keterangan'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning btn-edit" 
                                                    data-id="<?= $target['id_target'] ?>"
                                                    data-user-id="<?= $target['id_user'] ?>"
                                                    data-username="<?= htmlspecialchars($target['username']) ?>"
                                                    data-nama="<?= htmlspecialchars($target['nama_pegawai'] ?? $target['name']) ?>"
                                                    data-target="<?= $target['target_resi'] ?>"
                                                    data-keterangan="<?= htmlspecialchars($target['keterangan'] ?? '') ?>">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-delete" 
                                                    data-id="<?= $target['id_target'] ?>"
                                                    data-username="<?= htmlspecialchars($target['username']) ?>">
                                                <i class="fa fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fa fa-info-circle"></i> Belum ada target untuk tanggal ini. 
                                        Klik <strong>"Set Target"</strong> untuk menambahkan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Set Target -->
<div class="modal fade" id="modal-set-target" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #337ab7; color: white;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus-circle"></i> <strong>Set Target Harian</strong></h4>
            </div>
            <form id="form-set-target">
                <div class="modal-body">
                    <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                    <input type="hidden" name="role" value="<?= $role ?>">
                    
                    <div class="form-group">
                        <label>Tanggal: <strong><?= date('d F Y', strtotime($tanggal)) ?></strong></label> | 
                        <label>Role: <strong><?= $role ?></strong></label>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Misal: TIM INTI - Target Normal">
                    </div>
                    
                    <hr>
                    
                    <div class="form-group">
                        <label><strong>Pilih User dan Set Target:</strong></label>
                        
                        <!-- Search Box -->
                        <div class="input-group" style="margin-bottom: 10px;">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="text" id="user-search" class="form-control" placeholder="Cari username atau nama pegawai...">
                        </div>
                        
                        <div id="user-list" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                            <?php if (count($available_users) > 0): ?>
                                <?php foreach ($available_users as $user): ?>
                                    <div class="checkbox user-item" data-search-text="<?= strtolower($user['username'] . ' ' . $user['nama_pegawai']) ?>">
                                        <label>
                                            <input type="checkbox" name="users[]" value="<?= $user['id_user'] ?>" class="user-checkbox">
                                            <strong><?= htmlspecialchars($user['username']) ?></strong> - <?= htmlspecialchars($user['nama_pegawai']) ?>
                                            <?php if (isset($user['user_role'])): ?>
                                                <span class="label label-<?= $user['user_role'] == 'PICKER' ? 'primary' : 'warning' ?>" style="margin-left: 5px;">
                                                    <?= $user['user_role'] ?>
                                                </span>
                                            <?php endif; ?>
                                            <input type="number" name="targets[]" class="form-control input-sm" 
                                                   style="display: inline-block; width: 100px; margin-left: 10px;" 
                                                   placeholder="Target" min="1" disabled>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Tidak ada user tersedia</p>
                            <?php endif; ?>
                        </div>
                        
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> 
                            Menampilkan <strong id="user-count"><?= count($available_users) ?></strong> user. 
                            Target yang Anda set di sini akan menggunakan <strong>Role: <?= $role ?></strong> yang dipilih di filter atas.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Simpan Target
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Target -->
<div class="modal fade" id="modal-edit-target" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f0ad4e; color: white;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-edit"></i> <strong>Edit Target</strong></h4>
            </div>
            <form id="form-edit-target">
                <div class="modal-body">
                    <input type="hidden" name="id_user" id="edit-id-user">
                    <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                    <input type="hidden" name="role" value="<?= $role ?>">
                    
                    <div class="form-group">
                        <label>User</label>
                        <p><strong id="edit-username"></strong> - <span id="edit-nama"></span></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Target Resi</label>
                        <input type="number" name="target_resi" id="edit-target" class="form-control" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" id="edit-keterangan" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Copy Target -->
<div class="modal fade" id="modal-copy-target" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f0ad4e; color: white;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-copy"></i> <strong>Copy Target</strong></h4>
            </div>
            <form id="form-copy-target">
                <div class="modal-body">
                    <input type="hidden" name="to_date" value="<?= $tanggal ?>">
                    <input type="hidden" name="role" value="<?= $role ?>">
                    
                    <p>Copy target dari tanggal lain ke <strong><?= date('d F Y', strtotime($tanggal)) ?></strong></p>
                    
                    <div class="form-group">
                        <label>Copy dari Tanggal</label>
                        <input type="date" name="from_date" class="form-control" 
                               value="<?= date('Y-m-d', strtotime($tanggal . ' -1 day')) ?>" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        Target yang sudah ada akan di-update, yang belum ada akan dibuat baru.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-copy"></i> Copy Target
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global state to store current reportrange
window.targetKpiReportRange = <?= !empty($reportrange) ? '"' . $reportrange . '"' : '"' . date('Y-m-d 00:00:00') . ' - ' . date('Y-m-d 23:59:59') . '"' ?>;
window.targetKpiCurrentRole = '<?= $role ?>';

// Function to initialize daterangepicker - SAMA PERSIS seperti Laporan Resi Harian
function initTargetKpiDatepicker() {
    var report_range = window.targetKpiReportRange;
    
    var start = report_range !== null ? moment(report_range.split(" - ")[0]) : moment().startOf('day');
    var end = report_range !== null ? moment(report_range.split(" - ")[1]) : moment();
    
    var $reportrange = $('#reportrange');
    
    if ($reportrange.length === 0) {
        console.warn('Reportrange element not found');
        return;
    }
    
    // Destroy existing datepicker if exists
    if ($reportrange.data('daterangepicker')) {
        try {
            $reportrange.data('daterangepicker').remove();
        } catch(e) {
            console.warn('Error removing old daterangepicker:', e);
        }
    }
    
    // SAMA PERSIS dengan Laporan Resi Harian dan Dashboard KPI
    $reportrange.daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment().startOf('day'), moment()],
            'Last 1 Hours': [moment().subtract(1, 'hours'), moment()],
            'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
            'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
            'This Month': [moment().startOf('month'), moment()],
        },
        locale: {
            format: 'YYYY-MM-DD HH:mm:ss'
        }
    }, function(start, end, label) {
        // Callback when range is applied - UPDATE value and global variable
        var newRange = start.format('YYYY-MM-DD HH:mm:ss') + ' - ' + end.format('YYYY-MM-DD HH:mm:ss');
        $reportrange.val(newRange);
        window.targetKpiReportRange = newRange;
        
        console.log('✅ Date range selected:', label);
        console.log('✅ New range:', newRange);
        console.log('✅ Input value updated:', $reportrange.val());
        console.log('✅ Global variable updated:', window.targetKpiReportRange);
    });
    
    console.log('Target KPI daterangepicker initialized with:', report_range);
    console.log('Available ranges:', {
        'Today': [moment().startOf('day').format('YYYY-MM-DD HH:mm:ss'), moment().format('YYYY-MM-DD HH:mm:ss')],
        'Yesterday': [moment().subtract(1, 'days').startOf('day').format('YYYY-MM-DD HH:mm:ss'), moment().subtract(1, 'days').endOf('day').format('YYYY-MM-DD HH:mm:ss')],
        'Last 7 Days': [moment().subtract(6, 'days').startOf('day').format('YYYY-MM-DD HH:mm:ss'), moment().format('YYYY-MM-DD HH:mm:ss')],
        'This Month': [moment().startOf('month').format('YYYY-MM-DD HH:mm:ss'), moment().format('YYYY-MM-DD HH:mm:ss')]
    });
}

// Initialize on document ready
$(document).ready(function() {
    initTargetKpiDatepicker();
    
    // Handle form filter submission via AJAX
    $(document).off('submit', '#form-filter-target').on('submit', '#form-filter-target', function(e) {
        e.preventDefault();
        
        console.log('=== TARGET KPI FORM SUBMIT ===');
        
        // Get values with fallback to global stored values
        var reportrange = $('#reportrange').val() || window.targetKpiReportRange;
        var role = $('#role').val() || window.targetKpiCurrentRole || 'PICKER';
        
        // Validate reportrange
        if (!reportrange || reportrange === 'undefined' || reportrange.trim() === '' || reportrange === 'null') {
            console.warn('Invalid reportrange, using today');
            reportrange = moment().startOf('day').format('YYYY-MM-DD HH:mm:ss') + ' - ' + moment().format('YYYY-MM-DD HH:mm:ss');
        }
        
        // Validate role
        if (!role || role === 'undefined' || role.trim() === '' || role === 'null') {
            console.warn('Invalid role, using PICKER');
            role = 'PICKER';
        }
        
        // Update global stored values
        window.targetKpiReportRange = reportrange;
        window.targetKpiCurrentRole = role;
        
        console.log('Filtering Target KPI with:', { reportrange: reportrange, role: role });
        console.log('Parsed dates:', {
            start: reportrange.split(' - ')[0],
            end: reportrange.split(' - ')[1]
        });
        
        var btn = $('#btn-search');
        if (btn.length > 0) {
            var originalBtnHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        }
        
        // Show loading
        var loading = "<div style='max-width: 100%; display: flex; justify-content: center; align-items: center; padding: 50px;'><img src='assets/img/LoaderIcon.gif' /></div>";
        $('.page-content-wrap').html(loading);
        
        $.ajax({
            url: '<?= base_url('target_kpi/index') ?>',
            type: 'GET',
            data: {
                ajax: '1',
                reportrange: reportrange,
                role: role
            },
            dataType: 'text', // Changed to text to handle parsing manually
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            success: function(response) {
                console.log('Response received:', response);
                console.log('Response type:', typeof response);
                
                // Handle both JSON object and string
                var data = response;
                if (typeof response === 'string') {
                    try {
                        data = JSON.parse(response);
                    } catch(e) {
                        console.error('JSON parse error:', e);
                        console.error('Raw response:', response);
                        noty({text: 'Error parsing response. Check console.', timeout: 3000, layout: 'topRight', type: 'error'});
                        return;
                    }
                }
                
                if (data.view) {
                    // Replace content with new view
                    $('.page-content-wrap').html(data.view);
                    
                    // Re-initialize plugins
                    if (typeof formElements !== 'undefined' && formElements.init) formElements.init();
                    if (typeof uiElements !== 'undefined' && uiElements.init) uiElements.init();
                    if (typeof templatePlugins !== 'undefined' && templatePlugins.init) templatePlugins.init();
                    
                    // Re-initialize daterangepicker
                    setTimeout(function() {
                        if (typeof initTargetKpiDatepicker !== 'undefined') {
                            initTargetKpiDatepicker();
                        }
                    }, 200);
                    
                    noty({text: 'Data berhasil di-update', timeout: 2000, layout: 'topRight', type: 'success'});
                }
                if (data.message) {
                    noty({text: data.message, timeout: 3000, layout: 'topRight', type: 'success'});
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr, status, error);
                console.error('Status:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                
                noty({text: 'Error loading data. Check console for details.', timeout: 3000, layout: 'topRight', type: 'error'});
                
                // Try to show error message
                if (xhr.responseText) {
                    // Check if it's HTML error
                    if (xhr.responseText.indexOf('<!DOCTYPE') !== -1 || xhr.responseText.indexOf('<html') !== -1) {
                        console.error('Received HTML instead of JSON. Possible PHP error.');
                    }
                    $('.page-content-wrap').html('<div class="alert alert-danger"><strong>Error:</strong> ' + error + '</div>');
                }
            }
        });
        
        return false;
    });
    
    // Role change - no auto-submit (manual click button required)
    
    // Enable/disable target input when checkbox is checked
    $(document).on('change', '.user-checkbox', function() {
        var targetInput = $(this).closest('label').find('input[name="targets[]"]');
        if ($(this).is(':checked')) {
            targetInput.prop('disabled', false).focus();
        } else {
            targetInput.prop('disabled', true).val('');
        }
    });
    
    // Search/filter user in modal set target
    $(document).on('input', '#user-search', function() {
        var searchTerm = $(this).val().toLowerCase().trim();
        var visibleCount = 0;
        
        $('.user-item').each(function() {
            var searchText = $(this).data('search-text');
            
            if (searchText.indexOf(searchTerm) !== -1 || searchTerm === '') {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Update counter
        $('#user-count').text(visibleCount);
        
        // Show "no results" message if needed
        if (visibleCount === 0 && searchTerm !== '') {
            if ($('#no-results-msg').length === 0) {
                $('#user-list').prepend('<div id="no-results-msg" class="text-muted text-center" style="padding: 20px;"><i class="fa fa-search"></i> Tidak ada user yang cocok dengan pencarian "<strong>' + searchTerm + '</strong>"</div>');
            }
        } else {
            $('#no-results-msg').remove();
        }
    });
    
    // Show modal set target - using delegated event
    $(document).off('click', '#btn-set-target').on('click', '#btn-set-target', function() {
        // Reset search when opening modal
        $('#user-search').val('');
        $('.user-item').show();
        $('#user-count').text($('.user-item').length);
        $('#no-results-msg').remove();
        
        // Uncheck all checkboxes and disable target inputs
        $('.user-checkbox').prop('checked', false);
        $('input[name="targets[]"]').prop('disabled', true).val('');
        
        $('#modal-set-target').modal('show');
    });
    
    // Submit set target - using delegated event
    $(document).off('submit', '#form-set-target').on('submit', '#form-set-target', function(e) {
        e.preventDefault();
        
        // Show loading
        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('target_kpi/save_targets') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debug
                
                $submitBtn.html(originalText).prop('disabled', false);
                
                if (response.code == 200 || response.code == 201) {
                    // Success - pakai SweetAlert atau alert biasa dengan icon
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        alert('✅ BERHASIL!\n\n' + response.message);
                        location.reload();
                    }
                } else {
                    // Error
                    alert('❌ ERROR!\n\n' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText); // Debug
                $submitBtn.html(originalText).prop('disabled', false);
                
                var errorMsg = 'Terjadi kesalahan saat menyimpan target';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMsg = response.message;
                    }
                } catch(e) {}
                
                alert('❌ ERROR!\n\n' + errorMsg);
            }
        });
    });
    
    // Show modal edit - using delegated event
    $(document).off('click', '.btn-edit').on('click', '.btn-edit', function() {
        $('#edit-id-user').val($(this).data('user-id'));
        $('#edit-username').text($(this).data('username'));
        $('#edit-nama').text($(this).data('nama'));
        $('#edit-target').val($(this).data('target'));
        $('#edit-keterangan').val($(this).data('keterangan'));
        $('#modal-edit-target').modal('show');
    });
    
    // Submit edit target - using delegated event
    $(document).off('submit', '#form-edit-target').on('submit', '#form-edit-target', function(e) {
        e.preventDefault();
        
        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('target_kpi/update_target') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $submitBtn.html(originalText).prop('disabled', false);
                
                if (response.code == 200 || response.code == 201) {
                    alert('✅ BERHASIL!\n\n' + response.message);
                    location.reload();
                } else {
                    alert('❌ ERROR!\n\n' + response.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
                $submitBtn.html(originalText).prop('disabled', false);
                alert('❌ ERROR!\n\nTerjadi kesalahan saat mengupdate target');
            }
        });
    });
    
    // Delete target - using delegated event
    $(document).off('click', '.btn-delete').on('click', '.btn-delete', function() {
        if (!confirm('Hapus target untuk user ' + $(this).data('username') + '?')) {
            return;
        }
        
        var id_target = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('target_kpi/delete_target') ?>',
            type: 'POST',
            data: { id_target: id_target },
            dataType: 'json',
            success: function(response) {
                if (response.code == 200) {
                    alert('✅ ' + response.message);
                    location.reload();
                } else {
                    alert('❌ ' + response.message);
                }
            },
            error: function() {
                alert('❌ Terjadi kesalahan saat menghapus target');
            }
        });
    });
    
    // Show modal copy target - using delegated event
    $(document).off('click', '#btn-copy-target').on('click', '#btn-copy-target', function() {
        $('#modal-copy-target').modal('show');
    });
    
    // Submit copy target - using delegated event
    $(document).off('submit', '#form-copy-target').on('submit', '#form-copy-target', function(e) {
        e.preventDefault();
        
        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Copying...').prop('disabled', true);
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('target_kpi/copy_targets') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $submitBtn.html(originalText).prop('disabled', false);
                
                if (response.code == 200 || response.code == 201) {
                    alert('✅ BERHASIL!\n\n' + response.message);
                    location.reload();
                } else {
                    alert('❌ ERROR!\n\n' + response.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
                $submitBtn.html(originalText).prop('disabled', false);
                alert('❌ ERROR!\n\nTerjadi kesalahan saat copy target');
            }
        });
    });
});
</script>

<style>
/* Form Control */
.form-control {
    height: 34px;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.form-control:focus {
    border-color: #66afe9;
    outline: 0;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
}

.input-group {
    position: relative;
    display: table;
    border-collapse: separate;
}

.input-group .form-control {
    position: relative;
    z-index: 2;
    float: left;
    width: 100%;
    margin-bottom: 0;
}

.input-group-addon {
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 400;
    line-height: 1;
    color: #555;
    text-align: center;
    background-color: #eee;
    border: 1px solid #ccc;
    border-radius: 4px;
    display: table-cell;
    white-space: nowrap;
    vertical-align: middle;
    width: 1%;
}

.badge-primary {
    background-color: #337ab7;
    color: white;
}

.checkbox {
    padding: 5px;
    border-bottom: 1px solid #eee;
}

.checkbox:hover {
    background-color: #f5f5f5;
}
</style>

