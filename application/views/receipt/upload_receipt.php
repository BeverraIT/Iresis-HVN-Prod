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
            <div style="max-width: 100%; display: -webkit-box; display: -ms-flexbox; display: -webkit-flex; display: flex; justify-content: center; align-items: center;">
                <img src="assets/img/LoaderIcon.gif" />
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
                timeout: 600000, // 10 minutes timeout for large files
                success: function(data) {},
                error: function(data) {},
                beforeSend: function() {
                    $('#loadingPopUp').show(); // Pastikan spinner tampil sebelum kirim

                    // Show processing message for large files
                    $("#span_latest_receipt").text("Processing file... This may take several minutes for large files.");
                    $("#div_container_latest_receipt").removeClass("tile-danger tile-success").addClass("tile-default");

                    // Start progress checking for large files
                    window.progressInterval = setInterval(checkUploadProgress, 5000);
                }
            })
                .done(function(response) {
                    console.log("==============> Response Success", response);
                    $('#loadingPopUp').hide(); // Pastikan spinner di sembunyikan setelah sukses
                    clearInterval(window.progressInterval);

                    let res = JSON.parse(response);

                    document.getElementById('audio-alert').play();

                    $("#span_latest_receipt").text(res.message);
                    $("#div_container_latest_receipt").removeClass("tile-danger tile-default").addClass("tile-success");

                    $('#receipt_file').val(''); // Mengosongkan input file

                    // Remove auto-redirect - user stays on upload page
                    // setTimeout(function() {
                    //     window.location.href = 'receipt/list_receipt';
                    // }, 3000);
                })
                .fail(function(response) {
                    console.log("==============> Response Error", response);
                    $('#loadingPopUp').hide(); // Pastikan spinner di sembunyikan setelah sukses
                    clearInterval(window.progressInterval);

                    let errorMessage = "Error processing file";

                    try {
                        let res = JSON.parse(response.responseText);
                        errorMessage = res.message || errorMessage;
                    } catch (e) {
                        // If response is not JSON (timeout case)
                        if (response.status === 0 || response.statusText === 'timeout') {
                            errorMessage = "File processing timed out. Please try with a smaller file or contact support.";
                        }
                    }

                    document.getElementById('audio-fail').play();

                    $("#span_latest_receipt").text(errorMessage);
                    $("#div_container_latest_receipt").removeClass("tile-default tile-success").addClass("tile-danger");

                    $('#receipt_file').val(''); // Mengosongkan input file
                })

            return false; // supaya action setelah submit success maupun error di handle disini bukan di controller
        }
    });

    // Function to check upload progress
    function checkUploadProgress() {
        $.ajax({
            url: 'receipt/check_upload_progress',
            type: 'GET',
            success: function(response) {
                try {
                    let data = JSON.parse(response);
                    if (data.processing) {
                        let minutes = Math.floor(data.elapsed_time / 60);
                        let seconds = data.elapsed_time % 60;
                        let timeStr = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;

                        $("#span_latest_receipt").text(`Processing ${data.row_count} rows... Elapsed: ${timeStr}`);
                    } else {
                        // Processing completed, stop checking but don't redirect
                        clearInterval(window.progressInterval);
                        $("#span_latest_receipt").text("Processing completed!");
                        $("#div_container_latest_receipt").removeClass("tile-danger tile-default").addClass("tile-success");
                    }
                } catch (e) {
                    console.log("Progress check error:", e);
                }
            },
            error: function() {
                // Ignore progress check errors
            }
        });
    }
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