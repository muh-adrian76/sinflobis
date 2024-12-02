<?php
session_start();
if (empty($_SESSION['user']) && empty($_SESSION['pass'])) {
  echo "<script>window.location.replace('../index.php')</script>";
}
//koneksi
include("../class.php");
$sinflobis = new sinflobis;
$koneksi = $sinflobis->koneksi();
$user = $_SESSION['user'];

// ambil keterangan profil
$profil = mysqli_query($koneksi, "SELECT nama,foto FROM login WHERE username='$user' OR telepon='$user'");
$data_prof = mysqli_fetch_row($profil);
$namaAkun = $data_prof[0];
$fotoProfil = $data_prof[1];
// $emailAkun = $data_prof[2];
// $alamat = $data_prof[3];

if (isset($_POST['simpanAkun'])) {
  $namaAkun = $_POST['namaAkun'];
  // $emailAkun = $_POST['emailAkun'];
  $pswdAkun = $_POST['pswdAkun'];
  // $alamat = $_POST['alamat'];

  //upload file
  $target_dir = "assets/img/avatars/";
  $file_foto = basename($_FILES["foto"]["name"]);
  $target_file = $target_dir . $file_foto;

  if (!empty($file_foto) && !empty($pswdAkun)) {
    move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
    $pass_hash = password_hash($pswdAkun, PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE login SET nama='$namaAkun', password='$pass_hash', foto='$file_foto' WHERE username='$user' OR telepon='$user'");
    echo '<div class="alert alert-success" style="
          max-width: 50%;
          z-index: 999999999999;
          margin: auto;
          position: fixed;
          top: 10px;
          left: 50%;
          text-align:center;
          transform: translateX(-50%);
          "><strong>Berhasil</strong> mengubah profil.</div>';
    echo "<script>
            setTimeout(function() {
                window.location.replace('screenshoot.php');
            }, 3000);
          </script>";
  } elseif (!empty($file_foto) && empty($pswdAkun)) {
    move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
    mysqli_query($koneksi, "UPDATE login SET nama='$namaAkun', foto='$file_foto' WHERE username='$user' OR telepon='$user'");
    echo '<div class="alert alert-success" style="
          max-width: 50%;
          z-index: 999999999999;
          margin: auto;
          position: fixed;
          top: 10px;
          left: 50%;
          text-align:center;
          transform: translateX(-50%);
          "><strong>Berhasil</strong> mengubah profil.</div>';
    echo "<script>
            setTimeout(function() {
                window.location.replace('screenshoot.php');
            }, 3000);
          </script>";
  } elseif (empty($file_foto) && !empty($pswdAkun)) {
    $pass_hash = password_hash($pswdAkun, PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE login SET nama='$namaAkun', password='$pass_hash' WHERE username='$user' OR telepon='$user'");
    echo '<div class="alert alert-success" style="
          max-width: 50%;
          z-index: 999999999999;
          margin: auto;
          position: fixed;
          top: 10px;
          left: 50%;
          text-align:center;
          transform: translateX(-50%);
          "><strong>Berhasil</strong> mengubah profil.</div>';
    echo "<script>
            setTimeout(function() {
                window.location.replace('screenshoot.php');
            }, 3000);
          </script>";
  } elseif (!empty($namaAkun)) {
    mysqli_query($koneksi, "UPDATE login SET nama='$namaAkun' WHERE username='$user' OR telepon='$user'");
    echo '<div class="alert alert-success" style="
            max-width: 50%;
            z-index: 999999999999;
            margin: auto;
            position: fixed;
            top: 10px;
            left: 50%;
            text-align:center;
            transform: translateX(-50%);
            "><strong>Berhasil</strong> mengubah profil.</div>';
    echo "<script>
              setTimeout(function() {
                  window.location.replace('scrape.php');
              }, 3000);
            </script>";
    $tombol_val = "simpan";
  }
} else {
  $tombol_val = "simpan";
}


if (isset($_POST["hapusGambar"])) {
  $gambar = $_POST['gambar'];
  $q = mysqli_query($koneksi, "SELECT url from pictures WHERE timestamp='$gambar'");
  $d = mysqli_fetch_row($q);
  $url = $d[0];
  mysqli_query($koneksi, "DELETE FROM pictures WHERE timestamp='$gambar'");
  if (unlink($url)) {
    echo '<div class="alert alert-success" style="
            max-width: 50%;
            z-index: 999999999999;
            margin: auto;
            position: fixed;
            top: 10px;
            left: 50%;
            text-align:center;
            transform : translateX(-50%);
            "><strong>Berhasil</strong> menghapus Screenshot!
        </div>';
  } else {
    echo '<div class="alert alert-warning" style="
        max-width: 50%;
        z-index: 999999999999;
        margin: auto;
        position: fixed;
        top: 10px;
        left: 50%;
        text-align:center;
        transform : translateX(-50%);
        "><strong>File tidak ditemukan!</strong>
    </div>';
  }
  mysqli_query($koneksi, "ALTER TABLE pictures DROP id");
  mysqli_query($koneksi, "ALTER TABLE pictures ADD id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
  echo "<script>
          setTimeout(function() {
            window.location.replace('screenshoot.php');
          }, 3000);
        </script>";
  $gambar = "";
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>SINFLOBIS | Screenshot Trafik</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../img/placeholder.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="../css/style.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../css/main.css">

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <!-- <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet"> -->
  <link rel="stylesheet" href="../assets/vendor/simple-datatables/datatables.min.css">
  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="../assets/vendor/js/helpers.js"></script>

  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="../assets/js/config.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script>
    $(document).ready(function() {

      $(".alert:not(.alert-success)").fadeOut(5000);
      // loader
      const statusChange = function(status) {
        const el = $('.circle-loader')
        el.removeClass()
        el.addClass('circle-loader');
        el.addClass(status);
      }
      $(window).on('load', function() {
        statusChange('sukses');
        setTimeout(function() {
          $('#loading-screen').fadeOut('slow');
        }, 1000)
      });

      table = $('#datatable').DataTable({
        "dom": '<"top"f>rt<"bottom"ilp><"clear">',
        "language": {
          "lengthMenu": 'Tampilkan <select>' +
            '<option value="10">10</option>' +
            '<option value="25">25</option>' +
            '<option value="50">50</option>' +
            '<option value="100">100</option>' +
            '</select> baris data'
        },
        columnDefs: [{
            targets: [5, 6],
            orderable: false
          } // Disable ordering for the first column (index 0)
        ]
      });
      $('#search').on('keyup', function() {
        table.search(this.value).draw();
      });
      // modal delete
      $('#tableBody').on('click', '.trash-btn', function() {
        const gambar = $(this).attr('href');
        const id = $(this).data('gambar');
        console.log(`Gambar: ${gambar}, ID: ${id}`);
        $(".modal-title.delete").text("Konfirmasi Hapus");
        $(".modal-body.delete").html("Apakah anda yakin ingin menghapus gambar nomor <b>" + gambar + "</b> ?");
        const form = `
            <form method='post' id='formHapus'>
                <input type='hidden' name='gambar' value='${id}'>
                <button type='submit' name='hapusGambar' class='btn btn-danger m-2' data-bs-dismiss='modal'>Ya</button>
                <button type='button' class='btn btn-warning' data-bs-dismiss='modal'>Tidak</button>
            </form>`;
        $(".modal-footer.delete").empty().append(form);
      });

      // additional form
      $('#trafik').on('change', function() {
        const selectedValue = $(this).val();
        const additionalRowPlaceholder = $('#additional-row-placeholder');

        if (selectedValue === "1") {
          // Add the additional row if not already added
          if (!$('#additional-row').length) {
            const additionalRow = `
            <div class="row mb-3" id="additional-row">
              <div class="col-lg-1"></div>
                <label class="col-lg-1 col-form-label text-center" for="hari">Hari</label>
                <div class="col-lg-4 mb-3 input-group input-group-merge textarea-input" style="box-shadow: none;">
                  <span id="basic-icon-default-fullname3" class="input-group-text"><i class='bx bx-calendar' ></i></span>
                  <select class="form-select" id="hari" name="hari" required>
                      <option selected>Senin</option>
                      <option>Selasa</option>
                      <option>Rabu</option>
                      <option>Kamis</option>
                      <option>Jumat</option>
                      <option>Sabtu</option>
                      <option>Minggu</option>
                    </select>
                </div>
                <label class="col-lg-1 col-form-label text-center" for="waktu">Pukul</label>
                <div class="col-lg-4 mb-3 input-group input-group-merge" style="box-shadow: none;">
                  <span id="basic-icon-default-fullname4" class="input-group-text"><i class='bx bx-time'></i></span>
                  <input type="text" class="form-control" id="waktu" name="waktu" placeholder="06:00 hingga 22:00" maxlength="5" required>
                </div>
              <div class="col-lg-1"></div>
            </div>
          `;
            additionalRowPlaceholder.append(additionalRow);
          }
        } else {
          // Remove the additional row if exists
          $('#additional-row').remove();
        }
      });
      $('#trafik_manual').on('change', function() {
        const selectedValue = $(this).val();
        const additionalRowPlaceholder = $('#additional-row-placeholder_manual');

        if (selectedValue === "1") {
          // Add the additional row if not already added
          if (!$('#additional-row_manual').length) {
            const additionalRow = `
            <div class="row mb-3" id="additional-row_manual">
              <div class="col-lg-1"></div>
              <label class="col-lg-1 col-form-label text-center" for="hari_manual">Hari</label>
              <div class="col-lg-4 mb-3 input-group input-group-merge textarea-input" style="box-shadow: none;">
                <span id="basic-icon-default-fullname3" class="input-group-text"><i class='bx bx-calendar' ></i></span>
                <select class="form-select" id="hari_manual" name="hari_manual" required>
                    <option selected>Senin</option>
                    <option>Selasa</option>
                    <option>Rabu</option>
                    <option>Kamis</option>
                    <option>Jumat</option>
                    <option>Sabtu</option>
                    <option>Minggu</option>
                  </select>
              </div>
              <label class="col-lg-1 col-form-label text-center" for="waktu_manual">Pukul</label>
              <div class="col-lg-4 mb-3 input-group input-group-merge" style="box-shadow: none;">
                <span id="basic-icon-default-fullname4" class="input-group-text"><i class='bx bx-time'></i></span>
                  <input type="text" class="form-control" id="waktu_manual" name="waktu_manual" placeholder="06:00 hingga 22:00" maxlength="5" required>
                </div>
              <div class="col-lg-1"></div>
            </div>
          `;
            additionalRowPlaceholder.append(additionalRow);
          }
        } else {
          // Remove the additional row if exists
          $('#additional-row_manual').remove();
        }
      });

      $('#ss-db').on('submit', function(event) {
        event.preventDefault();
        statusChange('draw');
        const nama = $('#query').val();
        const trafik = $('#trafik').val();
        const hari = $('#hari').val();
        const waktu = $('#waktu').val();

        const type = trafik === "0" ? 'live' : 'typical';
        const payload = trafik === "0" ? {
          nama,
          type
        } : {
          nama,
          hari,
          waktu,
          type
        };
        if (type === "typical") {
          // cek waktu
          const timeInput = $('#waktu').val();
          const errorMessage = $('#toast-body');
          errorMessage.text('');
          const timePattern = /^(2[0-2]|[01]?[0-9]):[0-5][0-9]$/; // HH:MM format
          if (!timePattern.test(timeInput)) {
            errorMessage.text('Tolong masukkan format waktu dengan benar. Tolong sesuaikan menjadi (Jam:Menit), Contoh: 06:00.');
            $('#showToastPlacement').click();
            return;
          }
          const [hours, minutes] = timeInput.split(':').map(Number);
          if (hours < 6 || hours > 22 || (hours === 22 && minutes > 0)) {
            errorMessage.text('Tolong masukkan rentang waktu dengan benar, yaitu antara pukul 06:00 hingga 22:00.');
            $('#showToastPlacement').click();
            return;
          }
        }
        if (nama === '') {
          event.preventDefault(); // Prevent form submission
          alert('Tolong isi nama rest area atau grup!').show();
        }
        $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
        document.body.style.overflow = 'hidden';
        $('#loading-screen').fadeIn('slow');

        $.ajax({
          url: `http://localhost:3000/screenshots/location`,
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(payload), // Send data as JSON
          // xhrFields: {
          //   responseType: 'blob' // Ensure binary response
          // },
          success: function(response) {
            console.log(response);
            $('#preview-canvas-title h3').text(`Trafik ${response.nama}`);
            $('#preview-canvas-description h6').text(`Waktu Pengambilan Gambar: ${changeDescFormat(response.timestamp)}`);
            fetchTableData();
            statusChange('sukses');
            $('.loading-text').text('Screenshot berhasil!')
            setTimeout(function() {
              $('#loading-screen').fadeOut('fast');
              document.body.style.overflow = 'auto';
              setTimeout(function() {
                statusChange('draw');
                $('#query').val('');
                $('#hari').val('');
                $('#waktu').val('');
                $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
              }, 1000);
            }, 2000);
            const fileDir = `http://localhost/disertasi/sinflobis/backend/src/script/screenshots/${response.file}`;
            const imgElement = `
              <div id="magnify">
                <img src="${fileDir}" id="screenshot-trafik" alt="Screenshot Trafik" style="max-width: 100%; height: auto;">
              </div>
            `;
            $('#preview-canvas').html(imgElement);
            $('#query').empty();
            $('#waktu').empty();
          },
          error: function(error) {
            console.error('Pesan:', error);
            statusChange('failed');
            $('.loading-text').text('Screenshot gagal!')
            setTimeout(function() {
              $('#loading-screen').fadeOut('fast');
              document.body.style.overflow = 'auto';
              setTimeout(function() {
                statusChange('draw');
                $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
              }, 1000);
            }, 2000);
          },
        });
      });

      $('#ss-link').on('submit', function(event) {
        event.preventDefault();
        statusChange('draw');
        const url = $('#link').val();
        const trafik = $('#trafik_manual').val();
        const hari = $('#hari_manual').val();
        const waktu = $('#waktu_manual').val();

        const type = trafik === "0" ? 'live' : 'typical';
        const payload = trafik === "0" ? {
          url,
          type
        } : {
          url,
          hari,
          waktu,
          type
        };
        if (type === "typical") {
          // cek waktu
          const timeInput = $('#waktu_manual').val();
          const errorMessage = $('#toast-body');
          errorMessage.text('');
          const timePattern = /^(2[0-2]|[01]?[0-9]):[0-5][0-9]$/; // HH:MM format
          if (!timePattern.test(timeInput)) {
            errorMessage.text('Tolong masukkan format waktu dengan benar. Sesuaikan menjadi (Jam:Menit), Contoh: 06:00.');
            $('#showToastPlacement').click();
            return;
          }
          const [hours, minutes] = timeInput.split(':').map(Number);
          if (hours < 6 || hours > 22 || (hours === 22 && minutes > 0)) {
            errorMessage.text('Tolong masukkan rentang waktu dengan benar, yaitu antara pukul 06:00 hingga 22:00.');
            $('#showToastPlacement').click();
            return;
          }
        }
        if (url === '') {
          event.preventDefault(); // Prevent form submission
          alert('Tolong isi link url terlebih dahulu!').show();
        }
        $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
        document.body.style.overflow = 'hidden';
        $('#loading-screen').fadeIn('slow');

        $.ajax({
          url: 'http://localhost:3000/screenshots/url',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(payload), // Send data as JSON
          // xhrFields: {
          //   responseType: 'blob' // Ensure binary response
          // },
          success: function(response) {
            console.log(response);
            $('#preview-canvas-manual-title h3').text(`Trafik ${response.nama}`);
            $('#preview-canvas-manual-description h6').text(`Waktu Pengambilan Gambar: ${changeDescFormat(response.timestamp)}`);
            fetchTableData();
            statusChange('sukses');
            $('.loading-text').text('Screenshot berhasil!')
            setTimeout(function() {
              $('#loading-screen').fadeOut('fast');
              document.body.style.overflow = 'auto';
              setTimeout(function() {
                statusChange('draw');
                $('#link').val('');
                $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
              }, 1000);
            }, 2000);
            const fileDir = `http://localhost/disertasi/sinflobis/backend/src/script/screenshots/${response.file}`;
            const imgElement = `
              <div id="magnify">
                <img src="${fileDir}" id="screenshot-trafik" alt="Screenshot Trafik" style="max-width: 100%; height: auto;">
              </div>`;
            $('#preview-canvas-manual').html(imgElement);
            $('#link').empty();
            $('#waktu_manual').empty();
            $('#screenshot-trafik').on('load', function() {
              magnify("screenshot-trafik", 3);
            });
          },
          error: function(error) {
            console.error('Pesan:', error);
            statusChange('failed');
            $('.loading-text').text('Screenshot gagal!')
            setTimeout(function() {
              $('#loading-screen').fadeOut('fast');
              document.body.style.overflow = 'auto';
              setTimeout(function() {
                statusChange('draw');
                $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
              }, 1000);
            }, 2000);
          },
        });
      });

      $("input[name='lokasi[]']").keyup(function() {
        lokasi = $(this).val();
        $.ajax({
            type: "POST",
            url: "./data-lokasi.php",
            data: {
              nama: lokasi
            },
            dataType: "json"
          })
          .done(function(data) {
            $("input[name='lokasi[]']").autocomplete({
              source: data,
              select: function(event, ui) {
                // console.log(ui.item.label, ui.item.value);
                $(this).val(ui.item.label);
                return false; // Prevent the default behavior
              }
            });
          })
          .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error: ", textStatus, errorThrown);
          })
      });

      // Preview Gambar pada Tabel
      $('#tableBody').on('click', '.preview-table', function() {
        id = $(this).data('gambar');

        $.ajax({
          url: "./data-lokasi.php",
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            gambar: id
          }),
          success: function(response) {
            const data = response[0];
            $('#preview-canvas-title h3').text(`Trafik ${data.nama}`);
            $('#preview-canvas-manual-title h3').text(`Trafik ${data.nama}`);
            $('#preview-canvas-description h6').text(`Waktu Pengambilan Gambar: ${changeDescFormat(data.timestamp)}`);
            $('#preview-canvas-manual-description h6').text(`Waktu Pengambilan Gambar: ${changeDescFormat(data.timestamp)}`);
            const fileName = data.url.split('\\').pop();
            const fileDir = `http://localhost/disertasi/sinflobis/backend/src/script/screenshots/${fileName}`;
            const imgElement = `
              <div id="magnify">
                <img src="${fileDir}" id="screenshot-trafik" alt="Screenshot Trafik" style="max-width: 100%; height: auto;">
              </div>`;
            $('#preview-canvas-manual').html(imgElement);
            $('#preview-canvas').html(imgElement);
          },
          error: function(xhr, status, error) {
            console.error("Error fetching data gambar: ", error);
          }
        })
      });

      // zoom image on hover

      function changeDescFormat(timestamp) {
        const date = new Date(timestamp);
        const dayIndex = date.getDay();
        const daysInIndonesian = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const dayName = daysInIndonesian[dayIndex];
        const day = date.getDate().toString().padStart(2, '0');
        const month = date.toLocaleString('id-ID', {
          month: 'long'
        });
        const year = date.getFullYear();
        const time = date.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: false
        });
        const formattedDate = `${dayName}, ${day} ${month} ${year}, Pukul ${time} WIB`;
        return formattedDate
      }

      function fetchTableData() {
        $.ajax({
          url: 'data-lokasi.php',
          method: 'GET',
          data: {
            screenshot: true
          },
          dataType: 'json',
          success: function(response) {
            let tableBody = $('#tableBody');
            tableBody.empty();
            response.forEach((data, index) => {
              const jenisBadge = data.jenis === 'live' ?
                '<span class="badge bg-label-primary">Live</span>' :
                '<span class="badge bg-label-info">Typical</span>';
              const date = new Date(data.timestamp);
              const formattedDate = `${date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false })} WIB, ${date.getDate().toString().padStart(2, '0')}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getFullYear()}`;
              const row = `
                        <tr>
                            <td class='text-center'>${index + 1}</td>
                            <td><strong>${data.nama}</strong></td>
                            <td>${jenisBadge}</td>
                            <td>${data.hari}</td>
                            <td>${data.waktu} WIB</td>\
                            <td>${formattedDate} WIB</td>
                            <td class='text-center'>
                                <a class="btn btn-sm btn-outline-warning me-2 preview-table" 
                                href="#previewTable"
                                data-gambar="${data.timestamp}">
                                Tampilkan
                                <i class="bx bx-image ms-1"></i>
                                </a>
                                <a class="text-danger trash-btn" 
                                href="${index + 1}"
                                data-gambar="${data.timestamp}"
                                data-bs-toggle="modal" 
                                data-bs-target="#hapusGambar">
                                <i class="bx bx-trash me-1"></i>
                                </a>
                            </td>
                        </tr>`;
              tableBody.append(row);
            });
          },
          error: function(xhr, status, error) {
            console.error("Error fetching data screenshot: ", error);
          }
        });
      }
    });
  </script>
