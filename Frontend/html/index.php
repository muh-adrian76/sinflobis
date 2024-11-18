<?php
session_start();
if (empty($_SESSION['user']) && empty($_SESSION['pass'])) {
  echo "<script>window.location.replace('../index.php')</script>";
}

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

// ambil keterangan dashboard
$q_lokasi = mysqli_query($koneksi, "SELECT COUNT(id) FROM locations");
$d1 = mysqli_fetch_row($q_lokasi);
$jumlahRestArea = $d1[0];
$q_grup = mysqli_query($koneksi, "SELECT COUNT(id) FROM location_groups");
$d1 = mysqli_fetch_row($q_grup);
$jumlahGrup = $d1[0];

// ambil rata2 jam sibuk semua lokasi
$meanValues = [];
$results = mysqli_query($koneksi, "SELECT day, time, AVG(busy_percentage) AS mean_busy_percentage FROM popular_times GROUP BY day, time ORDER BY time");
foreach ($results as $row) {
  $meanValues[] = [
    'day' => $row['day'],
    'time' => $row['time'],
    'busy_percentage' => (float)$row['mean_busy_percentage']
  ];
}
$rata2 = json_encode($meanValues);

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
                window.location.replace('index.php');
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
                window.location.replace('index.php');
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
                window.location.replace('index.php');
            }, 3000);
          </script>";
  }

  // mysqli_query($koneksi, "ALTER TABLE pustaka_1 DROP id");
  // mysqli_query($koneksi, "ALTER TABLE pustaka_1 ADD id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
}

