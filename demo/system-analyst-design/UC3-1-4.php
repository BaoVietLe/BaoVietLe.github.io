<?php
// UC3-1-4.php
include './config/config.php';
session_start();

/*
 Giả định DB có các bảng:
 - courses, classes
 - HocVien(MaHV, TenHV, ...)
 - PhieuGhiDanh(MaPGD, NgayGhiDanh, MaLop, MaHV)
 - HoaDon(MaHD, NgayThanhToan, TongTien, MaPGD)
*/

// Giả sử login đã lưu:
if (!isset($_SESSION['mahv'])) {
    $_SESSION['mahv'] = 'HV12345';
    $_SESSION['tenhv'] = 'Nguyễn Văn A';
}

// Helper sinh mã
function gen_code($prefix='X', $len=6){
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $s = '';
    for ($i=0; $i<$len; $i++) $s .= $chars[random_int(0, strlen($chars)-1)];
    return $prefix . $s;
}

// ---- Handle AJAX ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    // Đăng ký lớp học
    if ($action === 'register') {
        if (!isset($_SESSION['mahv'])) {
            echo json_encode(['status'=>'error','message'=>'Bạn chưa đăng nhập.']);
            exit;
        }

        $maHV = $_SESSION['mahv'];
        $class_id = intval($_POST['class_id'] ?? 0);

        if ($class_id <= 0) {
            echo json_encode(['status'=>'error','message'=>'Thiếu thông tin lớp học.']);
            exit;
        }

        // Tạo phiếu ghi danh
        $maPGD = gen_code('PGD',6);
        $ngay = date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO PhieuGhiDanh (MaPGD, NgayGhiDanh, MaLop, MaHV) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssis', $maPGD, $ngay, $class_id, $maHV);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            echo json_encode(['status'=>'ok','mapgd'=>$maPGD, 'mahv'=>$maHV]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Lỗi khi tạo ghi danh: '.$conn->error]);
        }
        exit;
    }

    // Tạo hoá đơn
    if ($action === 'create_invoice') {
        $mapgd = trim($_POST['mapgd'] ?? '');
        if ($mapgd === '') {
            echo json_encode(['status'=>'error','message'=>'Thiếu MaPGD']);
            exit;
        }

        // Lấy MaLop & Giá
        $stmt = $conn->prepare("
            SELECT p.MaLop, cr.price
            FROM PhieuGhiDanh p
            JOIN classes cl ON cl.id = p.MaLop
            LEFT JOIN courses cr ON cr.id = cl.course_id
            WHERE p.MaPGD = ? LIMIT 1
        ");
        $stmt->bind_param('s', $mapgd);
        $stmt->execute();
        $stmt->bind_result($malop, $price_str);
        $found = $stmt->fetch();
        $stmt->close();

        if (!$found) {
            echo json_encode(['status'=>'error','message'=>'Không tìm thấy phiếu ghi danh.']);
            exit;
        }

        $price = $price_str ?: '0';

        $maHD = gen_code('HD',6);
        $stmt = $conn->prepare("INSERT INTO HoaDon (MaHD, NgayThanhToan, TongTien, MaPGD) VALUES (?, NULL, ?, ?)");
        $stmt->bind_param('sss', $maHD, $price, $mapgd);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            echo json_encode(['status'=>'ok','mahd'=>$maHD]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Lỗi tạo hóa đơn: '.$conn->error]);
        }
        exit;
    }

    echo json_encode(['status'=>'error','message'=>'Action không hợp lệ']);
    exit;
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Danh sách lớp học</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {background:#f8faff;font-family:Inter,Arial,sans-serif}
    header {background:#fff;border-bottom:1px solid #e5e9f2;padding:12px 20px;}
    h2 {color:#0d6efd;font-weight:700;margin-top:20px;margin-bottom:20px;text-align:center;}
    table {background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.05);}
    th {background:#0d6efd;color:#fff;text-align:center;}
    td {vertical-align:middle;text-align:center;}
    .btn-register {background:#ff8c00;border:none;color:#fff;padding:6px 12px;border-radius:6px;}
    .btn-register:hover {background:#e07b00;}
  </style>
</head>
<body>
<header class="d-flex justify-content-between align-items-center">
  <a href="UC3-1-3.php" class="btn btn-light">←</a>
  <h6 class="fw-bold mb-0">THÔNG TIN LỚP HỌC</h6>
  <div class="d-flex align-items-center gap-2">
    <img src="https://picsum.photos/seed/avatar2/36" class="rounded-circle" alt="user">
    <small class="text-muted"><?=htmlspecialchars($_SESSION['tenhv'])?></small>
  </div>
</header>

<main class="container my-4">
  <h2>Danh sách lớp học</h2>
  <div class="text-end mb-3">
    <a href="UC3-1-3.php" class="btn btn-outline-primary">Quay lại khóa học</a>
  </div>

  <table class="table table-bordered table-striped align-middle">
    <thead>
      <tr>
        <th>Mã lớp</th>
        <th>Tên lớp</th>
        <th>Giảng viên</th>
        <th>Lịch học</th>
        <th>Đã ghi danh</th>
        <th>Tối đa</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if (isset($_GET['course_id'])) {
        $course_id = intval($_GET['course_id']);
        $sql = "SELECT * FROM classes WHERE course_id=$course_id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
              <td>{$row['id']}</td>
              <td>{$row['class_name']}</td>
              <td>{$row['teacher']}</td>
              <td>{$row['schedule']}</td>
              <td>{$row['enrolled']}</td>
              <td>{$row['max_students']}</td>
              <td><button class='btn-register' data-id='{$row['id']}' data-name='".htmlspecialchars($row['class_name'],ENT_QUOTES)."'>Đăng ký</button></td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='7'>Không có lớp học nào cho khóa này.</td></tr>";
        }
      } else {
        echo "<tr><td colspan='7'>Thiếu thông tin khóa học.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</main>

<!-- Modal Đăng ký -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-primary fw-bold">Đăng ký lớp học</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="registerForm">
          <input type="hidden" id="reg_class_id" name="class_id">
          <div class="mb-3">
            <label class="form-label">Học viên:</label>
            <input type="text" class="form-control" id="reg_fullname" readonly value="<?=htmlspecialchars($_SESSION['tenhv'])?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Lớp học:</label>
            <input type="text" class="form-control" id="reg_class_name" readonly>
          </div>
        </form>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-primary" id="reg_confirm_btn">Xác nhận đăng ký</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Thành công -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body p-4">
        <h5 class="text-success fw-bold mb-3">🎉 Đăng ký thành công!</h5>
        <p>Bạn đã đăng ký lớp học thành công. Bạn có muốn thanh toán ngay không?</p>
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Để sau</button>
          <button type="button" class="btn btn-success" id="pay_now_btn">Xác nhận thanh toán</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
const successModal = new bootstrap.Modal(document.getElementById('successModal'));
let lastCreatedPGD = null;

document.querySelectorAll('.btn-register').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('reg_class_id').value = btn.dataset.id;
    document.getElementById('reg_class_name').value = btn.dataset.name;
    registerModal.show();
  });
});

document.getElementById('reg_confirm_btn').addEventListener('click', () => {
  const classId = document.getElementById('reg_class_id').value;
  const fd = new FormData();
  fd.append('class_id', classId);

  fetch('?action=register', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(j => {
      if (j.status === 'ok') {
        lastCreatedPGD = j.mapgd || null;
        registerModal.hide();
        successModal.show();
      } else alert('Lỗi: ' + (j.message || 'Không thể ghi danh'));
    }).catch(e => alert('Lỗi mạng'));
});

document.getElementById('pay_now_btn').addEventListener('click', () => {
  if (!lastCreatedPGD) { alert('Không tìm thấy phiếu ghi danh'); return; }
  const fd = new FormData();
  fd.append('mapgd', lastCreatedPGD);
  fetch('?action=create_invoice', { method:'POST', body:fd })
    .then(r=>r.json())
    .then(j=>{
      if(j.status==='ok') window.location.href='UC3-1-5.php';
      else alert('Lỗi: '+(j.message||''));
    });
});
</script>
</body>
</html>
