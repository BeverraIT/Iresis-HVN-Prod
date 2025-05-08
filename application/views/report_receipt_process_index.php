<div class="row">
  <div class="col-md-12">

    <div class="panel panel-default tabs">
      <ul class="nav nav-tabs nav-justified">
        <li class="active"><a href="#tab0" data-toggle="tab">Resi Belum Pick</a></li>
        <li><a href="#tab1" data-toggle="tab">Resi Picker Belum Scan Packer</a></li>
        <li><a href="#tab2" data-toggle="tab">Resi Packer Belum Scan Keluar</a></li>
      </ul>
      <div class="panel-body tab-content">
        <div class="tab-pane active" id="tab0">
          <?php $this->load->view('report_receipt_process_tab0') ?>
        </div>
        <div class="tab-pane" id="tab1">
          <?php $this->load->view('report_receipt_process_tab1') ?>
        </div>
        <div class="tab-pane" id="tab2">
          <?php $this->load->view('report_receipt_process_tab2') ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
</script>