<?php include './config/config.php'; ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Chi tiết khóa học</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f5f7fb;font-family:Inter,Arial,sans-serif}
    .course-box{background:#fff;border-radius:14px;box-shadow:0 8px 30px rgba(16,24,40,0.06);padding:26px}
    .hero-img{width:100%;border-radius:10px;object-fit:cover}
  </style>
</head>
<body>
  <header class="p-3 bg-white border-bottom">
    <a href="UC3-1-1.php" class="btn btn-light">←</a>
    <span class="fw-bold ms-2">THÔNG TIN KHÓA HỌC</span>
  </header>

  <main class="container my-4">
    <?php
    $id = $_GET['id'];
    $sql = "SELECT * FROM courses WHERE id=$id";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
      echo "
      <div class='course-box'>
        <div class='row'>
          <div class='col-md-8'>
            <h3 class='fw-bold mb-2'>{$row['name']}</h3>
            <p class='text-muted'>{$row['description']}</p>
            <p><strong>Địa điểm:</strong> {$row['address']}</p>
            <p><strong>Thời lượng:</strong> {$row['duration']}</p>
            <a href='UC3-1-4.php?course_id={$row['id']}' class='btn btn-outline-primary mt-3'>Xem danh sách lớp</a>
          </div>
          <div class='col-md-4'>
            <img src='{$row['img']}' class='hero-img'>
            <p class='mt-3 fw-bold text-primary'>{$row['price']}</p>
          </div>
        </div>
      </div>";
    } else {
      echo "<p>Không tìm thấy khóa học.</p>";
    }
    ?>
  </main>
</body>
</html>
