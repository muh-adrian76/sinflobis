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
                  window.location.replace('waspas.php');
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
                  window.location.replace('waspas.php');
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
                  window.location.replace('waspas.php');
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

if (isset($_POST["hapusKriteria"])) {
    $id = $_POST['kriteria'];
    mysqli_query($koneksi, "DELETE FROM criterias WHERE id='$id'");
    mysqli_query($koneksi, "ALTER TABLE criterias DROP id");
    mysqli_query($koneksi, "ALTER TABLE criterias ADD id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    echo '<div class="alert alert-success" style="
            max-width: 50%;
            z-index: 999999999999;
            margin: auto;
            position: fixed;
            top: 10px;
            left: 50%;
            text-align:center;
            transform : translateX(-50%);
            "><strong>Berhasil</strong> menghapus Kriteria!
        </div>';
    echo "<script>
            setTimeout(function() {
              window.location.replace('mcdm.php');
            }, 3000);
          </script>";
    $id = "";
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>SINFLOBIS | Seleksi Lokasi</title>

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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../assets/js/config.js"></script>
    <!-- edit data -->
    <script>
        $(document).ready(function() {

            $(".alert:not(.alert-success)").fadeOut(5000);
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
                        '<option value="5" selected>5</option>' + // Set 5 as the default
                        '<option value="10">10</option>' +
                        '</select> baris data'
                },
                "pageLength": 5,
                columnDefs: [{
                        targets: [2, 3, 4, 5],
                        orderable: false
                    } // Disable ordering for the first column (index 0)
                ]
            });
            $('#search').on('keyup', function() {
                table.search(this.value).draw();
            });
            // modal delete
            $("#tableBody").on("click", ".trash-btn", function() {
                const id = $(this).data('kriteria');
                const kriteria = $(this).attr('href');
                // console.log(`Kriteria: ${kriteria}, ID: ${id}`);
                $(".modal-title.delete").text("Konfirmasi Hapus");
                $(".modal-body.delete").html("Apakah anda yakin ingin menghapus kriteria <b>" + kriteria + "</b> ?");

                const form = `
                    <form method='post' id='formHapus'>
                        <input type='hidden' name='kriteria' value='${id}'>
                        <button type='submit' name='hapusKriteria' class='btn btn-danger m-2' data-bs-dismiss='modal'>Ya</button>
                        <button type='button' class='btn btn-warning' data-bs-dismiss='modal'>Tidak</button>
                    </form>`;
                $(".modal-footer.delete").empty().append(form);
            });

            // tambah alternatif
            let alternatif = 1;
            $(document).on('click', "button[name='tambahAlternatif']", function() {
                alternatif += 1;
                $.ajax({
                    type: "GET",
                    url: "./data-lokasi.php",
                    data: {
                        kriteria: true
                    },
                    contentType: 'application/json',
                    success: function(response) {
                        const row = `
                            <div class="input-group alternatif_${alternatif}">
                                <div class="form-floating">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="alternatif_${alternatif}"
                                        name="lokasi[]"
                                        placeholder="Masukkan Lokasi"
                                        aria-describedby="floatingInputHelp" 
                                        required />
                                    <label for="lokasi[]"><b>Alternatif ${alternatif}</b></label>
                                </div>
                            </div>`;
                        $("#row-alternatives").append(row);
                        response.forEach((data, index) => {
                            const column = `<input type='text' id='A${alternatif}_C${index + 1}' placeholder='A${alternatif}-C${data.id}' class='form-control' required />`;
                            $(`.alternatif_${alternatif}`).append(column);
                        });
                    },
                    error: function(error) {
                        console.error('Pesan:', error);
                    },
                });
            });

            // input form (single)
            $(document).on("keyup", "input[name='lokasi[]']", function() {
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
                                $(this).val(ui.item.label); // Set the input value to the label
                                return false; // Prevent the default behavior
                            }
                        });
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX error: ", textStatus, errorThrown);
                    })
            });
            $("input[name='nama[]']").keyup(function() {
                nama = $(this).val();
                $.ajax({
                        method: "POST",
                        url: "./data-lokasi.php",
                        data: {
                            kriteria: nama
                        },
                        dataType: "json"
                    })
                    .done(function(data) { //kalau sukses maka data akan ditampilkan
                        //panjang = data.length
                        $("input[name='nama[]']").autocomplete({
                            source: data,
                            select: function(event, ui) {
                                $(this).val(ui.item.label);
                                return false;
                            }
                        });
                    })
                    .fail(function(msg) {
                        console.log("error : " + msg)
                    })
            });
            // form kriteria
            $("#kriteria").on("submit", function(e) {
                e.preventDefault(); // agar tidak merefresh halaman
                const nama = $("#nama").val();
                const sifat = $("#sifat").val();
                const kategori = $("#kategori").val();
                const payload = {
                    nama,
                    sifat,
                    kategori
                };
                // console.log(payload);

                $.ajax({
                    url: "http://localhost:3000/criterias",
                    type: "POST",
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    success: function(response) {
                        // console.log(response);
                        const toastMessage = $('#toast-body');
                        const pesan = response.simpan === "insert" ? `Berhasil menambahkan data kriteria <b>${response.nama}</b>.` : `Berhasil memperbarui data kriteria <b>${response.nama}</b>.`
                        toastMessage.html(pesan);
                        $('#showToastPlacement').click();
                        fetchTableData();
                        addColumnCriteria(response.id);
                        $('#nama').val('');
                        alternatif = 1;
                    },
                    error: function(error) {
                        console.error('Pesan:', error);
                    },
                })
            });

            function fetchTableData() {
                $.ajax({
                    url: 'data-lokasi.php',
                    method: 'GET',
                    data: {
                        kriteria: true
                    },
                    dataType: 'json',
                    success: function(response) {
                        if ($.fn.DataTable.isDataTable('#datatable')) {
                            $('#datatable').DataTable().destroy();
                        }
                        let tableBody = $('#tableBody');
                        tableBody.empty();
                        response.forEach((data, index) => {
                            const badgeSifat = data.sifat === 'Benefit' ?
                                '<span class="badge bg-label-success">Benefit</span>' :
                                '<span class="badge bg-label-danger">Cost</span>';
                            const badgeKategori = data.kategori === 'Beneficial' ?
                                '<span class="badge bg-label-primary">Beneficial</span>' :
                                '<span class="badge bg-label-secondary">Non-Beneficial</span>';
                            const row = `
                                <tr>
                                    <td class='text-center'>${index + 1}</td>
                                    <td><strong>${data.nama}</strong></td>
                                    <td>C${index + 1}</td>
                                    <td>${badgeSifat}</td>
                                    <td>${badgeKategori}</td>
                                    <td class='text-center'>
                                        <a class="text-danger trash-btn" 
                                        href="${data.nama}"
                                        data-kriteria="${data.id}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#hapusKriteria">
                                        <i class="bx bx-trash me-1"></i>
                                        </a>
                                    </td>
                                </tr>`;
                            tableBody.append(row);
                        });
                        $('#datatable').DataTable({
                            "dom": '<"top"f>rt<"bottom"ilp><"clear">',
                            "language": {
                                "lengthMenu": 'Tampilkan <select>' +
                                    '<option value="5" selected>5</option>' +
                                    '<option value="10">10</option>' +
                                    '</select> baris data'
                            },
                            "pageLength": 5,
                            columnDefs: [{
                                targets: [2, 3, 4, 5],
                                orderable: false
                            }]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching data screenshot: ", error);
                    }
                });
            }

            function addColumnCriteria(criteriaId) {
                $("#row-matriks").empty();
                alternatif = 1;
                $.ajax({
                    type: "GET",
                    url: "./data-lokasi.php",
                    data: {
                        kriteria: true
                    },
                    contentType: 'application/json',
                    success: function(response) {
                        const row = `
                            <div class="input-group matriks-header" style="font-weight:bold;">
                                <div class=" form-floating">
                                    <input
                                        type="text"
                                        class="form-control"
                                        disabled
                                        placeholder="judul-matriks"
                                        name="judul-matriks"
                                        aria-describedby="floatingInputHelp" />
                                    <label for="judul-matriks"><i>Matriks Keputusan</i></label>
                                </div>
                            </div>
                            <div id="row-alternatives">
                                <div class="input-group alternatif_1">
                                    <div class="form-floating">
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="alternatif_1"
                                            name="lokasi[]"
                                            placeholder="Masukkan Lokasi"
                                            aria-describedby="floatingInputHelp" />
                                        <label for="lokasi[]"><b>Alternatif 1</b></label>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("#row-matriks").append(row);
                        response.forEach((data, index) => {
                            const rowHeader = `<input type='text' value='C${data.id}' class='form-control' style='font-weight:bold;' disabled />`;
                            $("#row-matriks .matriks-header").append(rowHeader);
                            const rowBody = `<input type='text' id='A${alternatif}_C${data.id}' placeholder='A${alternatif}-C${data.id}' class='form-control' required />`;
                            $("#row-matriks .alternatif_1").append(rowBody);
                        });
                    },
                    error: function(error) {
                        console.error('Pesan:', error);
                    },
                });
                $(".matriks-bobot").append(`<input type='text' id='bobot_C${criteriaId}' placeholder='C${criteriaId}' class='form-control' required />`)
            }

            $("#merec").on("click", function() {
                let matrix = [];
                $("#row-alternatives .input-group").each(function() {
                    let alternatif = {};
                    let alternatifName = $(this).find("input[name='lokasi[]']").val();
                    if (alternatifName) {
                        alternatif['name'] = alternatifName;
                    }

                    let criteria = {};
                    $(this)
                        .find("input[id^='A']")
                        .each(function() {
                            let criteriaId = $(this).attr("id").split("_")[1]; // Extract C1, C2, etc.
                            let value = $(this).val();
                            criteria[criteriaId] = value;
                        });

                    alternatif['criteria'] = criteria;
                    matrix.push(alternatif);
                });
                ''
                // let matrixJSON = JSON.stringify(matrix, null, 4); // Beautified JSON output for readability
                // console.log(matrixJSON);

                const toastMessage = $('#toast-body');
                $.ajax({
                    url: 'http://localhost:3000/merec',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(matrix),
                    success: function(response) {
                        toastMessage.html(`Berhasil memperbarui data bobot menggunakan metode <b>MEREC</b>.`);
                        $('#showToastPlacement').click();

                        // Update bobot values in input fields
                        for (let key in response) {
                            if (response.hasOwnProperty(key)) {
                                $(`#bobot_${key}`).val(response[key]); // fill based on ID
                            }
                        }

                    },
                    error: function(xhr, status, error) {
                        console.error('Error sending data:', error, xhr.responseText);
                        $("#toast-title").text("Peringatan!");
                        $("#toast-smallText").text("Pesan Error");
                        toastMessage.html(`Tolong lengkapi dulu matriks sebelum menggunakan metode <b>MEREC</b> dan pastikan jumlah alternatif sudah lebih dari 1.`);
                        $("#selectTypeOpt").val("bg-danger");
                        $("#showToastPlacement").click();
                        setTimeout(function() {
                            $("#selectTypeOpt").val("bg-success");
                            $("#toast-title").text("Notifikasi");
                            $("#toast-smallText").text("Success");
                        }, 6000);
                    }
                });
            });

            // form seleksi
            $("#seleksi").on("submit", function(e) {
                e.preventDefault();
                let matrix = [];
                $("#row-alternatives .input-group").each(function() {
                    let alternatif = {};
                    let alternatifName = $(this).find("input[name='lokasi[]']").val();
                    if (alternatifName) {
                        alternatif['name'] = alternatifName;
                    }

                    let criteria = {};
                    $(this)
                        .find("input[id^='A']")
                        .each(function() {
                            let criteriaId = $(this).attr("id").split("_")[1];
                            let value = $(this).val();
                            criteria[criteriaId] = value;
                        });

                    alternatif['criteria'] = criteria;
                    matrix.push(alternatif);
                });

                let bobot = {};
                $(".matriks-bobot input[id^='bobot_C']").each(function() {
                    let criteriaId = $(this).attr("id").split("_")[1];
                    let value = $(this).val();
                    bobot[criteriaId] = value;
                });
                let method = $("input[name='btnradio']:checked").next("label").text();
                const payload = {
                    matriks: matrix,
                    bobot: bobot,
                    metode: method,
                };

                $.ajax({
                    url: 'http://localhost:3000/seleksi',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(response) {
                        console.log(response);
                        const newWindow = window.open("", "_blank");
                        if (newWindow) {
                            // Write the HTML response into the new window
                            newWindow.document.open();
                            newWindow.document.write(response);
                            newWindow.document.close();
                        } else {
                            console.error("Unable to open new window. Check pop-up blocker settings.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error sending data:', error, xhr.responseText);
                    }
                });
            });
        });
    </script>
    <style>
        tbody td:nth-child(2) {
            text-align: start !important;
        }
    </style>
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
                    <li class="menu-item">
                        <a href="screenshoot.php" class="menu-link">
                            <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/traffic-jam.png" alt="">
                            <div data-i18n="Tables">Screenshot Trafik</div>
                        </a>
                    </li>

                    <li class="menu-item active">
                        <a href="mcdm.php" class="menu-link">
                            <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/topsis.png" alt="">
                            <div data-i18n="Tables">Seleksi Lokasi Terbaik</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- TOAST -->
            <div id="alert-kriteria" class="bs-toast toast toast-placement-ex m-2 fade" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" style="z-index: 9999999;">
                <div class="toast-header">
                    <i class='bx bx-info-circle me-2'></i>
                    <div id="toast-title" class="me-auto fw-medium">Notifikasi</div>
                    <small id="toast-smallText">Success</small>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button> -->
                </div>
                <div id="toast-body" class="toast-body"></div>
            </div>
            <input id="selectTypeOpt" type="hidden" value="bg-success" />
            <input id="selectPlacement" type="hidden" value="top-0 end-0" />

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
            <!-- Modal DELETE -->
            <div class="modal fade" id="hapusKriteria" data-bs-backdrop="static" tabindex="-1">
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
                                    <input type="text" id="search" class="form-control border-0 shadow-none" placeholder="Cari Data Tabel Kriteria" aria-label="Cari Data Tabel Kriteria" aria-controls="datatable" />
                                </div>
                            </div>
                            <!-- /Search -->

                            <ul class="navbar-nav flex-row align-items-center ms-auto">

                                <!-- User -->
                                <button id="showToastPlacement" data-bs-toggle="toast" data-bs-target="#alert-kriteria" class="btn" style="visibility:hidden;">s</button>
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
                        <h4 class="fw-bold py-3 mb-3"><span class="text-muted fw-light">Data</span> Kriteria</h4>

                        <!-- Basic Layout & Basic with Icons -->
                        <div class="row">
                            <!-- Basic Layout -->
                            <div class="col-xxl">
                                <div class="card">
                                    <div class="card-header d-flex align-items-center justify-content-center"></div>
                                    <div class="card-body">
                                        <form id="kriteria">
                                            <div class="row mb-3">
                                                <div class="col-lg-1"></div>
                                                <label class="col-lg-1 col-form-label text-center" for="nama">Nama</label>
                                                <div class="col-lg-2 mb-3 input-group input-group-merge" style="box-shadow: none;">
                                                    <span id="basic-icon-default-fullname1" class="input-group-text"><i class='bx bxs-rename'></i></span>
                                                    <input type="text" id="nama" name="nama[]" class="form-control" placeholder="Masukkan Kriteria" required />
                                                </div>
                                                <label class="col-lg-1 col-form-label text-center" for="sifat">Sifat</label>
                                                <div class="col-lg-2 mb-3 input-group input-group-merge" style="box-shadow: none;">
                                                    <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bxs-award'></i></span>
                                                    <select class="form-select" id="sifat" name="sifat">
                                                        <option value="0" selected>Benefit</option>
                                                        <option value="1">Cost</option>
                                                    </select>
                                                </div>
                                                <label class="col-lg-1 col-form-label text-center" for="kategori">Kategori</label>
                                                <div class="col-lg-2 mb-3 input-group input-group-merge" style="box-shadow: none;">
                                                    <span id="basic-icon-default-fullname2" class="input-group-text"><i class='bx bxs-category'></i></span>
                                                    <select class="form-select" id="kategori" name="kategori">
                                                        <option value="0" selected>Beneficial</option>
                                                        <option value="1">Non-Beneficial</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-1"><button type="submit" name="tambahKriteria" class="btn btn-warning">Simpan</button></div>
                                                <div class="col-lg-1"></div>
                                            </div>
                                            <!-- <div class="row mt-3 mb-3 d-flex spasi justify-content-center">
                                                <div class="col-md-6">
                                                    <button type="submit" name="tambahKriteria" class="btn btn-warning d-inline-flex align-items-center">Simpan</button>
                                                </div>
                                            </div> -->
                                        </form>
                                        <div class="divider divider-warning">
                                            <div class="divider-text">Tabel <i class='bx bxs-data ms-2'></i></div>
                                        </div>
                                        <div class="row">
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="datatable">
                                                    <thead>
                                                        <tr>
                                                            <th class="dt-head-center sorting" scope="col">No.</th>
                                                            <th class="dt-head-center sorting" scope="col">Nama</th>
                                                            <th class="dt-head-center">Kode</th>
                                                            <th class="dt-head-center">Sifat</th>
                                                            <th class="dt-head-center">Kategori</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="table-border-bottom-0" id="tableBody">
                                                        <?php
                                                        $kriteria = mysqli_query($koneksi, "SELECT id, nama, sifat, kategori FROM criterias ORDER BY id ASC");
                                                        $no = 1;
                                                        while ($data = mysqli_fetch_row($kriteria)) {
                                                            $id = $data[0];
                                                            $nama = ucfirst($data[1]);
                                                            $jenis = $data[2];
                                                            $kategori = $data[3];
                                                            if ($jenis == 'Benefit') {
                                                                $badgeSifat = '<span class="badge bg-label-success">' . $jenis . '</span>';
                                                            } else {
                                                                $badgeSifat = '<span class="badge bg-label-danger">' . $jenis . '</span>';
                                                            }
                                                            if ($kategori == 'Beneficial') {
                                                                $badgeKategori = '<span class="badge bg-label-primary">' . $kategori . '</span>';
                                                            } else {
                                                                $badgeKategori = '<span class="badge bg-label-secondary">' . $kategori . '</span>';
                                                            }
                                                            echo "
                                                <tr>
                                                <td class='text-center'>$no</td>
                                                <td><strong>$nama</strong></td>
                                                <td>C$no</td>
                                                <td>$badgeSifat</td>
                                                <td>$badgeKategori</td>
                                                <td class='text-center'>
                                                    <a class=\"text-danger trash-btn\" 
                                                    href=\"$nama\"
                                                    data-kriteria=\"$id\"
                                                    data-bs-toggle=modal 
                                                    data-bs-target=#hapusKriteria>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <hr class="my-4" />
                        <h4 class="fw-bold py-3 mb-4" style="text-align: center !important;"><span class="text-muted fw-light">Form</span> Seleksi</h4>

                        <div class="row">
                            <!-- Basic Layout -->
                            <div class="col-xxl">
                                <div class="card">
                                    <div class="card-header d-flex align-items-center justify-content-center">Tolong lengkapi data-data dibawah ini</div>
                                    <div class="card-body">
                                        <form id="seleksi">
                                            <div class="row mb-2" id="row-matriks">
                                                <div class="input-group matriks-header" style="font-weight:bold;">
                                                    <div class="form-floating">
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            disabled
                                                            placeholder="judul-matriks"
                                                            name="judul-matriks"
                                                            aria-describedby="floatingInputHelp" />
                                                        <label for="judul-matriks"><i>Matriks Keputusan</i></label>
                                                    </div>
                                                    <?php
                                                    $matriks_c = mysqli_query($koneksi, "SELECT id FROM criterias ORDER BY id ASC");
                                                    while ($data = mysqli_fetch_row($matriks_c)) {
                                                        $id = $data[0];
                                                        echo "<input type='text' value='C$id' class='form-control' style='font-weight:bold;' disabled />";
                                                    }
                                                    ?>
                                                </div>
                                                <div id="row-alternatives">
                                                    <div class="input-group alternatif_1">
                                                        <div class="form-floating">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="alternatif_1"
                                                                name="lokasi[]"
                                                                placeholder="Masukkan Lokasi"
                                                                aria-describedby="floatingInputHelp" required />
                                                            <label for="lokasi[]"><b>Alternatif 1</b></label>
                                                        </div>
                                                        <?php
                                                        $matriks_c = mysqli_query($koneksi, "SELECT id FROM criterias ORDER BY id ASC");
                                                        while ($data = mysqli_fetch_row($matriks_c)) {
                                                            $id = $data[0];
                                                            echo "<input type='text' id='A1_C$id' placeholder='A1-C$id' class='form-control' required />";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mb-3">
                                                <div class="d-flex justify-content-center">
                                                    <button type="button" name="tambahAlternatif" class="btn btn-warning d-inline-flex align-items-center"><i class='bx bx-plus me-2'></i> Tambah Alternatif</button>
                                                </div>
                                            </div>
                                            <div class="divider divider-warning py-3">
                                                <div class="divider-text">Bobot Kriteria</div>
                                            </div>
                                            <div class="input-group mt-3 matriks-bobot">
                                                <button class="btn btn-outline-warning" type="button" id="merec">Gunakan Metode MEREC</button>
                                                <?php
                                                $matriks_c = mysqli_query($koneksi, "SELECT id FROM criterias ORDER BY id ASC");
                                                while ($data = mysqli_fetch_row($matriks_c)) {
                                                    $id = $data[0];
                                                    echo "<input type='text' id='bobot_C$id' placeholder='C$id' class='form-control' required />";
                                                }
                                                ?>
                                            </div>
                                            <div class="divider divider-warning pt-3">
                                                <div class="divider-text">Metode</div>
                                            </div>
                                            <div class="row">
                                                <div class="col d-flex justify-content-center">
                                                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                                        <input type="radio" class="btn-check" name="btnradio" id="btnradio1" checked>
                                                        <label class="btn btn-outline-warning" for="btnradio1">TOPSIS</label>
                                                        <input type="radio" class="btn-check" name="btnradio" id="btnradio2">
                                                        <label class="btn btn-outline-warning" for="btnradio2">WASPAS</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="d-flex justify-content-center">
                                                    <button type="submit" name="hitung" class="btn btn-warning d-inline-flex align-items-center"><i class='bx bx-search me-2'></i> Cari Lokasi Terbaik</button>
                                                </div>
                                            </div>
                                        </form>
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