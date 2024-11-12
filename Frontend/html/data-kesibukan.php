<?php
include "../class.php";
$sinflobis = new sinflobis;
$koneksi = $sinflobis->koneksi();

if (isset($_GET['location_id'])) {
    $locationId = $_GET['location_id'];
    $sql = "SELECT day, time, busy_percentage FROM popular_times WHERE location_id = '$locationId'";
    $result = mysqli_query($koneksi, $sql);

    $popularTimes = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $popularTimes[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($popularTimes);
}

if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];
    $meanValues = [];
    if ($group_id === '0') {
        $results = mysqli_query($koneksi, "SELECT day, time, AVG(busy_percentage) AS mean_busy_percentage FROM popular_times GROUP BY day, time ORDER BY time");
        foreach ($results as $row) {
            $meanValues[] = [
                'day' => $row['day'],
                'time' => $row['time'],
                'busy_percentage' => (float)$row['mean_busy_percentage']
            ];
        }
    } else {
        // Kalibrasi data
        // $q = mysqli_query($koneksi, "SELECT member FROM location_groups WHERE id = '$group_id'");

        // $members = [];
        // if ($row = mysqli_fetch_assoc($q)) {
        //     // Split the member string into an array
        //     $members = explode(',', $row['member']);
        // }

        $q = mysqli_query($koneksi, "SELECT id FROM locations WHERE grup = '$group_id'");

        $members = [];
        while ($row = mysqli_fetch_assoc($q)) {
            // Collect all location IDs into the $members array
            $members[] = $row['id'];
        }

        if (!empty($members)) {
            $members_placeholder = implode(',', $members);
            $sql = "SELECT day, time, AVG(busy_percentage) AS mean_busy_percentage   
                    FROM popular_times   
                    WHERE location_id IN ($members_placeholder)   
                    GROUP BY day, time   
                    ORDER BY time";
            $results = mysqli_query($koneksi, $sql);
            foreach ($results as $row) {
                $meanValues[] = [
                    'day' => $row['day'],
                    'time' => $row['time'],
                    'busy_percentage' => (float)$row['mean_busy_percentage']
                ];
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($meanValues);
}

mysqli_close($koneksi);
