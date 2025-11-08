<!DOCTYPE html>
<html lang="en" class="body-full-height">

<head>
    <!-- META SECTION -->
    <title>IRESIS - Sistem Informasi Resi dan Stock</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" id="theme" href="assets/css/theme-default.css" />
	
	<!-- CSS INCLUDE -->
    <link rel="stylesheet" type="text/css" id="theme" href="assets/css/theme-default.css" />
    <!-- EOF CSS INCLUDE -->
    <!-- STYLE OVERRIDE -->
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('http://192.168.1.179:8080/iresis/assets/img/bg-iresis.png') no-repeat center center fixed !important;
            background-size: cover !important;
            background-color: #1a1a1a !important;
        }

        .login-container {
            background: transparent !important;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: rgba(0, 0, 0, 0.78);
            border-radius: 25px;
            padding: 45px;
            width: 380px;
            box-shadow: 0 0 40px rgba(255, 105, 180, 0.35);
            backdrop-filter: blur(6px);
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-logo {
            text-align: center;
            font-size: 44px;
            font-weight: 700;
            color: #ff4081;
            letter-spacing: 4px;
            margin-bottom: 5px;
            margin-top: -30px;
            text-shadow: 0 0 20px rgba(255, 105, 180, 0.8);
        }

        .login-subtitle-text {
            text-align: center;
            font-size: 14px;
            color: #ffd3e6;
            letter-spacing: 1px;
            margin-bottom: 25px;
            font-weight: 400;
            text-shadow: 0 0 8px rgba(255, 182, 193, 0.5);
        }

        .login-box input,
        .login-box select {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 8px;
            color: #fff;
            padding: 10px 12px;
        }

        .login-box input::placeholder,
        .login-box select {
            color: #ccc;
        }

        .btn-info {
            background-color: #ff4081 !important;
            border: none !important;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-info:hover {
            background-color: #e73370 !important;
            transform: scale(1.03);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #ff85b2;
            font-size: 12px;
        }

        .login-footer a {
            color: #ffb6c1;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .help-block {
            color: #d9534f;
            margin-top: 5px;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="login-container">

        <div class="login-box animated fadeInDown">
            <div class="login-body">
                <div class="login-title"><strong>Welcome</strong>, Please login</div>

                <?php if (!empty($message)) : ?>
                    <div class="login-subtitle"><?= $message ?></div>
                <?php endif; ?>

                <form action="auth" class="form-horizontal" method="post" id="loginForm" novalidate>
                    <input type="hidden" name="nama_komputer" value="<?= $machine_name ?>" />
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="text" name="username" id="username" class="form-control" placeholder="Username" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <select name="nama_pk" id="nama_pk" class="form-control select" data-live-search="true" style="font-weight: bold;">
                                <option value="" style="font-weight: bold;" selected disabled>-- Select Computer --</option>
                                <?php foreach ($list_pk as $pk) : ?>
                                    <option
                                            value="<?= $pk['nama_pk'] ?>"
                                            style="font-weight: bold;"
                                            <?= ($this->input->post('nama_pk') == $pk['nama_pk']) ? 'selected' : '' ?>>
                                            <?= $pk['nama_pk'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <select name="status_performa" id="status_performa" class="form-control select" data-live-search="true" style="font-weight: bold;">
                                <option value="" style="font-weight: bold;" selected disabled>-- Select Status Performa --</option>
                                <?php foreach ($list_status_performa as $role => $statuses) : ?>
                                    <!-- <optgroup label="<?= $role ?>"> -->
                                        <?php foreach ($statuses as $status) : ?>
                                            <option value="<?= $status['status_name'] ?>"><?= $status['status_name'] ?></option>
                                        <?php endforeach; ?>
                                    <!-- </optgroup> -->
                                <?php endforeach; ?>
                            </select>
                            <span class="help-block" style="color: #d9534f; margin-top: 5px; font-size: 12px;"><strong>* Status Performa wajib dipilih untuk menghindari kesalahan data</strong></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-info btn-block">Log In</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="login-footer">
                <div class="pull-left">
                    &copy; 2025 BEVERRA
                    <p>
                        Login dengan scanner <a href="login?machine_name=<?= $machine_name ?>&using_scanner=1">di sini</a>
                    </p>
                </div>
            </div>
        </div>

    </div>

</body>

</html>