<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Cari Resi Picker</strong></h3>
      </div>

      <div class="panel-body">
        <table class="table table-striped datatable-picking">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Picker</th>
              <th>Tanggal Resi Ambil Barang</th>
              <th>Jam Resi Ambil Barang</th>
              <th>Diinput Oleh</th>
              <th>Nama Komputer</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('.datatable-picking').DataTable({
      'scrollX': true,
      'pageLength': 10,
      'processing': true,
      'serverSide': true,
      'deferRender': true, // Improve rendering performance
      'stateSave': true, // Save user's state (pagination, sorting)
      'order': [
        [3, 'desc']
      ],
      'lengthMenu': [
        [10, 50, 100, 200],
        [10, 50, 100, 200]
      ],
      'ajax': {
        url: 'picker/get-search-picker-data',
        type: 'POST',
        timeout: 30000, // 30 second timeout
        error: function(xhr, error, thrown) {
          console.error('DataTable AJAX Error:', error, thrown);
          alert('Terjadi kesalahan saat memuat data. Silakan refresh halaman.');
        }
      },
      'language': {
        'processing': 'Memuat data...',
        'loadingRecords': 'Memuat...',
        'emptyTable': 'Tidak ada data',
        'zeroRecords': 'Tidak ada data yang sesuai'
      },
      'searchDelay': 800, // Delay search to reduce server requests
      'columnDefs': [
        {
          'targets': [0, 6], // Non-sortable columns
          'orderable': false,
          'searchable': false
        },
        {
          'targets': [3, 4], // Date/time columns
          'type': 'datetime'
        }
      ]
    });
  });
</script>