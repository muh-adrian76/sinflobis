<?php
class sinflobis
{
  public function koneksi()
  {
    error_reporting(1); //untuk mematikan detail laporan error  
    //koneksi
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "sinflobis";
    // $user="u1573365_telkom";
    // $pass="*t3lk0m#2023";
    // $db = "u1573365_digilib_11";


    $koneksi = mysqli_connect($host, $user, $pass, $db);
    // if(!$koneksi) echo 'koneksi gagal'.mysqli_error($koneksi);
    if (!$koneksi) return mysqli_connect_error();
    else return $koneksi;
  }
  public function nim_to_nama($nim)
  {
    $q = mysqli_query($this->koneksi(), "SELECT nama FROM mhs WHERE nim='$nim'");
    $d = mysqli_fetch_row($q);
    if (!empty($d[0])) {
      $nama = strtolower($d[0]);
      $namaMhs = ucwords($nama);
      return $namaMhs;
    }
  }
  public function nip_to_nama($nip)
  {
    $q = mysqli_query($this->koneksi(), "SELECT gelar_depan,nama,gelar_belakang FROM dosen WHERE nip='$nip'");
    $d = mysqli_fetch_row($q);
    if (!empty($d[1])) {
      $nama = strtolower($d[1]);
      $namaDosen = $d[0] . ' ' . ucwords($nama) . ', ' . $d[2];
      return $namaDosen;
    }
  }
}

class database
{
  public function koneksi()
  {
    error_reporting(1); //untuk mematikan detail laporan error  
    //koneksi
    $host = "localhost";
    $user = "root";
    $pass = "";
    // $db="digilib_tk";
    // $user="u1573365_telkom";
    // $pass="*t3lk0m#2023";
    $db = "bioflok";

    $koneksi = mysqli_connect($host, $user, $pass, $db);
    // if(!$koneksi) echo 'koneksi gagal'.mysqli_error($koneksi);
    if (!$koneksi) return mysqli_connect_error();
    else return $koneksi;
  }
  public function nim_to_nama($nim)
  {
    $q = mysqli_query($this->koneksi(), "SELECT nama FROM mhs WHERE nim='$nim'");
    $d = mysqli_fetch_row($q);
    if (!empty($d[0])) {
      $nama = strtolower($d[0]);
      $namaMhs = ucwords($nama);
      return $namaMhs;
    }
  }
  public function nip_to_nama($nip)
  {
    $q = mysqli_query($this->koneksi(), "SELECT gelar_depan,nama,gelar_belakang FROM dosen WHERE nip='$nip'");
    $d = mysqli_fetch_row($q);
    if (!empty($d[1])) {
      $nama = strtolower($d[1]);
      $namaDosen = $d[0] . ' ' . ucwords($nama) . ', ' . $d[2];
      return $namaDosen;
    }
  }
}
