<!DOCTYPE html>
<html lang="en" class="body-full-height">

<head>
    <!-- META SECTION -->
    <title>BEVERRA - Manajemen Resi</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <!-- END META SECTION -->

    <!-- CSS INCLUDE -->
    <link rel="stylesheet" type="text/css" id="theme" href="assets/css/theme-default.css" />
    <!-- EOF CSS INCLUDE -->
</head>

<body>

    <div class="login-container">

        <div class="login-box animated fadeInDown">
            <div class="login-body">
                <div class="login-title"><strong>Welcome</strong>, Please login</div>

                <?php if (!empty($message)) : ?>
                    <div class="login-subtitle"><?= $message ?></div>
                <?php endif; ?>

                <form action="auth" class="form-horizontal" method="post">
                    <input type="hidden" name="nama_komputer" value="<?= $machine_name ?>" />
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="text" name="username" class="form-control" placeholder="Username" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="password" name="password" class="form-control" placeholder="Password" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button class="btn btn-info btn-block">Log In</button>
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
                <div class="pull-right">
                    Computer: <?= $machine_name ?>
                </div>
            </div>
        </div>

    </div>

</body>

</html>