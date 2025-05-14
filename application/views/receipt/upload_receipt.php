<div class="row">
    <div class="col-md-6 center-block float-none">
        <form action="receipt/upload-receipt-action" method="post" enctype="multipart/form-data" class="form-horizontal" id="form_upload_resi_jubelio" autocomplete="off">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>Upload File Dari Jubelio</strong></h3>
                </div>

                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-md-3 col-xs-12 control-label">Upload File</label>
                        <div class="col-md-8 col-xs-12">
                            <input id="receipt_file" class="form-control" type="file" name="receiptFile" accept=".xls, .xlsx">
                        </div>
                    </div>

                    <div class="tile tile-default bg-success" id="div_container_latest_receipt">
                        <span id="span_latest_receipt">-</span>
                        <p><small id="p_latest_receipt_message"></small></p>
                    </div>

                </div>

                <div class="panel-footer">
                    <button type="submit" class="btn btn-info">Submit</button>
                    <button type="reset" class="btn btn-primary pull-right">Reset</button>
                </div>
            </div>
        </form>

        <div id="response_message">-</div>

        <!-- Loading Pop UP -->
        <div id="loadingPopUp" class="custom-popup-overlay" style="display: none;">
            <div class="custom-popup-box col-md-6 center-block float-none">
                <h1><strong>Loading!</strong></h1>
                <p>Data Sedang Di Proses, Siahkan Tunggu...</p>
            </div>
        </div>


    </div>
</div>

<script type="text/javascript">

    let jvalidate = $("#form_upload_resi_jubelio").validate({
        ignore: [],
        rules: {
            receiptFile: {
                required: true
            }
        },
        messages: {
            receiptFile: {
                required: "Silahkan Input File Terlebih Dahulu!" // Pesan kesalahan
            }
        },
        invalidHandler: function(event, validator) {
            // Callback ketika form gagal divalidasi
            console.log("=============> Input file tidak diisi");

            // Mainkan audio jika validasi gagal
            document.getElementById('audio-fail').play();
        },
        submitHandler: function(form) {

            var formData = new FormData(form);

            $.ajax({
                url: form.action,
                type: 'post',
                data: formData,
                contentType: false, // Jangan set contentType, jQuery akan menanganinya
                processData: false, // Jangan Merubah data menjadi query string
                success: function(data) {},
                error: function(data) {},
                beforeSend: function() {
                    $('#loadingPopUp').show(); // Pastikan spinner tampil sebelum kirim
                }
            })
                .done(function(response) {
                    console.log("==============> Response Success", response);
                    $('#loadingPopUp').hide(); // Pastikan spinner di sembunyikan setelah sukses

                    let res = JSON.parse(response);

                    document.getElementById('audio-alert').play();

                    $("#span_latest_receipt").text(res.message);
                    $("#div_container_latest_receipt").removeClass("tile-danger tile-default").addClass("tile-success");

                    $('#receipt_file').val(''); // Mengosongkan input file
                })
                .fail(function(response) {
                    console.log("==============> Response Error", response);
                    $('#loadingPopUp').hide(); // Pastikan spinner di sembunyikan setelah sukses

                    let res = JSON.parse(response.responseText);

                    document.getElementById('audio-fail').play();

                    $("#span_latest_receipt").text("Error");
                    $("#div_container_latest_receipt").removeClass("tile-default").addClass("tile-danger");

                    $('#receipt_file').val(''); // Mengosongkan input file
                })

            return false; // supaya action setelah submit success maupun error di handle disini bukan di controller
        }
    });
</script>

<style>
    .custom-popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9998;
    }

    .custom-popup-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        width: 50%;
        text-align: center;
    }

    .custom-popup-buttons {
        margin-top: 15px;
    }

    .custom-popup-buttons button {
        margin: 0 5px;
    }

    table.dataTable tbody td {
        vertical-align: middle;
        padding-top: 30px;
        padding-bottom: 30px;
    }

    .modal-content-custom {
        position: relative;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        max-width: 600px;
        width: 90%;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }

    /* .modal-content-custom {
      background: white;
      padding: 20px;
      border-radius: 8px;
      width: 100%;
      max-width: 600px;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
      } */

    .close-button {
        position: absolute;
        top: -15px;
        right: -2px;
        font-size: 34px;
        background: none;
        border: none;
        color: #333;
        cursor: pointer;
        z-index: 10;
    }

    .close-button:hover {
        color: red;
    }
</style>