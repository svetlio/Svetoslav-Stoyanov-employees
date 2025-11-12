<?php
session_start();
$target_dir = './uploads/';
$file_name = basename($_FILES['fileToUpload']['name']);

$ext = pathinfo($file_name, PATHINFO_EXTENSION);
if ($ext != 'csv') {
  $_SESSION['error'] = 'Please upload a csv file';
  header('Location: index.php');
  die();
}
unset($_SESSION['error']);
$target_file = $target_dir . $file_name;
move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file);
$_SESSION['file_name'] = $file_name;
header('Location: result.php');
die();
