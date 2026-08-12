<?php
    error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/config.php';

$maHV = $_SESSION['MaHV'] ?? null; // Mã học viên đang đăng nhập

if ($maHV) {
    // Câu truy vấn lấy tên khoá học mà học viên đã ghi danh
    $sql = "
        SELECT c.name AS TenKhoaHoc, cl.class_name AS TenLop, pgd.NgayGhiDanh
        FROM PhieuGhiDanh pgd
        JOIN classes cl ON pgd.MaLop = cl.id
        JOIN courses c ON cl.course_id = c.id
        WHERE pgd.MaHV = ?
        ORDER BY pgd.NgayGhiDanh DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $maHV);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $tenKhoaHoc = htmlspecialchars($row['TenKhoaHoc']);
        $tenLop = htmlspecialchars($row['TenLop']);
        $ngayGhiDanh = htmlspecialchars($row['NgayGhiDanh']);
    } else {
        $tenKhoaHoc = null;
    }
} else {
    $tenKhoaHoc = null;
}

// Giả sử login đã lưu MaNguoiDung vào session:
$maNguoiDung = $_SESSION['MaNguoiDung'];

$sql = "SELECT n.HoTen, n.Email, n.SDT, n.NgaySinh 
        FROM NguoiDung n 
        JOIN HocVien hv ON n.MaNguoiDung = hv.MaNguoiDung 
        WHERE n.MaNguoiDung = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $maNguoiDung);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Dashboard Học viên</title>
