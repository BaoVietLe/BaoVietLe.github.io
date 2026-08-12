<?php
$servername = "sql100.ezyro.com"; // thay bằng host MySQL thực tế
$username = "ezyro_40226050";            // user do ProFreeHost cấp
$password = "24ab717f";         // password MySQL của bạn
$dbname = "ezyro_40226050_english_center"; // tên database (ProFreeHost yêu cầu có prefix)

// Kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