?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>SINFLOBIS | Dashboard</title>

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
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <!-- Helpers -->
  <script src="../assets/vendor/js/helpers.js"></script>

  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="../assets/js/config.js"></script>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/css/multi-select-tag.css">
  <script src="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/js/multi-select-tag.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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

      function groupTitleChart() {
        var selectedText = $("#grup-lokasi").find("option:selected").text();
        return `Rata-rata ${selectedText.toLowerCase()}`;
      }

      // default chart
      let myChart;
      chartBackend(<?php echo $rata2 ?>);
      $(".judul-grafik").text(groupTitleChart());

      function createChart(labels, data) {
        const ctx = document.getElementById('myChart').getContext('2d');
        if (myChart) {
          myChart.destroy(); // Destroy the previous chart instance
        }
        const property = {
          id: 'chartProperty',
          afterInit(chart, args, options) {
            // console.log(chart.legend.fit);
            const fitValue = chart.legend.fit;
            chart.legend.fit = function fit() {
              fitValue.bind(chart.legend)();
              let height = this.height += 25;
              return height;
            };
          }
        }
        const config = {
          type: 'radar',
          data: {
            labels: labels,
            datasets: data // Use the datasets array created above
          },
          options: {
            elements: {
              line: {
                borderWidth: 3
              }
            },
            scale: {
              ticks: {
                beginAtZero: true,
                max: 100 // Assuming busy_percentage is between 0 and 100
              }
            },
            plugins: {
              legend: {
                onClick: (e, legendItem, legend) => {
                  const index = legendItem.datasetIndex;
                  const chart = legend.chart;
                  const dataset = chart.data.datasets[index];

                  // Check if all datasets are currently visible
                  const allVisible = chart.data.datasets.every(ds => ds.hidden !== true);

                  if (allVisible) {
                    // If all datasets are visible, hide all except the clicked one
                    chart.data.datasets.forEach((ds, i) => {
                      ds.hidden = i !== index;
                    });
                  } else if (dataset.hidden) {
                    // If the clicked dataset is currently hidden, show all datasets
                    dataset.hidden = false;
                  } else {
                    // If only one dataset is visible, toggle its visibility
                    chart.data.datasets.forEach(ds => {
                      ds.hidden = false;
                    });
                  }

                  chart.update();
                },
              },
              tooltip: {
                callbacks: {
                  title: function(tooltipItems) {
                    // Get the time from the first tooltip item
                    return `${tooltipItems[0].label} WIB`; // This will be the time
                  },
                  label: function(tooltipItem) {
                    // Get the dataset label
                    const datasetLabel = tooltipItem.dataset.label || '';
                    // Get the data value
                    const dataValue = tooltipItem.raw; // This should now correctly reference the busy_percentage
                    // Return the custom tooltip string
                    return `${datasetLabel}: ${dataValue}%`; // Customize as needed
                  }
                }
              }
            }
          },
          plugins: [property]
        };
        myChart = new Chart(ctx, config);
      }

      function getRandomColor() {
        const r = Math.floor(Math.random() * 128) + 96;
        const g = Math.floor(Math.random() * 128) + 96;
        const b = Math.floor(Math.random() * 128) + 96;
        return `rgb(${r}, ${g}, ${b})`; // Return RGB color
      }

      function getRGBAColor(rgbColor, alpha) {
        return rgbColor.replace('rgb', 'rgba').replace(')', `, ${alpha})`);
      }

      function chartBackend(data) {
        if (data.length > 0) {
          const labels = [...new Set(data.map(item => item.time))]; // Get unique time values
          labels.sort((a, b) => {
            // Convert time strings to minutes since midnight
            const timeA = a.split(':').reduce((acc, time) => (60 * acc) + +time, 0);
            const timeB = b.split(':').reduce((acc, time) => (60 * acc) + +time, 0);
            return timeA - timeB; // Sort in ascending order
          });
          const newLabels = labels.map(label => `${label} WIB`);
          const datasets = {};

          // Group data by day
          data.forEach(item => {
            if (!datasets[item.day]) {
              const rgbColor = getRandomColor();
              datasets[item.day] = {
                label: item.day,
                data: Array(labels.length).fill(0), // Initialize data array with zeros
                fill: true,
                backgroundColor: getRGBAColor(rgbColor, 0.2), // You can customize this for each day
                borderColor: getRGBAColor(rgbColor, 1), // You can customize this for each day
                pointBackgroundColor: getRGBAColor(rgbColor, 1),
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: getRGBAColor(rgbColor, 1)
              };
            }
            // Find the index of the current time in the labels array
            const timeIndex = labels.indexOf(item.time);
            if (timeIndex !== -1) {
              datasets[item.day].data[timeIndex] = item.busy_percentage; // Set the busy percentage
            }
          });

          // Convert datasets object to an array
          const datasetsArray = Object.values(datasets);

          // Create or update the chart
          createChart(labels, datasetsArray);
        } else {
          alert('Tidak ada data yang tersedia pada lokasi ini.');
        }
      }

      function fetchPopularTimes(locationId) {
        $.ajax({
          url: './data-kesibukan.php', // Create this PHP file to fetch popular times
          type: 'GET',
          data: {
            location_id: locationId
          },
          dataType: 'json',
          success: function(data) {
            chartBackend(data);
          },
          error: function(xhr, status, error) {
            console.error('Error fetching popular times:', error);
          }
        });
      }

      function fetchPopularTimesGroup(groupId) {
        $.ajax({
          url: './data-kesibukan.php', // Create this PHP file to fetch popular times
          type: 'GET',
          data: {
            group_id: groupId
          },
          dataType: 'json',
          success: function(data) {
            chartBackend(data);
          },
          error: function(xhr, status, error) {
            console.error('Error fetching popular times:', error);
          }
        });
      }

      // select form (group)
      $("#grup-lokasi").change(function() {
        const id = $(this).find("option:selected").val();
        $("input[name='lokasi[]']").val('');
        $(".judul-grafik").text(groupTitleChart());
        fetchPopularTimesGroup(id);
      });
      // add group select form
      $.ajax({
        url: './data-lokasi.php', // Create this PHP file to fetch locations
        method: 'GET',
        data: {
          group: true
        },
        dataType: 'json',
        success: function(data) {
          $.each(data, function(index, group) {
            $('#grup-lokasi').append(
              $('<option></option>').val(group.id).text(group.name)
            );
          });
        },
        error: function(xhr, status, error) {
          console.error('Error fetching locations:', error);
        }
      });
      $.ajax({
        url: './data-lokasi.php', // Create this PHP file to fetch locations
        method: 'GET',
        data: {
          fetch_location: true
        },
        dataType: 'json',
        success: function(data) {
          $.each(data, function(index, location) {
            $('#group-select').append(
              $('<option></option>').val(location.id).text(location.name)
            );
          });
        },
        error: function(xhr, status, error) {
          console.error('Error fetching locations:', error);
        }
      });
      $("button[name='tambah_grup']").click(function() {
        new MultiSelectTag('group-select', {
          rounded: true, // default true
          shadow: true, // default false
          placeholder: 'Cari rest area', // default Search...
          tagColor: {
            textColor: '#3C3C3C',
            borderColor: '#fd7e14',
            bgColor: '#FDE4D0',
          },
          onChange: function(values) {
            // console.log(values)
            let selection = [];
            for (let i = 0; i < values.length; i++) {
              selection.push(values[i].value); // Push each value into the selections array  
            }
            $('#group-form').data('selection', selection);
          }
        });
      });
      $('#group-form').on('submit', function(event) {
        event.preventDefault();
        document.body.style.overflow = 'hidden';
        $('#loading-screen').fadeIn('slow');
        const nama = $("input[name='nama_grup']").val();
        const selection = $('#group-form').data('selection') || []; // Get the stored selection
        const grup = {
          nama: nama,
          anggota: selection
        };
        $.ajax({
          url: 'http://localhost:3000/grup-lokasi',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            grup
          }), // Send data as JSON
          success: function(response) {
            // console.log(response);
            statusChange('sukses');
            $('.loading-text').text('Berhasil menambahkan grup!')
            setTimeout(function() {
              window.location.replace('index.php');
            }, 3000);
          },
          error: function(error) {
            console.error('Pesan:', error);
          },
        });
      });

      // input form (single)
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
                $(this).val(ui.item.label); // Set the input value to the label
                $(".judul-grafik").text(ui.item.label);
                fetchPopularTimes(ui.item.value); // Fetch popular times using the selected item's value
                $("#grup-lokasi").val('');
                return false; // Prevent the default behavior
              }
            });
          })
          .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error: ", textStatus, errorThrown);
          })
      });

      function exportToJsonFile(data, nama) {
        const jsonString = JSON.stringify(data, null, 2); // Pretty-print JSON with 2-space indentation
        const blob = new Blob([jsonString], {
          type: "application/json"
        });

        // Create a link element
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `${nama}.json`; // Name of the file

        // Simulate a click on the link to trigger download
        document.body.appendChild(link);
        link.click();

        // Clean up by removing the link element
        document.body.removeChild(link);

        // exportToJsonFile(response, query);
      }

      function exportToCsvFile(data, nama) {
        // Create CSV header based on keys from the first object in data
        const headers = Object.keys(data[0]).join(",") + "\n";
        // Map each object to a CSV row
        const rows = data
          .map(obj => Object.values(obj).join(","))
          .join("\n");

        const csvContent = headers + rows;
        const blob = new Blob([csvContent], {
          type: "text/csv"
        });

        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `${nama}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // exportToCsvFile(data, "data");
      }

      async function exportToPdfFile(data, nama) {
        const {
          jsPDF
        } = window.jspdf;
        const doc = new jsPDF();

        // Set the title of the document
        doc.setFontSize(16);
        doc.text("Exported Data", 10, 10);

        // Define headers from data keys
        const headers = Object.keys(data[0]);
        let startY = 20;

        // Print headers
        doc.setFontSize(12);
        doc.text(headers.join(" | "), 10, startY);
        startY += 10;

        // Print each row of data
        data.forEach((item, index) => {
          const row = headers.map(header => item[header]);
          doc.text(row.join(" | "), 10, startY + index * 10);
        });

        // Save the file
        doc.save(`${nama}.pdf`);

        // exportToPdfFile(data, "data");
      }

      $('#saveChart').on('click', function() {
        const grafikElement = document.querySelector('.grafik');
        const judul = $(".judul-grafik").text();

        if (grafikElement) {
          html2canvas(grafikElement).then(canvas => {
            const image = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.href = image;
            link.download = `${judul}.png`;
            link.click();
          }).catch(error => {
            console.error('Error capturing the grafik element:', error);
          });
        } else {
          console.error('.grafik element not found.');
        }
      });
      $('#resetChartColor').on('click', function() {
        myChart.data.datasets.forEach((dataset, index) => {
          const rgbColor = getRandomColor();
          dataset.backgroundColor = getRGBAColor(rgbColor, 0.2);
          dataset.borderColor = getRGBAColor(rgbColor, 1);
          dataset.pointBackgroundColor = getRGBAColor(rgbColor, 1);
          dataset.pointHoverBorderColor = getRGBAColor(rgbColor, 1);
        });

        myChart.update();
      });
    });
  </script>
</head>

<body>
  <!-- loader -->
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
          <li class="menu-item active">
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

          <li class="menu-item">
            <a href="topsis.php" class="menu-link">
              <img class="menu-icon tf-icons me-2" src="../assets/img/icons/unicons/topsis.png" alt="">
              <div data-i18n="Tables">Seleksi TOPSIS</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="waspas.php" class="menu-link">
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
      <div class="modal fade" id="tambahGrup" aria-labelledby="tambahGrup" tabindex="-1" style="display: none" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <button
                class="btn-close"
                data-bs-dismiss="modal"></button>
            </div>
            <div class='card' style="box-shadow: none;">
              <div class='card-body' style="padding-bottom: 3rem;">
                <div class='d-flex flex-column align-items-center gap-3'>
                  <div class='d-flex flex-column align-items-center'>
                    <h4 class='mb-2'>Tambah Grup Baru <i class='bx bxs-group ps-2' style="font-size: 2rem;"></i></h4>
                    <p>Tolong pilih lokasi yang akan ditambahkan ke grup!</p>
                  </div>
                  <form id='group-form' class='mb-3' style="width: 70%;">
                    <div class='mb-3'>
                      <div class="form-floating">
                        <input
                          type="text"
                          class="form-control mb-3"
                          id="floatingInput"
                          name="nama_grup"
                          placeholder="Contoh: Grup 1"
                          aria-describedby="floatingInputHelp" required />
                        <label for="floatingInput">Nama Grup</label>
                      </div>
                      <label class="form-label" style="margin-left:1rem;" for="group-select">Anggota</label>
                      <select name="group-select" id="group-select" multiple required></select>
                    </div>
                    <div class='mb-3'>
                      <button class='btn btn-warning d-grid w-100' type='submit'>Simpan</button>
                    </div>
                  </form>
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
                <div class="nav-item d-flex align-items-center">
                  <div>
                    <h5 class="mb-0"><strong>Dashboard</strong></h5>
                    <small class="logo">Sistem Informasi Lokasi Bisnis</small>
                  </div>
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

          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
              <div class="col-lg-12 order-0 mb-4">
                <div class="card">
                  <div class="row card-body" style="text-align: center;padding-right: 0;">
                    <div class="col" style="padding-right: 2.5rem;">
                      <h4 class="card-title"><b>Grafik Jam Sibuk</b> <i class='bx bx-time text-warning' style="font-size: 1.5rem;"></i></h4>
                      <div id="defaultFormControlHelp" class="form-text">
                        <i>Tingkat kesibukan dalam satuan (%)</i>
                      </div>
                      <div class="divider divider-warning" style="padding-right: 1rem;">
                        <div class="divider-text"><i class='bx bx-search'></i></div>
                      </div>
                    </div>
                    <div class="row search-grafik">
                      <div id="defaultFormControlHelp" class="form-text mb-3">
                        Pilih salah satu metode pencarian
                      </div>
                      <div class="col-lg-6 mb-3">
                        <label class="form-label" for="select-group">Grup / Jamak (Nilai Rata-Rata)</label>
                        <!-- <div class="col-md hstack d-flex gap-3">
                          <button type="button" data-bs-toggle="modal" data-bs-target="#tambahGrup" name="tambah_grup" class="btn btn-outline-warning"><i class='bx bx-plus'></i></button> -->
                        <select id="grup-lokasi" class="form-select" name="select-group">
                          <option value="">Pilih Grup</option>
                          <option value="0" selected>Semua lokasi</option>
                        </select>
                        <p class="mt-2" style="font-size: 85%;">Jumlah saat ini : <strong><?php echo $jumlahGrup ?> Grup</strong></p>
                        <!-- </div> -->
                      </div>
                      <div class="col-lg-6">
                        <label class="form-label" for="locationSelect">Tunggal</label>
                        <input type="text" id="locationSelect" class="form-control mb-2" name=lokasi[] value="" placeholder="Masukkan nama rest area" required />
                        <small class="text-nowrap">Jumlah saat ini : <strong><?php echo $jumlahRestArea ?> Lokasi</strong><small>
                      </div>
                    </div>
                    <div class="card-body" style="padding-right: 3rem;">
                      <div class="row d-flex justify-content-center">
                        <div class="col-12">
                          <div class="grafik" style="margin: 0 auto;">
                            <h4 class="judul-grafik"></h4>
                            <canvas id="myChart"></canvas>
                          </div>
                          <div class="d-flex gap-3 justify-content-center button-canvas mt-3">
                            <button id="resetChartColor" class="btn btn-palette" style="font-weight: bold;"><i class='bx bx-reset me-1'></i> Ubah Warna</button>
                            <button id="saveChart" class="btn btn-outline-warning" style="font-weight: bold;"><i class='bx bx-image me-1'></i> Simpan</button>
                          </div>
                        </div>
                      </div>
                      <!-- <div class="divider divider-warning" style="padding-right: 1rem;">
                        <div class="divider-text"><i class='bx bx-save'></i></div>
                      </div> -->
                      <!-- <div class="row">
                        <small class="text-light fw-semibold">Pilih format penyimpanan</small>
                        <div class="mt-3">
                          <div class="btn-group" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-outline-warning">JSON</button>
                            <button type="button" class="btn btn-outline-success">CSV</button>
                            <button type="button" class="btn btn-outline-danger">PDF</button>
                          </div>
                        </div>
                      </div> -->
                    </div>
                  </div>
                </div>
              </div>
              <!-- Order Statistics -->
              <div class="col-lg-3 order-3 order-md-2 d-none">
                <div class="row d-flex flex-column">
                  <div class="mb-3">
                    <div class="card">
                      <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="card-title d-flex align-items-center gap-3 align-self-start ms-3">
                          <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning"
                              data-bs-toggle="tooltip"
                              data-bs-offset="0,4"
                              data-bs-placement="bottom"
                              data-bs-html="true"
                              title="<div class='d-flex gap-3 align-items-center text-start'><i class='bx bx-info-circle'></i> <span>Jumlah data hasil dari scraping data lokasi pada Google Maps</span></div>">
                              <i class='bx bx-location-plus'></i>
                            </span>
                          </div>
                          <span class="d-block fw-semibold text-warning mb-1">Hasil Scraping</span>
                        </div>
                        <h5 class="card-title text-nowrap mb-2"><strong><?php echo $jumlahRestArea ?></strong> Lokasi</h5>
                        <!-- <small class="text-primary fw-semibold"><i class="bx bx-wink-smile"></i> Orang</small> -->
                      </div>
                    </div>
                  </div>
                  <div class=" mb-3">
                    <div class="card">
                      <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="card-title d-flex align-items-center gap-3 align-self-start ms-3">
                          <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-danger"
                              data-bs-toggle="tooltip"
                              data-bs-offset="0,4"
                              data-bs-placement="bottom"
                              data-bs-html="true"
                              title="<div class='d-flex gap-3 align-items-center text-start'><i class='bx bx-info-circle'></i> <span>Jumlah data trafik dari hasil screenshoot lalu lintas</span></div>">
                              <i class='bx bx-file'></i>
                            </span>
                          </div>
                          <span class="d-block fw-semibold mb-1 text-danger">Data Trafik</span>
                        </div>
                        <h5 class="card-title text-nowrap mb-2"><strong><?php echo $jumlahRestArea ?></strong></h5>
                        <!-- <small class="text-secondary fw-semibold"><i class="bx bx-id-card"></i> Username</small> -->
                      </div>
                    </div>
                  </div>
                  <div class=" mb-3">
                    <div class="card">
                      <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="card-title d-flex align-items-center gap-3 align-self-start ms-3">
                          <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-secondary"
                              data-bs-toggle="tooltip"
                              data-bs-offset="0,4"
                              data-bs-placement="top"
                              data-bs-html="true"
                              title="<div class='d-flex gap-3 align-items-center text-start'><i class='bx bx-info-circle'></i> <span>Jumlah data hasil dari perhitungan statistik menggunakan metode TOPSIS</span></div>">
                              <i class='bx bx-file'></i>
                            </span>
                          </div>
                          <span class="d-block fw-semibold text-secondary mb-1">Hasil TOPSIS</span>
                        </div>
                        <h5 class="card-title text-nowrap mb-2"><strong><?php echo $jumlahRestArea ?></strong></h5>
                        <!-- <small class="text-secondary fw-semibold"><i class="bx bx-id-card"></i> Username</small> -->
                      </div>
                    </div>
                  </div>
                  <div class=" mb-3">
                    <div class="card">
                      <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="card-title d-flex align-items-center gap-3 align-self-start ms-3">
                          <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-dark"
                              data-bs-toggle="tooltip"
                              data-bs-offset="0,4"
                              data-bs-placement="top"
                              data-bs-html="true"
                              title="<div class='d-flex gap-3 align-items-center text-start'><i class='bx bx-info-circle'></i> <span>Jumlah data hasil dari perhitungan statistik menggunakan metode WASPAS</span></div>">
                              <i class='bx bx-file'></i>
                            </span>
                          </div>
                          <span class="fw-semibold text-dark d-block mb-1">Hasil WASPAS</span>
                        </div>
                        <h5 class="card-title mb-2"><strong><?php echo $jumlahRestArea ?></strong></h5>
                        <!-- <small class="text-info fw-semibold"><i class="bx bx-file"></i> Laporan</small> -->
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
            <!--/ Transactions -->
          </div>

        </div>
        <!-- / Content -->

        <!-- Footer -->
        <!-- <footer class="content-footer footer bg-footer-theme">
          <div class="container-xxl d-flex flex-wrap justify-content-center py-2 flex-md-row flex-column" style="text-align: center !important;">
            <div class="mb-2 mb-md-0">
              ©
              <script>
                document.write(new Date().getFullYear());
              </script>

              <a href="#" class="footer-link fw-bolder">| SINFLOBIS</a>
            </div>
          </div>
        </footer> -->
        <!-- / Footer -->

        <div class="content-backdrop fade"></div>
      </div>
      <!-- Content wrapper -->
    </div>
    <!-- / Layout page -->
  </div>

  <!-- Overlay -->
  <div class="layout-overlay layout-menu-toggle"></div>
  <!-- / Layout wrapper -->


  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  <script src="../assets/vendor/libs/popper/popper.js"></script>
  <script src="../assets/vendor/js/bootstrap.js"></script>
  <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <!-- Page JS -->
  <script src="../assets/js/pages-account-settings-account.js"></script>

  <script src="../assets/vendor/js/menu.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>

  <!-- Main JS -->
  <script src="../assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="../assets/js/dashboards-analytics.js"></script>

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>