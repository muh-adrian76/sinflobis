<?php

session_start();
date_default_timezone_set('Asia/Jakarta');

//koneksi
include("../../class.php");
$db = new sinflobis;
$koneksi = $db->koneksi();

$noWA = $_SESSION['noWA'];

$otp1 = $_POST['otp1'];
$otp2 = $_POST['otp2'];
$otp3 = $_POST['otp3'];
$otp4 = $_POST['otp4'];
$verify_otp = $otp1 . $otp2 . $otp3 . $otp4;

if (isset($_POST['verifyOTP'])) {
  $token = "_88AV_@dmuy3QfApzCUb";
  $curl = curl_init();
  $otp = $verify_otp;
  // $nomor = mysqli_escape_string($koneksi, $_POST['nomor']);
  $q = mysqli_query($koneksi, "SELECT * FROM login WHERE telepon = '$noWA' AND otp = '$otp'");
  $row = mysqli_num_rows($q);
  if ($row) {
    mysqli_query($koneksi, "UPDATE login SET verified='1' WHERE telepon='$noWA'");
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
                <i class='bx bx-check-circle'></i> Aktivasi berhasil.
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
        'message' => 'Akun anda telah terverifikasi, silahkan melakukan login pada website.',
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
                window.location.replace('../../index.php');
            }, 5000);
          </script>";
  } else {
    $otp1 = "";
    $otp2 = "";
    $otp3 = "";
    $otp4 = "";
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
                <i class='bx bx-error'></i> Kode OTP anda salah.
              </div>";
  }
}

?>

<!DOCTYPE html>
<!-- Coding by CodingLab || www.codinglabweb.com -->
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SINFLOBIS | OTP Verification</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" type="image/x-icon" href="../../img/placeholder.png" />
  <!-- Boxicons CSS -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="script.js" defer></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script>
    $(document).ready(function() {
      $(window).on('load', function() {
        $('#spinner').fadeOut('slow');
      });
      $(".alert:not(.alert-success)").fadeOut(5000);

      $("#darkMode").click(function() {
        $("body").toggleClass("dark");
        $(this).toggleClass('bx-moon bx-sun');
      });
    });
  </script>
</head>

<body>
  <div class="container-otp">
    <header>
      <i class="bx bxs-check-shield"></i>
    </header>
    <h4>Masukkan Kode OTP</h4>
    <h6>Silahkan Cek Whatsapp Anda</h6>
    <form class="otp" action="" method="POST">
      <div class="input-field">
        <input type="number" name="otp1" />
        <input type="number" name="otp2" disabled />
        <input type="number" name="otp3" disabled />
        <input type="number" name="otp4" disabled />
      </div>
      <button name="verifyOTP">Verifikasi OTP</button>
    </form>
    <h6>&copy; SINFLOBIS 2024</h6>
  </div>
</body>

</html>