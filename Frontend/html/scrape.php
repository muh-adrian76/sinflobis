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
          position: absolute;
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
  } elseif (!empty($file_foto) && empty($pswdAkun)) {
    move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
    mysqli_query($koneksi, "UPDATE login SET nama='$namaAkun', foto='$file_foto' WHERE username='$user' OR telepon='$user'");
    echo '<div class="alert alert-success" style="
          max-width: 50%;
          z-index: 999999999999;
          margin: auto;
          position: absolute;
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
  } elseif (empty($file_foto) && !empty($pswdAkun)) {
    $pass_hash = password_hash($pswdAkun, PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE login SET nama='$namaAkun', password='$pass_hash' WHERE username='$user' OR telepon='$user'");
    echo '<div class="alert alert-success" style="
          max-width: 50%;
          z-index: 999999999999;
          margin: auto;
          position: absolute;
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
  } elseif (!empty($namaAkun)) {
    mysqli_query($koneksi, "UPDATE login SET nama='$namaAkun' WHERE username='$user' OR telepon='$user'");
    echo '<div class="alert alert-success" style="
            max-width: 50%;
            z-index: 999999999999;
            margin: auto;
            position: absolute;
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

if (isset($_POST["hapusLokasi"])) {
  $lokasi = $_POST['lokasi'];
  $q = mysqli_query($koneksi, "SELECT id from locations WHERE name='$lokasi'");
  $d = mysqli_fetch_row($q);
  $id = $d[0];
  mysqli_query($koneksi, "DELETE FROM locations WHERE name='$lokasi'");
  mysqli_query($koneksi, "DELETE FROM popular_times WHERE location_id='$id'");
  mysqli_query($koneksi, "ALTER TABLE popular_times DROP id");
  mysqli_query($koneksi, "ALTER TABLE popular_times ADD id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
  echo '<div class="alert alert-success" style="
            max-width: 50%;
            z-index: 999999999999;
            margin: auto;
            position: absolute;
            top: 10px;
            left: 50%;
            text-align:center;
            transform : translateX(-50%);
            "><strong>Berhasil</strong> menghapus Lokasi!
        </div>';
  echo "<script>
          setTimeout(function() {
            window.location.replace('scrape.php');
          }, 3000);
        </script>";
  $lokasi = "";
}

?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>SINFLOBIS | Scraping Google Maps</title>

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
  <!-- edit data -->
  <script>
    $(document).ready(function() {
      $(".alert:not(.alert-success)").fadeOut(5000);

      table = $('#datatable').DataTable({
        "dom": '<"top"f>rt<"bottom"ilp><"refresh-scrape">',
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
      $('<button id="refresh-scrape" style="margin: 1.05rem" class="btn btn-warning"><i class="bx bx-reset me-1"></i> Perbarui Data Scraping</button>').appendTo('.refresh-scrape');
      $('.refresh-scrape').addClass('d-flex justify-content-center');

      $('#search').on('keyup', function() {
        table.search(this.value).draw();
      });
      // modal delete
      $("tbody a:nth-child(1)").click(function() {
        $("#formHapus").on('submit', function(event) {
          event.preventDefault();
        });
        lokasi = $(this).attr('href');
        $(".modal-title.delete").text("Konfirmasi Hapus");
        $(".modal-body.delete").html("Apakah anda yakin ingin menghapus<br><strong>" + lokasi + "</strong> ?");

        form1 = "<form method=post id=formHapus><input type=hidden name=lokasi value='" + lokasi + "'>";
        form2 = "<button type=submit name=hapusLokasi class=\"btn btn-danger m-2\" data-bs-dismiss=modal>Ya</button>";
        form3 = "<button type=button class=\"btn btn-warning\" data-bs-dismiss=modal>Tidak</button>";
        form4 = "</form>";
        form = form1 + form2 + form3 + form4;
        $(".modal-footer.delete").empty();
        $(".modal-footer.delete").append(form);
      });

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

      // scrape modal
      const rescrapeModal = new bootstrap.Modal(document.getElementById('re_scrape'));
      $('#refresh-scrape').on('click', function() {
        rescrapeModal.show();
      });

      const scrapeResultButton = $('<button>', {
        text: 'Lihat Hasil',
        class: 'btn btn-warning mt-3',
        click: function() {
          $('#loading-screen').fadeOut('fast');
          rescrapeModal.hide();
          const myModal = new bootstrap.Modal(document.getElementById('scrape-result'));
          myModal.show();
        }
      });

      $("#close-scrape-result").click(function() {
        window.location.replace('scrape.php');
      });

      $('#scrape-form').on('submit', function(event) {
        event.preventDefault(); // Prevent form from refreshing the page
        // tambah loading screen
        statusChange('draw');
        $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
        document.body.style.overflow = 'hidden';
        $('#loading-screen').fadeIn('slow');
        const query = $('#query').val();
        const group = $('#group').val();
        const checkbox = $('#defaultCheck1').prop('checked');

        $.ajax({
          url: 'http://localhost:3000/scrapes',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            query,
            group,
            checkbox
          }), // Send data as JSON
          success: function(response) {
            console.log(response);
            statusChange('sukses');
            $('.loading-text').text('Scraping telah selesai!')
            $('.loading-wrapper').append(scrapeResultButton);

            $('#scrape-result-table').empty();
            const berhasilList = response.data.filter(item => item.berhasil).map(item => item.berhasil);
            const gagalList = response.data.filter(item => item.gagal).map(item => item.gagal);
            const maxRows = Math.max(berhasilList.length, gagalList.length);
            for (let i = 0; i < maxRows; i++) {
              const row = $('<tr>');
              row.append($("<td class='text-center'>").text(i + 1)); // First column: index + 1
              if (i < berhasilList.length) {
                row.append($('<td>').html(berhasilList[i])); // Second column: berhasil
              } else {
                row.append($('<td>').text('')); // Second column: empty if no lebih
              }
              if (i < gagalList.length) {
                row.append($('<td>').html(gagalList[i])); // Third column: gagal
              } else {
                row.append($('<td>').text('')); // Third column: empty if no gagal
              }
              $('#scrape-result-table').append(row);
            }
          },
          error: function(error) {
            console.error('Pesan:', error);
            statusChange('failed');
            $('.loading-text').text('Gagal melakukan scraping!')
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

      $('#rescrape-form').on('submit', function(event) {
        event.preventDefault();
        const query = $('#nama_lokasi').val();
        const group = $('#nama_grup').val();
        if (query === '' && group === '') {
          event.preventDefault(); // Prevent form submission
          alert('Tolong isi salah satu form.').show();
        }
        statusChange('draw');
        $('.loading-text').text('Tolong tunggu hingga selesai, jangan me-refresh halaman...')
        document.body.style.overflow = 'hidden';
        $('#loading-screen').fadeIn('slow');

        $.ajax({
          url: 'http://localhost:3000/rescrapes',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            query,
            group
          }), // Send data as JSON
          success: function(response) {
            console.log(response);
            statusChange('sukses');
            $('.loading-text').text('Scraping telah selesai!')
            $('.loading-wrapper').append(scrapeResultButton);

            $('#scrape-result-table').empty();
            const berhasilList = response.data.filter(item => item.berhasil).map(item => item.berhasil);
            const gagalList = response.data.filter(item => item.gagal).map(item => item.gagal);
            const maxRows = Math.max(berhasilList.length, gagalList.length);
            for (let i = 0; i < maxRows; i++) {
              const row = $('<tr>');
              row.append($("<td class='text-center'>").text(i + 1)); // First column: index + 1
              if (i < berhasilList.length) {
                row.append($('<td>').html(berhasilList[i])); // Second column: berhasil
              } else {
                row.append($('<td>').text('')); // Second column: empty if no lebih
              }
              if (i < gagalList.length) {
                row.append($('<td>').html(gagalList[i])); // Third column: gagal
              } else {
                row.append($('<td>').text('')); // Third column: empty if no gagal
              }
              $('#scrape-result-table').append(row);
            }
          },
          error: function(error) {
            console.error('Pesan:', error);
            statusChange('failed');
            $('.loading-text').text('Gagal melakukan scraping!')
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

      $("input[name='group[]']").keyup(function() {
        nama = $(this).val();
        $.ajax({
            type: "POST",
            url: "./data-lokasi.php",
            data: {
              grup: nama
            },
            dataType: "json"
          })
          .done(function(data) {
            $("input[name='group[]']").autocomplete({
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

      $("input[name='nama_grup[]']").keyup(function() {
        nama = $(this).val();
        $.ajax({
            type: "POST",
            url: "./data-lokasi.php",
            data: {
              grup: nama
            },
            dataType: "json"
          })
          .done(function(data) {
            $("input[name='nama_grup[]']").autocomplete({
              source: data,
              select: function(event, ui) {
                // console.log(ui.item.label, ui.item.value);
                $(this).val(ui.item.label);
                return false; // Prevent the default behavior
              },
              create: function() {
                // Set the z-index of the autocomplete menu
                $(this).data("ui-autocomplete").menu.element.css("z-index", 1091); // Adjust the z-index as needed
              }
            });
          })
          .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error: ", textStatus, errorThrown);
          })
      });

      $("input[name='nama_lokasi[]']").keyup(function() {
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
            $("input[name='nama_lokasi[]']").autocomplete({
              source: data,
              select: function(event, ui) {
                $(this).val(ui.item.label);
                return false; // Prevent the default behavior
              },
              create: function() {
                // Set the z-index of the autocomplete menu
                $(this).data("ui-autocomplete").menu.element.css("z-index", 1091); // Adjust the z-index as needed
              }
            });
          })
          .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error: ", textStatus, errorThrown);
          })
      });
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
          <li class="menu-item active">
            <a href="scrape.php" class="menu-link">
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/google-maps.png" alt="">
              <div data-i18n="Tables">Scraping Google Maps</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="screenshoot.php" class="menu-link">
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/traffic-jam.png" alt="">
              <div data-i18n="Tables">Screenshot Trafik</div>
            </a>
          </li>

          <li class="menu-item">
            <a href="mcdm.php" class="menu-link">
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/topsis.png" alt="">
              <div data-i18n="Tables">Seleksi Lokasi Terbaik</div>
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
                  <input type="text" id="search" class="form-control border-0 shadow-none" placeholder="Cari Data Tabel Lokasi" aria-label="Cari Data Tabel Lokasi" aria-controls="datatable" />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">

                <!-- User -->
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


        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y" style="text-align: center !important;">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Form</span> Scraping Lokasi</h4>

            <!-- Basic Layout & Basic with Icons -->
            <div class="row">
              <!-- Basic Layout -->
              <div class="col-xxl">
                <div class="card">
                  <div class="card-header d-flex align-items-center justify-content-center">
                    <!-- <h5 class="mb-0">Tambah Data Lokasi</h5> -->
                  </div>
                  <div class="card-body">
                    <form id="scrape-form">
                      <div class="row mb-3">
                        <label class="col-lg-2 col-form-label" for="query">Nama</label>
                        <div class="col-lg-8 mb-3 input-group input-group-merge textarea-input" style="box-shadow: none;">
                          <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bx-map'></i></span>
                          <textarea class="form-control" type="text" id="query" name="query" rows="1" placeholder="Masukkan nama lokasi" required></textarea>
                        </div>
                        <div class="col-lg-2">
                          <button type="button" class="btn btn-outline-warning mx-auto wow fadeIn" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasBackdrop" aria-controls="offcanvasBackdrop">Panduan
                            <i class='bx bx-help-circle'></i></button>
                        </div>
                      </div>
                      <div class="row mb-3">
                        <label class="col-lg-2 col-form-label" for="group">Grup<!-- (Opsional)--></label>
                        <div class="col-lg-8 mb-3 input-group input-group-merge textarea-input" style="box-shadow: none;">
                          <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bxs-group'></i></span>
                          <input class="form-control" type="text" id="group" name="group[]" placeholder="Masukkan nama grup" required />
                        </div>
                        <div class="col-lg-2">
                          <button type="submit" name="tombol" class="btn btn-warning mx-auto"><i class='bx bx-map-pin me-2'></i> Scrape</button>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-lg-2"></div>
                        <div class="col-lg-10 d-flex gap-3 mb-3 row-checkbox">
                          <input class="form-check-input" type="checkbox" value="" id="defaultCheck1" />
                          <label class="form-check-label" for="defaultCheck1">Simpan data kosong (<i>missing values</i>)</label>
                        </div>
                      </div>
                      <!-- <div class="row">
                        <div class="col-lg-12 d-flex justify-content-center">
                          <button type="submit" name="tombol" value="<?php echo $tombol_val ?>" class="btn btn-warning">Scrape</button>
                        </div>
                      </div> -->
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="container-xxl flex-grow-1 container-p-y">

            <hr class="my-4" />
            <h4 class="fw-bold py-3 mb-4 text-center"><span class="text-muted fw-light">Tabel</span> Lokasi</h4>

            <!-- Hoverable Table rows -->
            <div class="card overflow-hidden" style="max-height:620px; padding:10px 20px;">
              <div class="table-responsive">
                <table class="table table-hover" id="datatable">
                  <thead>
                    <tr>
                      <th class="dt-head-center sorting" scope="col">No.</th>
                      <th class="dt-head-center sorting" scope="col">Nama</th>
                      <th class="dt-head-center sorting" scope="col">Grup</th>
                      <th class="dt-head-center sorting" scope="col">Latitude</th>
                      <th class="dt-head-center sorting" scope="col">Longitude</th>
                      <th class="dt-head-center" scope="col">Diambil Pada</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody class="table-border-bottom-0">
                    <?php
                    // ambil data scraping
                    $scraping = mysqli_query($koneksi, "SELECT 
                                                            l.name AS location_name, 
                                                            lg.name AS group_name, 
                                                            l.latitude, 
                                                            l.longitude,
                                                            l.last_updated
                                                        FROM 
                                                            locations l 
                                                        LEFT JOIN 
                                                            location_groups lg ON l.grup = lg.id 
                                                        ORDER BY 
                                                            l.last_updated DESC");
                    $no = 1;
                    while ($data_scrap = mysqli_fetch_row($scraping)) {
                      $namaLokasi = $data_scrap[0];
                      $grup = $data_scrap[1];
                      $latitude = $data_scrap[2];
                      $longitude = $data_scrap[3];
                      $last_updated = $data_scrap[4];
                      $dateTime = new DateTime($last_updated);
                      $formattedDate = $dateTime->format('H:i:s') . ' WIB, ' . $dateTime->format('d-m-Y');

                      echo "
                          <tr>
                            <td class='text-center'>$no</td>
                            <td><strong>$namaLokasi</strong></td>
                            <td>$grup</td>
                            <td>$latitude</td>
                            <td>$longitude</td>
                            <td>$formattedDate</td>
                            <td class='text-center'>
                                  <a class=\"text-danger\" 
                                    href=\"$namaLokasi\"
                                    data-bs-toggle=modal 
                                    data-bs-target=#hapusLokasi>
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

            <!-- Off Canvas -->
            <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBackdrop"
              aria-labelledby="offcanvasBackdropLabel" style="visibility: hidden;" aria-hidden="true">
              <div class="offcanvas-header">
                <div></div>
                <h3 id="offcanvasBackdropLabel" class="offcanvas-title">Panduan Scraping Data Jam Sibuk</h3>

                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                  aria-label="Close"></button>
              </div>
              <div class="row">
                <div class="divider divider-warning">
                  <div class="divider-text"><i class='bx bx-help-circle'></i></div>
                </div>
              </div>
              <div class="offcanvas-body mb-auto mx-0 flex-grow-0">
                <div class="accordion" id="accordionExample">
                  <div id="accordionIcon" class="accordion mt-3 accordion-without-arrow" style="pointer-events: none;">
                    <div class="row">
                      <div class="col-lg-4">
                        <div class="accordion-item card mb-3">
                          <h2 class="accordion-header text-body d-flex justify-content-between"
                            id="accordionIconOne">
                            <button type="button" class="accordion-button collapsed"
                              data-bs-toggle="collapse" data-bs-target="#accordionIcon-1"
                              aria-controls="accordionIcon-1" aria-expanded="false">
                              1. Nama yang digunakan
                            </button>
                          </h2>
                          <div id="accordionIcon-1" class="accordion-collapse collapsed"
                            data-bs-parent="#accordionIcon">
                            <div class="accordion-body">
                              <img src="../img/place-details.PNG" alt="">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="accordion-item card mb-3">
                          <h2 class="accordion-header text-body d-flex justify-content-between"
                            id="accordionIconTwo">
                            <button type="button" class="accordion-button collapsed"
                              data-bs-toggle="collapse" data-bs-target="#accordionIcon-2"
                              aria-controls="accordionIcon-2" aria-expanded="false">
                              2. Lokasi harus memiliki data jam favorit
                            </button>
                          </h2>
                          <div id="accordionIcon-2" class="accordion-collapse collapsed"
                            data-bs-parent="#accordionIcon">
                            <div class="accordion-body">
                              <img src="../img/busy-hour.PNG" alt="">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="accordion-item card">
                          <h2 class="accordion-header text-body d-flex justify-content-between"
                            id="accordionIconThree">
                            <button type="button" class="accordion-button collapsed"
                              data-bs-toggle="collapse" data-bs-target="#accordionIcon-3"
                              aria-expanded="false" aria-controls="accordionIcon-3">
                              3. Gunakan salah satu pemisah jika data lebih dari 1
                            </button>
                          </h2>
                          <div id="accordionIcon-3" class="accordion-collapse collapsed"
                            data-bs-parent="#accordionIcon">
                            <div class="accordion-body">
                              <img src="../img/delimiter.PNG" alt="">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal -->
            <div class="col-lg-12 col-md-6">
              <div class="mt-3">
                <div class='modal fade' id='scrape-result' aria-labelledby='scrape-result' data-bs-backdrop="static" tabindex='-1' style='display: none' aria-hidden='true'>
                  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button
                          class="btn-close"
                          id="close-scrape-result"></button>
                      </div>
                      <h4 class="modal-title text-center" id="modalCenterTitle" style="margin-top: -10px;"><strong>HASIL SCRAPING</strong></h4>
                      <div class="divider divider-warning">
                        <div class="divider-text"><img class="me-2" style="width: 40px;" src="../assets/img/icons/unicons/google-maps.png" alt=""></div>
                      </div>
                      <div class="modal-body">
                        <div class="row g-2">
                          <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="datatable">
                              <thead>
                                <tr>
                                  <th class="dt-head-center"><strong>Nomor</strong></th>
                                  <th class="dt-head-center sorting" scope="col"><strong>Berhasil</strong></th>
                                  <th class="dt-head-center sorting" scope="col"><strong>Gagal</strong></th>
                                </tr>
                              </thead>
                              <tbody class="table-border-bottom-0" id="scrape-result-table"></tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Modal DELETE -->
                <div class="modal fade" id="hapusLokasi" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
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
                <div class='modal fade' id='re_scrape' aria-labelledby='re_scrape' tabindex='-1' style='display: none' aria-hidden='true'>
                  <div class='modal-dialog modal-dialog-centered'>
                    <div class='modal-content'>
                      <!-- Register -->
                      <div class='card'>
                        <div class='card-body'>
                          <div class='d-flex flex-column align-items-center'>
                            <div class='d-flex flex-column align-items-center'>
                              <h4 class='mb-2'>Perbarui Data Lokasi <i class='bx bx-refresh ps-2' style="font-size: 2rem;"></i></h4>
                              <p>Tolong pilih salah satu metode pembaruan dibawah!</p>
                            </div>
                            <form id='rescrape-form' style="width: 70%;">
                              <div class='mb-3'>
                                <div class="form-floating">
                                  <input
                                    type="text"
                                    class="form-control mb-3"
                                    id="nama_grup"
                                    name="nama_grup[]"
                                    placeholder="Masukkan Nama Grup"
                                    aria-describedby="floatingInputHelp" />
                                  <label for="nama_grup[]">Lokasi (Grup)</label>
                                </div>
                                <div class="form-floating">
                                  <input
                                    type="text"
                                    class="form-control mb-3"
                                    id="nama_lokasi"
                                    name="nama_lokasi[]"
                                    placeholder="Masukkan Nama Lokasi"
                                    aria-describedby="floatingInputHelp" />
                                  <label for="nama_lokasi[]">Lokasi (Tunggal)</label>
                                </div>
                              </div>
                              <button class='btn btn-warning d-grid w-100' type='submit'>Scrape Ulang</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
  <!-- Page JS -->
  <script src="../assets/js/pages-account-settings-account.js"></script>

  <script src="../assets/vendor/js/menu.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->

  <!-- Main JS -->
  <script src="../assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="../assets/js/dashboards-analytics.js"></script>

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>