</head>

<body>
  <div id="loading-screen" style="display: none;">
    <div class="loading-wrapper">
      <div class="circle-loader">
        <div class="status draw"></div>
      </div>
      <h6 class="loading-text"></h6>
    </div>
  </div>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->

      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="#" class="app-brand-link">
            <span class="app-brand-logo demo"><img src="../img/sinflobis2.jpg" alt="" style="margin: 30px 0;"></span>
            <!-- <span class="app-brand-text demo menu-text fw-bolder ms-2">DIGILIB</span> -->
          </a>

          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">
          <!-- Dashboard -->
          <li class="menu-item">
            <a href="index.php" class="menu-link">
              <i class="menu-icon tf-icons bx bxs-dashboard"></i>
              <div data-i18n="Analytics">Dashboard</div>
            </a>
          </li>

          <!-- Forms & Tables -->
          <li class="menu-header small text-uppercase"><span class="menu-header-text">Metodologi Penelitian</span></li>

          <!-- Tables -->
          <li class="menu-item">
            <a href="scrape.php" class="menu-link">
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/google-maps.png" alt="">
              <div data-i18n="Tables">Scraping Google Maps</div>
            </a>
          </li>
          <li class="menu-item active">
            <a href="screenshoot.php" class="menu-link">
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/traffic-jam.png" alt="">
              <div data-i18n="Tables">Screenshot Trafik</div>
            </a>
          </li>

          <li class="menu-item">
            <a href="topsis.php" class="nav-link disabled">
              <!-- <a href="topsis.php" class="menu-link"> -->
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/topsis.png" alt="">
              <div data-i18n="Tables">Seleksi TOPSIS</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="waspas.php" class="nav-link disabled">
              <!-- <a href="waspas.php" class="menu-link"> -->
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/waspas.png" alt="">
              <div data-i18n="Tables">Seleksi WASPAS</div>
            </a>
          </li>
        </ul>
      </aside>
      <!-- / Menu -->

      <!-- Modal-->
      <div class="modal fade" id="akun" aria-labelledby="akun" data-bs-backdrop="static" tabindex="-1" style="display: none" aria-hidden="true">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header d-flex justify-content-center" style="text-align: center;">
              <ul class="nav nav-pills flex-column flex-md-row">
                <li class="nav-item">
                  <button class="nav-link active" data-bs-target="#akun" data-bs-toggle="modal"><i class="bx bx-user me-1"></i> Akun</button>
                </li>
                <!-- <li class="nav-item">
                  <button class="nav-link" data-bs-target="#notifikasi" data-bs-toggle="modal"><i class="bx bx-bell me-1"></i> Notifikasi</button>
                </li> -->
              </ul>
            </div>
            <form id="form_profil" enctype="multipart/form-data" method="POST">
              <div class="modal-body">
                <div class="card mb-3">
                  <h5 class="card-header" style="text-align: center;">Profil Anda</h5>
                  <!-- Account -->
                  <div class="card-body">
                    <!-- <input type="hidden" class="form-control" name="p" value="profil" required /> -->
                    <div class="row">
                      <div class="d-flex col-lg-6 mb-3 justify-content-center align-items-start align-items-sm-center gap-4">
                        <img src="../assets/img/avatars/<?php echo $fotoProfil ?>" alt="user-avatar" class="d-block rounded" height="120" width="120" id="uploadedAvatar" />
                        <div class="button-wrapper">
                          <label for="upload" class="btn btn-warning me-2 mb-4" tabindex="0">
                            <span class="d-none d-sm-block" style="text-align: start !important;">Upload Foto</span>
                            <i class="bx bx-upload d-block d-sm-none"></i>
                            <input type="file" id="upload" name="foto" class="account-file-input" value="" hidden accept="image/png, image/jpeg" />
                          </label>

                          <p class="text-muted mb-0">Format JPG atau PNG.<br>Ukuran Maksimum 5 Mb</p>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <label for="namaAkun" class="form-label">Ubah Nama Lengkap</label>
                        <input class="form-control mb-3" type="text" id="namaAkun" name="namaAkun" value="<?php echo $namaAkun ?>" placeholder="Masukkan Nama Anda" autofocus />
                        <div class="mb-3 form-password-toggle">
                          <label class="form-label" for="pswdAkun">Ubah Password</label>
                          <div class="input-group input-group-merge">
                            <span class="input-group-text text-muted">Min. 8 Karakter</span>
                            <input type="password" minlength="8" id="pswdAkun" name="pswdAkun" value="<?php echo $pswdAkun ?>" class="form-control"
                              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" autofocus />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                  <button type="submit" name="simpanAkun" value="simpanAkun" class="btn btn-warning me-2">
                    Simpan
                  </button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Kembali
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- Modal DELETE -->
      <div class="modal fade" id="hapusGambar" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header d-flex justify-content-center">
              <h5 class="modal-title delete" id="modalCenterTitle"></h5>
            </div>
            <div class="modal-body delete" style="text-align: center;"></div>
            <div class="modal-footer delete d-flex justify-content-center mb-3"></div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="logout" aria-labelledby="logout" data-bs-backdrop="static" tabindex="-1" style="display: none" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
          <div class="modal-content">
            <div class='card'>
              <div class='card-body'>
                <div class='d-flex flex-column align-items-center gap-3'>
                  <h4 class='mb-3'>Yakin ingin logout?</h4>
                  <div class="d-flex gap-3">
                    <a href="../logout.php"><button class='btn btn-warning'>Ya</button></a>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- TOAST -->
      <div id="alert-waktu" class="bs-toast toast toast-placement-ex m-2 fade" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" style="z-index: 9999999;">
        <div class="toast-header">
          <i class='bx bx-error me-2'></i>
          <div class="me-auto fw-medium">Screenshot Gagal!</div>
          <small>Pesan Error</small>
          <!-- <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button> -->
        </div>
        <div id="toast-body" class="toast-body"></div>
      </div>
      <input id="selectTypeOpt" type="hidden" value="bg-danger" />
      <input id="selectPlacement" type="hidden" value="top-0 end-0" />

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
        <div class="sticky">
          <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center" id="datatable_filter">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <input type="text" id="search" class="form-control border-0 shadow-none" placeholder="Cari Data Tabel Gambar" aria-label="Cari Data Tabel Gambar" aria-controls="datatable" style="background-color: transparent;" />
                </div>
              </div>
              <!-- /Search -->

              <ul class=" navbar-nav flex-row align-items-center ms-auto">

                <!-- User -->
                <button id="showToastPlacement" data-bs-toggle="toast" data-bs-target="#alert-waktu" class="btn" style="visibility:hidden;">s</button>
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="d-flex gap-3">
                      <div class="avatar avatar-online">
                        <img src="../assets/img/avatars/<?php echo $fotoProfil ?>" alt class="w-px-40 h-auto rounded-circle" />
                      </div>
                      <div class="d-flex flex-column">
                        <span class="fw-semibold d-block"><?php echo ucwords($namaAkun) ?></span>
                        <small class="text-muted">Admin</small>
                      </div>
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#akun">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Pengaturan</span>
                      </button>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="" data-bs-toggle="modal" data-bs-target="#logout">
                        <i class="bx bx-log-out-circle me-2"></i>
                        <span class="align-middle">Keluar</span>
                      </a>
                    </li>
                    <!--/ User -->
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
        </div>
        <!-- / Navbar -->

        <!-- edit data -->


        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->

          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4 text-center"><span class="text-muted fw-light">Form </span> Screenshot Trafik</h4>
            <div class="nav-align-top mb-6">
              <ul class="nav nav-pills mb-4" role="tablist" style="margin: 0 auto;">
                <li class="nav-item mb-3 mb-sm-0 me-3">
                  <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-messages" aria-controls="navs-pills-justified-messages" aria-selected="false"><span class="d-none d-sm-block"></span><span class="d-flex align-items-center justify-content-center gap-2"><i class="tf-icons bx bx-map-alt bx-sm me-1_5 align-text-bottom"></i> Berdasarkan Link Google Maps</span></button>
                </li>
                <li class="nav-item">
                  <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-profile" aria-controls="navs-pills-justified-profile" aria-selected="true"><span class="d-none d-sm-block"></span><span class="d-flex align-items-center justify-content-center gap-2"><i class="tf-icons bx bx-map bx-sm me-1_5 align-text-bottom"></i> Berdasarkan Data Lokasi</span></button>
                </li>
              </ul>
              <div class="tab-content" id="previewTable" style="scroll-margin-top: -5.5rem;">
                <div class="tab-pane fade" id="navs-pills-justified-profile" role="tabpanel">
                  <div class="card-body">
                    <form id="ss-db">
                      <div class="row">
                        <div class="col-lg-1"></div>
                        <label class="col-lg-1 col-form-label text-center" for="query">Nama</label>
                        <div class="col-lg-4 mb-3 input-group input-group-merge" style="box-shadow: none;">
                          <span id="basic-icon-default-fullname1" class="input-group-text"><i class='bx bxs-map'></i></span>
                          <input class="form-control" type="text" id="query" name="lokasi[]" placeholder="Masukkan nama lokasi" required />
                        </div>
                        <label class="col-lg-1 col-form-label text-center" for="trafik">Trafik</label>
                        <div class="col-lg-4 mb-3 input-group input-group-merge" style="box-shadow: none;">
                          <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bxs-car'></i></span>
                          <select class="form-select" id="trafik" name="trafik">
                            <option value="0" selected>Langsung (Live)</option>
                            <option value="1">Biasanya (Typical)</option>
                          </select>
                        </div>
                        <div class="col-lg-1"></div>
                      </div>
                      <div id="additional-row-placeholder"></div>
                      <div class="row mt-3 pb-2">
                        <div class="col-sm-12 d-flex justify-content-center">
                          <button type="submit" name="tombol" value="<?php echo $tombol_val ?>" class="btn btn-warning d-flex align-items-center gap-2"><i class='bx bx-screenshot'></i> Screenshot</button>
                        </div>
                      </div>
                    </form>
                    <div class="divider divider-warning" style="padding-right: 1rem;">
                      <div class="divider-text mx-auto">Preview <i class='bx bx-image ms-2'></i></div>
                    </div>
                    <div class="row text-center" id="preview-canvas-title">
                      <h3><b></b></h3>
                    </div>
                    <div class="row mb-3" id="preview-canvas"></div>
                    <div class="row text-center" id="preview-canvas-description">
                      <h6 class="mb-0"></h6>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade show active" id="navs-pills-justified-messages" role="tabpanel">
                  <div class="card-body">
                    <form id="ss-link">
                      <div class="row">
                        <div class="col-lg-1"></div>
                        <label class="col-lg-1 col-form-label text-center" for="link">Link URL</label>
                        <div class="col-lg-4 mb-3 input-group input-group-merge" style="box-shadow: none;">
                          <span id="basic-icon-default-fullname1" class="input-group-text"><i class='bx bx-link'></i></span>
                          <input type="text" id="link" name="link" class="form-control" placeholder="Contoh: https://google.com/maps/@..." required />
                        </div>
                        <label class="col-lg-1 col-form-label text-center" for="trafik_manual">Trafik</label>
                        <div class="col-lg-4 mb-3 input-group input-group-merge" style="box-shadow: none;">
                          <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bxs-car'></i></span>
                          <select class="form-select" id="trafik_manual" name="trafik_manual">
                            <option value="0" selected>Langsung (Live)</option>
                            <option value="1">Biasanya (Typical)</option>
                          </select>
                        </div>
                        <div class="col-lg-1"></div>
                      </div>
                      <div id="additional-row-placeholder_manual"></div>
                      <div class="row mt-3 pb-2">
                        <div class="d-flex justify-content-center">
                          <button type="submit" name="tombol" value="<?php echo $tombol_val ?>" class="btn btn-warning d-flex align-items-center gap-2"><i class='bx bx-screenshot'></i> Screenshot</button>
                        </div>
                      </div>
                    </form>
                    <div class="divider divider-warning" style="padding-right: 1rem;">
                      <div class="divider-text mx-auto">Preview <i class='bx bx-image ms-2'></i></div>
                    </div>
                    <div class="row text-center" id="preview-canvas-manual-title">
                      <h3><b></b></h3>
                    </div>
                    <div class="row mb-3" id="preview-canvas-manual"></div>
                    <div class="row text-center" id="preview-canvas-manual-description">
                      <h6 class="mb-0"></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="container-xxl flex-grow-1 container-p-y">
              <hr class="my-4" />
              <h4 class="fw-bold py-3 mb-4 text-center"><span class="text-muted fw-light">Tabel Data /</span> Gambar Screenshot</h4>

              <!-- Hoverable Table rows -->
              <div class="card overflow-hidden" style="max-height:620px; padding:10px 20px;">
                <div class="table-responsive">
                  <table class="table table-hover" id="datatable">
                    <thead>
                      <tr>
                        <th class="dt-head-center sorting" scope="col">No.</th>
                        <th class="dt-head-center sorting" scope="col">Nama</th>
                        <th class="dt-head-center sorting" scope="col">Jenis</th>
                        <th class="dt-head-center sorting" scope="col">Hari</th>
                        <th class="dt-head-center sorting" scope="col">Waktu</th>
                        <th class="dt-head-center">Diambil Pada</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0" id="tableBody">
                      <?php
                      // ambil data scraping
                      $screenshot = mysqli_query($koneksi, "SELECT nama, jenis, hari, waktu, url, timestamp FROM pictures ORDER BY timestamp DESC");
                      $no = 1;
                      while ($data = mysqli_fetch_row($screenshot)) {
                        $nama = $data[0];
                        $jenis = ucfirst($data[1]);
                        $hari = $data[2];
                        $waktu = $data[3] . ' WIB';
                        $url = $data[4];
                        $timestamp = $data[5];
                        $dateTime = new DateTime($timestamp);
                        $formattedDate = $dateTime->format('H:i:s') . ' WIB, ' . $dateTime->format('d-m-Y');


                        if ($jenis == 'Live') {
                          $badge = '<span class="badge bg-label-primary">' . $jenis . '</span>';
                        } else {
                          $badge = '<span class="badge bg-label-info">' . $jenis . '</span>';
                        }
                        echo "
                        <tr>
                          <td class='text-center'>$no</td>
                          <td><strong>$nama</strong></td>
                          <td>$badge</td>
                          <td>$hari</td>
                          <td>$waktu</td>
                          <td>$formattedDate</td>
                          <td class='text-center'>
                            <a class=\"btn btn-sm btn-outline-warning me-2 preview-table\" 
                            href=\"#previewTable\"
                            data-gambar=\"$timestamp\">
                            Tampilkan
                            <i class=\"bx bx-image ms-1\"></i>
                            </a>
                            <a class=\"text-danger trash-btn\" 
                            href=\"$no\"
                            data-gambar=\"$timestamp\"
                            data-bs-toggle=modal 
                            data-bs-target=#hapusGambar>
                            <i class=\"bx bx-trash me-1\"></i>
                            </a>
                          </td>
                        </tr>";
                        $no++;
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <!--/ Hoverable Table rows -->
            </div>

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->


    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="../assets/js/ui-toasts.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>