<?php
include "../class.php";
$sinflobis = new sinflobis;
$koneksi = $sinflobis->koneksi();

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
