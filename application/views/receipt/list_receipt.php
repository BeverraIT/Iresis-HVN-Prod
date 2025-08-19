<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Daftar Resi</strong></h3>
            </div>

            <div class="panel-body">
                <table class="table table-striped datatable-resi">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Resi</th>
                        <th>Tanggal Scan Resi</th>
                        <th>Kurir</th>
                        <th>Market Place</th>
                        <th>Nomor Picklist</th>
                        <th>Status Pesanan</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.datatable-resi').DataTable({
            'scrollX': true,
            'pageLength': 10,
            'processing': true,
            'serverSide': true,
            'order': [
                [2, 'desc']
            ],
            'lengthMenu': [
                [10, 50, 100, 150, 200],
                [10, 50, 100, 150, 200]
            ],
            'ajax': {
                url: 'receipt/get-list-receipt-data',
                type: 'POST',
            },
        });
    });
</script>

<?php if ($this->session->flashdata('noty_message')):
    $msg = $this->session->flashdata('noty_message');
    ?>
    <script>
        $(document).ready(function() {
            noty({
                text: "<?= htmlspecialchars($msg['text'], ENT_QUOTES) ?>",
                layout: 'topRight',
                type: "<?= $msg['type'] ?>",
                timeout: 3000
            });
        });
    </script>
<?php endif; ?>