<?php
include "../class.php";
$sinflobis = new sinflobis;
$koneksi = $sinflobis->koneksi();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($_POST['nama'])) {
    $nama = $_POST['nama'];
    $data = [];
    $q = mysqli_query($koneksi, "SELECT id,name FROM locations WHERE name LIKE '%$nama%'");
    while ($d = mysqli_fetch_row($q)) {
        $label = $d[1];
        $value = $d[0];
        $data[] = array('label' => $label, 'value' => $value);
    }
    if (empty($data)) {
        $data[] = array('label' => 'No results found', 'value' => '');
    }
    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($data);
}

if (isset($_POST['grup'])) {
    $grup = $_POST['grup'];
    $data = [];
    $q = mysqli_query($koneksi, "SELECT id,name FROM location_groups WHERE name LIKE '%$grup%'");
    while ($d = mysqli_fetch_row($q)) {
        $label = $d[1];
        $value = $d[0];
        $data[] = array('label' => $label, 'value' => $value);
    }
    if (empty($data)) {
        $data[] = array('label' => 'No results found', 'value' => '');
    }
    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($data);
}

if (isset($_POST['kriteria'])) {
    $kriteria = $_POST['kriteria'];
    $data = [];
    $q = mysqli_query($koneksi, "SELECT id,nama FROM criterias WHERE nama LIKE '%$kriteria%'");
    while ($d = mysqli_fetch_row($q)) {
        $label = $d[1];
        $value = $d[0];
        $data[] = array('label' => $label, 'value' => $value);
    }
    if (empty($data)) {
        $data[] = array('label' => 'No results found', 'value' => '');
    }
    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($data);
}

if (isset($_GET['fetch_location'])) {
    $sql = "SELECT id, name FROM locations";
    $result = mysqli_query($koneksi, $sql);

    $locations = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $locations[] = $row;
        }
    }

    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($locations);
}

if (isset($_GET['group'])) {
    $sql = "SELECT id, name FROM location_groups";
    $result = mysqli_query($koneksi, $sql);

    $groups = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $groups[] = $row;
        }
    }

    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($groups);
}

if (isset($_GET['screenshot'])) {
    $screenshot = mysqli_query($koneksi, "SELECT nama, jenis, hari, waktu, url, timestamp FROM pictures ORDER BY timestamp DESC");
    $dataArray = [];
    while ($data = mysqli_fetch_assoc($screenshot)) {
        $dataArray[] = $data;
    }
    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($dataArray);
}

if (isset($data['gambar'])) {
    $gambar = $data['gambar'];
    $q = mysqli_query($koneksi, "SELECT timestamp, nama, url, area FROM pictures WHERE timestamp='$gambar'");
    $dataArray = [];
    if (mysqli_num_rows($q) > 0) {
        while ($row = mysqli_fetch_assoc($q)) {
            $dataArray[] = $row;
        }
    }
    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($dataArray);
}

if (isset($_GET['kriteria'])) {
    $kriteria = mysqli_query($koneksi, "SELECT * FROM criterias ORDER BY id ASC");
    $dataArray = [];
    while ($data = mysqli_fetch_assoc($kriteria)) {
        $dataArray[] = $data;
    }
    mysqli_close($koneksi);
    header('Content-Type: application/json');
    echo json_encode($dataArray);
}
