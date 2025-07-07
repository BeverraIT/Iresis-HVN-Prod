<div class="row">
  <div class="col-md-12">

    <div class="panel panel-default tabs">
      <ul class="nav nav-tabs nav-justified">
        <li class="active"><a href="#tab0" data-toggle="tab">Laporan Picker</a></li>
        <li><a href="#tab1" data-toggle="tab">Laporan Packer</a></li>
      </ul>
      <div class="panel-body tab-content">
        <div class="tab-pane active" id="tab0">
          <?php $this->load->view('report/production_team_report_tab0') ?>
        </div>
        <div class="tab-pane" id="tab1">
          <?php $this->load->view('report/production_team_report_tab1') ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
</script>