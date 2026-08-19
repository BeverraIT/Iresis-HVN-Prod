<!-- DEBUG MARKER: MAIN TEMPLATE START -->
<!DOCTYPE html>
<html lang="en">

<head>
	<!-- META SECTION -->
	<title>BEVERRA - Manajemen Resi</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<!-- END META SECTION -->

	<!-- CSS INCLUDE -->
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
	
	<link rel="stylesheet" type="text/css" id="theme" href="assets/css/theme-black.css?v=<?= time() ?>" />
	<!-- EOF CSS INCLUDE -->
</head>

<body>
	<!-- START PAGE CONTAINER -->
	<div class="page-container page-navigation-top-fixed">

		<!-- START PAGE SIDEBAR -->
		<div class="page-sidebar page-sidebar-fixed scroll">
			<!-- START X-NAVIGATION -->
			<ul class="x-navigation">
				<li class="xn-logo">
					<a href="">BEVERRA</a>
					<a href="#" class="x-navigation-control"></a>
				</li>

				<li class="xn-profile">
					<a href="#" class="profile-mini">
						<img src="assets/img/no-image.jpg" alt="John Doe" />
					</a>
					<div class="profile">
						<div class="profile-image">
							<img src="assets/img/no-image.jpg" alt="John Doe" />
						</div>
						<div class="profile-data">
							<div class="profile-data-name"><?= $user['username'] ?></div>
							<div class="profile-data-title"><?= $user['akses'] . ' - ' . $nama_pk ?></div>
                            <?php if (!empty($status_performa)) : ?>
                            <div class="profile-data-title">Status: <?= $status_performa ?></div>
                            <?php endif; ?>
						</div>					</div>
				</li>

				<li class="xn-title">Navigation</li>

				<li class="active">
					<a href=""><span class="fa fa-desktop"></span> <span class="xn-text">Dashboard</span></a>
				</li>

				<?= $html_menu_tree ?>
			</ul>
			<!-- END X-NAVIGATION -->
		</div>
		<!-- END PAGE SIDEBAR -->

		<!-- PAGE CONTENT -->
		<div class="page-content">

			<!-- START X-NAVIGATION VERTICAL -->
			<ul class="x-navigation x-navigation-horizontal x-navigation-panel">
				<!-- TOGGLE NAVIGATION -->
				<li class="xn-icon-button">
					<a href="#" class="x-navigation-minimize"><span class="fa fa-dedent"></span></a>
				</li>
				<!-- END TOGGLE NAVIGATION -->

				<!-- SIGN OUT -->
				<li class="xn-icon-button pull-right">
					<a href="#" class="mb-control" data-box="#mb-signout"><span class="fa fa-sign-out"></span></a>
				</li>
				<!-- END SIGN OUT -->
			</ul>
			<!-- END X-NAVIGATION VERTICAL -->

			<!-- START BREADCRUMB -->
			<ul class="breadcrumb">
				<li>Welcome</li>
			</ul>
			<!-- END BREADCRUMB -->

			<!-- PAGE CONTENT WRAPPER -->
			<div class="page-content-wrap">

				                
				                				<?= $content ?>

			</div>
			<!-- END PAGE CONTENT WRAPPER -->
		</div>
		<!-- END PAGE CONTENT -->
	</div>
	<!-- END PAGE CONTAINER -->

	<!-- MESSAGE BOX-->
	<div class="message-box animated fadeIn" data-sound="alert" id="mb-signout">
		<div class="mb-container">
			<div class="mb-middle">
				<div class="mb-title"><span class="fa fa-sign-out"></span> Log <strong>Out</strong> ?</div>
				<div class="mb-content">
					<p>Are you sure you want to log out?</p>
					<p>Press No if youwant to continue work. Press Yes to logout current user.</p>
				</div>
				<div class="mb-footer">
					<div class="pull-right">
						<a href="logout" class="btn btn-success btn-lg">Yes</a>
						<button class="btn btn-default btn-lg mb-control-close">No</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END MESSAGE BOX-->

	<!-- START PRELOADS -->
	<audio id="audio-alert" src="assets/audio/alert.mp3" preload="auto"></audio>
	<audio id="audio-fail" src="assets/audio/fail.mp3" preload="auto"></audio>
	<audio id="audio-error" src="assets/audio/error.mp3" preload="auto"></audio>
	<audio id="audio-jnt" src="assets/audio/jnt.mp3" preload="auto"></audio>
	<audio id="audio-shopee" src="assets/audio/shopee.mp3" preload="auto"></audio>
	<audio id="audio-lazada" src="assets/audio/lazada.mp3" preload="auto"></audio>
	<audio id="audio-jne" src="assets/audio/jne.mp3" preload="auto"></audio>
	<audio id="audio-rekomen" src="assets/audio/rekomen.mp3" preload="auto"></audio>
	<audio id="audio-sicepat" src="assets/audio/sicepat.mp3" preload="auto"></audio>
	<audio id="audio-ninja" src="assets/audio/ninja.mp3" preload="auto"></audio>
	<audio id="audio-instant" src="assets/audio/instant.mp3" preload="auto"></audio>

	<!-- Bunyi khusus modul timbangan -->
	<audio id="audio-benar" src="assets/audio/benar.wav" preload="auto"></audio>
	<audio id="audio-beda" src="assets/audio/beda.wav" preload="auto"></audio>
	<audio id="audio-salah" src="assets/audio/salah.wav" preload="auto"></audio>
	<!-- END PRELOADS -->

	<!-- START PLUGINS -->
	<script type="text/javascript" src="assets/js/plugins/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/jquery/jquery-ui.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/bootstrap/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/bootstrap/bootstrap-select.js"></script>
	<script type="text/javascript" src="assets/js/plugins/bootstrap/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="assets/js/plugins/bootstrap/bootstrap-timepicker.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/bootstrap/bootstrap-file-input.js"></script>
	<script type="text/javascript" src="assets/js/plugins/mcustomscrollbar/jquery.mCustomScrollbar.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/owl/owl.carousel.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/jstree/jstree.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/summernote/summernote.js"></script>
	<script type="text/javascript" src="assets/js/plugins/icheck/icheck.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/datatables/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/nvd3/lib/d3.v3.js"></script>
	<script type="text/javascript" src="assets/js/plugins/nvd3/nv.d3.min.js"></script>
	<script type='text/javascript' src='assets/js/plugins/jquery-validation/jquery.validate.js'></script>

	<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

	<script type='text/javascript' src='assets/js/plugins/noty/jquery.noty.js'></script>
	<script type='text/javascript' src='assets/js/plugins/noty/layouts/center.js'></script>
	<script type='text/javascript' src='assets/js/plugins/noty/layouts/topRight.js'></script>

	<script type='text/javascript' src='assets/js/plugins/noty/themes/default.js'></script>
	<!-- END PLUGINS -->

	<!-- START TEMPLATE -->
	<script type="text/javascript" src="assets/js/plugins.js?v=<?= time() ?>"></script>
	<script type="text/javascript" src="assets/js/actions.js?v=<?= time() ?>"></script>
	<!-- END TEMPLATE -->

	<!-- START HALAMAN AWAL -->
	<?php
	// Hanya berlaku di halaman utama. Kalau pengguna membuka alamat halaman
	// tertentu secara langsung, halaman itulah yang harus tampil -- jangan
	// ditimpa menu awal.
	$di_halaman_utama = $this->router->class === 'welcome';
	?>
	<?php if ($di_halaman_utama && defined('LANDING_MENU_URI') && LANDING_MENU_URI !== '') : ?>
		<script type="text/javascript">
			// Buka menu tertentu begitu shell selesai dimuat. Karena isi halaman
			// diambil lewat AJAX (devScript.openPage), alamat di address bar tetap
			// pendek: <?= base_url() ?> tanpa embel-embel /timbangan/scan-timbangan.
			//
			// Kalau user tidak punya hak akses menu itu, link-nya tidak ada di
			// sidebar dan blok ini tidak melakukan apa-apa (dashboard biasa tampil).
			$(function() {
				var uriAwal = <?= json_encode(LANDING_MENU_URI) ?>;
				var $menu = $('a.link[href="' + uriAwal + '"]').first();

				if ($menu.length) {
					$menu.trigger('click');
				}
			});
		</script>
	<?php endif; ?>
	<!-- END HALAMAN AWAL -->
</body>

</html>