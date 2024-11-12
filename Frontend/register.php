<?php
session_start();
error_reporting(0);
include("class.php");
$sinflobis = new sinflobis;
$koneksi = $sinflobis->koneksi();

// input data login admin
// $user = "admin";
// $pass = "admin";
// $pass_hash = password_hash($pass, PASSWORD_DEFAULT); //hashing password
// mysqli_query($koneksi, "INSERT INTO login VALUES ('$user','$pass_hash',NOW(),NOW())");

if (isset($_POST['tombol'])) {
  $reg_user = $_POST['username'];
  $reg_pswd = $_POST['password'];
  $reg_fname = $_POST['nama'];
  $reg_noWA = $_POST['telepon'];

  $pass_hash = password_hash($reg_pswd, PASSWORD_DEFAULT);

  $cek_akun = mysqli_query($koneksi, "SELECT username,telepon FROM login WHERE username = '$reg_user' OR telepon = '$reg_noWA'");
  $ambil_data = mysqli_num_rows($cek_akun);

  if (empty($ambil_data)) {
    $token = "_88AV_@dmuy3QfApzCUb";
    $curl = curl_init();
    $otp = rand(1000, 9999);
    // $waktu = time();
    mysqli_query($koneksi, "INSERT INTO login VALUES ('$reg_user', '$pass_hash', NOW(), NOW(), '$reg_fname', '$reg_noWA', 'default.png', '$otp', '0')");

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'target' => $reg_noWA,
        'message' => "Kode aktivasi akun sinflobis anda : " . $otp,
        'countryCode' => '62', //optional
      ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token" //change TOKEN to your actual token
      ),
    ));
    $result = curl_exec($curl);
    curl_close($curl);

    $_SESSION['noWA'] = $reg_noWA;

    echo "<script>window.location.replace('html/otp/index.php')</script>";
  } else {
    $reg_user = "";
    $reg_pswd = "";
    $reg_fname = "";
    $reg_noWA = "";
    echo "<div class=\"alert alert-dark center login\" 
                role=\"alert\"
                style=\"
                  max-width: 50%;
                  z-index: 999999999999;
                  margin: auto;
                  position: absolute;
                  top: 10px;
                  left: 50%;
                  transform: translateX(-50%);
                  display: flex;
                  align-items:center;
                  gap: 10px;
                \">
                <i class='bx bx-error'></i> Nomor atau Username Anda sudah digunakan.
              </div>";
  }
}

?>
<!DOCTYPE html>
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>SINFLOBIS | Register</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="./img/placeholder.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <!-- Core CSS -->

  <!-- Page CSS -->
  <link rel="stylesheet" href="./css/login.css">
  <link rel="stylesheet" href="./css/style.css">
  <!-- Page -->
  <script src="./js/jquery-3.6.4.js"></script>
  <script>
    $(function() {
      $("i.bx").click(function() {
        $(this).toggleClass('bx-hide bx-show');
        if ($("input#password").attr('type') === 'text') {
          $("input#password").attr('type', 'password');
        } else if ($("input#password").attr('type') === 'password') {
          $("input#password").attr('type', 'text');
        };
      });
      $(".alert").fadeOut(5000);
    });
  </script>
</head>

<body>
  <!-- Content -->

  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register Card -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
              <div class="app-brand-link">
                <span class="app-brand-logo"><img src="./img/sinflobis2.jpg" alt=""></span>
              </div>
            </div>
            <!-- /Logo -->
            <div class="d-flex flex-column align-items-center">
              <h4 class="mb-2">Sistem Informasi Lokasi Bisnis</h4>
              <p class="mb-4">Silahkan isi form berikut !</p>
            </div>

            <form id="formAuthentication" class="mb-3" action="" method="POST">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group input-group-merge">
                  <input
                    type="text"
                    class="form-control"
                    id="username"
                    name="username"
                    placeholder="Masukkan Username"
                    autofocus />
                  <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bx-user'></i></span>
                </div>
              </div>
              <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <div class="input-group input-group-merge">
                  <input
                    type="text"
                    class="form-control"
                    id="nama"
                    name="nama"
                    placeholder="Masukkan Nama Lengkap"
                    autofocus />
                  <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bx-id-card'></i></span>
                </div>
              </div>
              <div class="mb-3">
                <label for="telepon" class="form-label">Nomor Whatsapp</label>
                <div class="input-group input-group-merge">
                  <input type="number" class="form-control" id="telepon" name="telepon" placeholder="Contoh: 08..." />
                  <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                </div>
              </div>
              <div class="mb-3 form-password-toggle">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="password"
                    minlength="8"
                    class="form-control"
                    name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>

              <!-- <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                  <label class="form-check-label" for="terms-conditions">
                    I agree to
                    <a href="javascript:void(0);">privacy policy & terms</a>
                  </label>
                </div>
              </div> -->
              <button class="btn btn-warning d-grid w-100" type="submit" name="tombol" value="tombol">Register</button>
            </form>

            <p class="text-center">
              <span>Sudah punya akun?</span>
              <a href="index.php">
                <span>Beralih ke halaman login</span>
              </a>
            </p>
          </div>
        </div>
        <!-- Register Card -->
      </div>
    </div>
  </div>

  <!-- / Content -->

</body>

</html>