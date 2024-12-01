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
  $user = $_POST['user'];
  $pass = $_POST['pswd'];

  // query data di tabel login username tsb
  $cari_query = mysqli_query($koneksi, "SELECT username,password,verified FROM login WHERE username='$user' OR telepon='$user'");
  // ambil data password hash
  $ambil_data = mysqli_fetch_row($cari_query);
  $pass_db = $ambil_data[1];
  $aktivasi = $ambil_data[2];

  // cek data username tsb ada atau tdk di tabel login 
  $cek_data = mysqli_num_rows($cari_query);
  $pass_hash = password_verify($pass, $pass_db);

  if (!empty($cek_data) && !empty($pass_hash)) {
    if ($aktivasi == '1') {
      //jika login berhasil maka akan membuat session baru username dan password
      session_start();
      $_SESSION['user'] = $user;
      $_SESSION['pass'] = $pass_db;
      mysqli_query($koneksi, "UPDATE login SET last_login=NOW() WHERE username='$user'");

      echo "<script>window.location.replace('html/index.php')</script>";
    } else {
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
              <i class='bx bx-error'></i> Akun anda belum terverifikasi.
            </div>";
      echo "<script>
              // Use setTimeout to give a slight delay before showing the modal
              setTimeout(function() {
                  var myModal = new bootstrap.Modal(document.getElementById('otp'));
                  myModal.show();
              }, 1000); // Adjust the delay time as needed (2000 ms = 2 seconds)
            </script>";
    }
  } else {
    // kalo gagal login diberi alert
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
              <i class='bx bx-error'></i> Data Login Anda Salah.
            </div>";
  }
}

if (isset($_POST['otp'])) {
  $noWA = $_POST['whatsapp'];
  $q = mysqli_query($koneksi, "SELECT * FROM login WHERE telepon = '$noWA'");
  $row = mysqli_num_rows($q);
  if ($row) {
    $token = "_88AV_@dmuy3QfApzCUb";
    $curl = curl_init();
    $otp = rand(1000, 9999);

    mysqli_query($koneksi, "UPDATE login SET otp='$otp'");

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
        'target' => $noWA,
        'message' => "Kode aktivasi akun sinflobis anda : " . $otp,
        'countryCode' => '62', //optional
      ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token" //change TOKEN to your actual token
      ),
    ));
    $result = curl_exec($curl);
    curl_close($curl);

    $_SESSION['noWA'] = $noWA;

    echo "<script>window.location.replace('html/otp/index.php')</script>";
  } else {
    $noWA = "";
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
                <i class='bx bx-error'></i> Nomor anda tidak terdaftar.
              </div>";
  }
}

if (isset($_POST['lupa_pswd'])) {
  $reset_pswd = $_POST['reset_pswd'];
  $q = mysqli_query($koneksi, "SELECT * FROM login WHERE username = '$reset_pswd' OR telepon = '$reset_pswd'");
  $row = mysqli_num_rows($q);
  $d = mysqli_fetch_row($q);
  $noWA = $d[5];
  if ($row) {
    $token = "_88AV_@dmuy3QfApzCUb";
    $curl = curl_init();
    $new_pswd = substr(str_shuffle(str_repeat('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*', 8)), 0, 8);
    $pass_hash = password_hash($new_pswd, PASSWORD_DEFAULT);

    mysqli_query($koneksi, "UPDATE login SET password='$pass_hash' WHERE username = '$reset_pswd' OR telepon = '$reset_pswd'");
    echo "<div class=\"alert alert-success center login\" 
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
                <i class='bx bx-check-circle'></i> Password berhasil diubah, silahkan cek whatsapp anda.
              </div>";
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
        'target' => $noWA,
        'message' => "Berikut adalah password baru untuk akun anda: *" . $new_pswd . "*\nAnda bisa mengubah password pada menu pengaturan akun.",
        'countryCode' => '62', //optional
      ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token" //change TOKEN to your actual token
      ),
    ));
    $result = curl_exec($curl);
    curl_close($curl);

    echo "<script>
            setTimeout(function() {
                window.location.replace('index.php');
            }, 5000);
          </script>";
  } else {
    $reset_pswd = "";
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
                <i class='bx bx-error'></i> Username atau nomor anda tidak terdaftar.
              </div>";
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SINFLOBIS | Login</title>
  <link rel="icon" type="image/x-icon" href="./img/placeholder.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="./css/login.css" />
  <link rel="stylesheet" href="./css/style.css" />
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
      $(".alert:not(.alert-success)").fadeOut(5000);
    });
  </script>