<link href="https://fonts.googleapis.com/css2?family=Montseratt:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    * {
  box-sizing: border-box;
}
body {
  font-family: 'Montseratt', sans-serif;
  background: linear-gradient(to bottom right, #dbeafe, #eef2ff);
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  overflow-x: hidden;
}

/* Header */
.header {
  width: 100%;
  background: #e0e7ff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 60px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.header-left button, .header-right button {
  border: none;
  background: white;
  border-radius: 10px;
  width: 40px; height: 40px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  transition: 0.2s;
}
.header-left button:hover, .header-right button:hover {
  transform: scale(1.05);
}
.header-right {
  display: flex; align-items: center; gap: 18px;
}
.header-right img {
  width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
}
.header-right span {
  color: #1d4ed8; font-weight: 600; cursor: pointer; font-size: 16px;
}

/* Dashboard Layout */
.container {
  width: 90%;
  max-width: 1400px;
  margin: 40px auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
}

/* Card Style */
.card {
  background: white;
  border-radius: 18px;
  padding: 25px;
  box-shadow: 0 5px 16px rgba(0,0,0,0.1);
  transition: transform 0.25s ease;
}
.card:hover {
  transform: translateY(-4px);
}
.card h3 {
  color: #1e3a8a;
  font-size: 18px;
  margin-bottom: 15px;
}
.time-box {
  background: #f1f5f9;
  border-radius: 12px;
  padding: 10px 14px;
  margin: 6px 0;
  font-size: 15px;
}

/* Nút */
.btn {
  display: block;
  text-align: center;
  border-radius: 10px;
  padding: 10px;
  font-weight: 600;
  text-decoration: none;
  margin-top: 12px;
  transition: 0.25s;
}
.btn-blue {
  background: linear-gradient(to right, #3b82f6, #60a5fa);
  color: white;
}
.btn-red {
  background: linear-gradient(to right, #f87171, #fbbf24);
  color: white;
}
.btn:hover {
  opacity: 0.9;
  transform: scale(1.02);
}

/* Chart đẹp và to */
.chart {
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  gap: 20px;
  height: 200px;
  background: linear-gradient(to top, #f9fafb, #e0f2fe);
  border-radius: 20px;
  padding: 20px;
  box-shadow: inset 0 3px 10px rgba(0,0,0,0.05);
}
.chart div {
  width: 50px;
  border-radius: 12px 12px 0 0;
  background: linear-gradient(180deg, #60a5fa, #2563eb);
  box-shadow: 0 6px 14px rgba(59,130,246,0.4);
  transition: all 0.4s ease;
}

/* Thông tin cá nhân */
.card.profile {
  grid-column: span 2;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card.profile img {
  width: 120px; height: 120px;
  border-radius: 50%; object-fit: cover;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
/* Responsive */
@media (max-width: 700px) {
  .profile-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
</head>

<body>
<div class="header">
  <div class="header-left">
    <button onclick="history.back()">←</button>
  </div>
  <div class="header-right">
    <button>🏠</button>
    <button>⚙️</button>
    <button>🔍</button>
    <img src="https://picsum.photos/seed/avatar/40" alt="avatar">
    <span>Học viên</span>
  </div>
</div>

<div class="container">
  <!-- LỊCH HỌC -->
  <div class="card">
    <h3>📅 LỊCH HỌC</h3>
    <div class="time-box"><b>Hôm nay</b></div>
    <div class="time-box">7:00 - 8:30 AM</div>
    <div class="time-box" style="background:#fee2e2;">12h - 13h30 PM</div>
    <div class="time-box"><b>21/10/2025</b></div>
    <div class="time-box">15:00 - 16:30 PM</div>
  </div>

  <!-- KHÓA HỌC -->
  <div class="card">
    <h3>🧭 THÔNG TIN KHÓA HỌC</h3>
<div class="card shadow-lg p-4 mt-4 rounded-4" style="background: linear-gradient(135deg, #e8f0ff, #ffffff);">
  <h4 class="fw-bold text-primary mb-3">
    <i class="fa-solid fa-book-open-reader me-2"></i> Thông tin khoá học
  </h4>

  <?php if ($tenKhoaHoc): ?>
    <div class="p-3 border rounded-3 bg-white">
      <p><strong>Tên khoá học:</strong> <?= $tenKhoaHoc ?></p>
      <p><strong>Lớp học:</strong> <?= $tenLop ?></p>
      <p><strong>Ngày ghi danh:</strong> <?= $ngayGhiDanh ?></p>
      <p class="text-success fw-bold"><i class="fa-solid fa-check-circle me-1"></i> Đang theo học</p>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center rounded-3">
      <i class="fa-solid fa-triangle-exclamation me-2"></i>
      Chưa ghi danh khoá học nào.
    </div>
  <?php endif; ?>
</div>
    <a href="UC3-1-1.php" class="btn btn-blue">Đăng ký khóa học</a>
     <a href="UC3-1-5.php" class="btn btn-blue">Xem lịch sử thanh toán</a>
  </div>

  <!-- BÀI KIỂM TRA -->
  <div class="card">
    <h3>📎 BÀI KIỂM TRA</h3>
    <a href="developing.html" class="btn btn-blue">Lịch sử kiểm tra</a>
    <a href="developing.html" class="btn btn-red">Tham gia kiểm tra</a>
  </div>

  <!-- TIẾN ĐỘ -->
  <div class="card">
    <h3>📊 TIẾN ĐỘ</h3>
    <div class="chart">
      <div style="height:50px;"></div>
      <div style="height:70px;"></div>
      <div style="height:90px;"></div>
      <div style="height:65px;"></div>
      <div style="height:85px;"></div>
    </div>
  </div>

  <!-- THÔNG TIN CÁ NHÂN -->
  <div class="card" style="grid-column: span 2; display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h3>👤 THÔNG TIN CÁ NHÂN</h3>
<p>Tên: <b><?= htmlspecialchars($user['HoTen']) ?></b></p>
<p>Email: <b><?= htmlspecialchars($user['Email']) ?></b></p>

      <a href="developing.html" class="btn btn-blue">Chỉnh sửa</a>
    </div>
    <img src="https://picsum.photos/seed/avatar/40" alt="avatar" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
  </div>
</div>
</body>
</html>