</head>

<body>
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register -->
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
              <h4 class="mb-2 text-center" style="line-height: 2rem;">Sistem Informasi<br>Penentuan Lokasi Bisnis</h4>
              <p class="mb-4">Tolong masuk ke akun anda terlebih dahulu !</p>
            </div>
            <form id="formAuthentication form1" class="mb-3" action="" method="POST">
              <div class="mb-3">
                <label for="email" class="form-label">Username</label>
                <div class="input-group input-group-merge">
                  <input type="text" class="form-control" id="username" name="user" placeholder="Masukkan Username atau No. WA (08...)"
                    autofocus required />
                  <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bx-user'></i></span>
                </div>
              </div>
              <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                  <label class="form-label" for="password">Password</label>
                  <a href="" data-bs-toggle="modal" data-bs-target="#lupa_pswd">
                    <small>Lupa Password?</small>
                  </a>
                </div>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="pswd"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" autofocus
                    required />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>
              <!-- <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                  </div>
                </div> -->
              <div class="mb-3">
                <button class="btn btn-warning d-grid w-100" type="submit" name="tombol" value="tombol">Masuk</button>
              </div>
            </form>
            <p class="text-center register">
              <span>Belum punya akun?</span>
              <a href="register.php">
                <span>Buat akun baru</span>
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class='modal fade' id='otp' aria-labelledby='otp' data-bs-backdrop="static" tabindex='-1' style='display: none' aria-hidden='true'>
      <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
          <!-- Register -->
          <div class='card'>
            <div class='card-body'>
              <div class='d-flex flex-column align-items-center'>
                <h4 class='mb-2'>Verifikasi Akun 🗹</h4>
                <p class='mb-4'>Tolong masukkan nomor anda !</p>
              </div>
              <form id='formAuthentication form2' class='mb-3' action='' method='POST'>
                <div class='mb-3'>
                  <label for='whatsapp' class='form-label'>Nomor Whatsapp</label>
                  <div class="input-group input-group-merge">
                    <input type='number' class='form-control' id='whatsapp' name='whatsapp' placeholder='Contoh: 08...' autofocus required />
                    <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                  </div>
                </div>
                <div class='mb-3'>
                  <button class='btn btn-warning d-grid w-100' type='submit' name='otp' value='otp'>Kirim OTP</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class='modal fade' id='lupa_pswd' aria-labelledby='lupa_pswd' tabindex='-1' style='display: none' aria-hidden='true'>
      <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
          <!-- Register -->
          <div class='card'>
            <div class='card-body'>
              <div class='d-flex flex-column align-items-center'>
                <h4 class='mb-2'>Lupa Password? 🔒</h4>
                <p class='mb-4'>Tolong isi data berikut !</p>
              </div>
              <form id='formAuthentication form3' class='mb-3' action='' method='POST'>
                <div class='mb-3'>
                  <label for='reset_pswd' class='form-label'>Username</label>
                  <div class="input-group input-group-merge">
                    <input type='text' class='form-control' id='reset_pswd' name='reset_pswd' placeholder='Masukkan Username atau Nomor WA (08...)' autofocus required />
                    <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bx-id-card'></i></span>
                  </div>
                </div>
                <div class='mb-3'>
                  <button class='btn btn-warning d-grid w-100' type='submit' name='lupa_pswd' value='lupa_pswd'>Ubah Password</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="./assets/vendor/js/bootstrap.js"></script>
</body>

</html>