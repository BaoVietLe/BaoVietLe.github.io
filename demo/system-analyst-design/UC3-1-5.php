<?php include 'config/config.php'; ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Thanh toán học phí</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {background:#f5f7fb;font-family:'Inter',sans-serif;}
    .table th {background:#0d6efd;color:#fff;text-align:center;}
    .table td {text-align:center;vertical-align:middle;}
    .btn-pay {background:#007bff;color:#fff;border:none;padding:6px 12px;border-radius:8px;}
    .btn-pay:hover {background:#0056b3;}
    .modal-content {border-radius:16px;}
    .amount {font-size:1.8rem;font-weight:700;margin:16px 0;color:#000;}
    .success-icon {font-size:90px;color:#28a745;margin:20px 0;}
  </style>
</head>
<body>

<header class="d-flex justify-content-between align-items-center p-3 bg-white border-bottom">
  <a href="UC3-1-4.php" class="btn btn-light">←</a>
  <h5 class="fw-bold mb-0 text-primary">THANH TOÁN HỌC PHÍ</h5>
  <div class="d-flex align-items-center gap-2">
    <img src="https://picsum.photos/seed/avatar3/36" class="rounded-circle" alt="user">
    <small class="text-muted">Học viên</small>
  </div>
</header>

<main class="container my-4">
  <h4 class="text-center fw-bold mb-4 text-secondary">DANH SÁCH KHÓA HỌC CHỜ THANH TOÁN</h4>
  <table class="table table-bordered table-striped align-middle shadow-sm">
    <thead>
      <tr>
        <th>Mã hóa đơn</th>
        <th>Mã phiếu ghi danh</th>
        <th>Khóa học</th>
        <th>Giá tiền</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = "
        SELECT hd.MaHD, hd.NgayThanhToan, pgd.MaPGD, c.class_name, cr.name AS course_name, cr.price, c.id AS class_id
        FROM HoaDon hd
        JOIN PhieuGhiDanh pgd ON hd.MaPGD = pgd.MaPGD
        JOIN classes c ON pgd.MaLop = c.id
        JOIN courses cr ON c.course_id = cr.id
        WHERE hd.NgayThanhToan IS NULL
        ORDER BY hd.MaHD DESC
      ";
      $result = $conn->query($sql);
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "
          <tr>
            <td>{$row['MaHD']}</td>
            <td>{$row['MaPGD']}</td>
            <td>{$row['course_name']} - {$row['class_name']}</td>
            <td>{$row['price']}</td>
            <td><span class='text-warning fw-bold'>Chờ thanh toán</span></td>
            <td><button class='btn-pay' data-id='{$row['MaHD']}' data-class='{$row['class_id']}' data-price='{$row['price']}'>Thanh toán</button></td>
          </tr>";
        }
      } else {
        echo "<tr><td colspan='6'>Không có hóa đơn cần thanh toán.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</main>

<!-- Modal chung (QR + tick) -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <button type="button" class="btn-close position-absolute end-0 me-3 mt-3" data-bs-dismiss="modal"></button>
      <h5 class="fw-bold mb-2 text-dark">Thanh toán học phí</h5>
      <p class="text-muted small mb-1">Mã thanh toán: <span id="maHD">XXXXXXX</span></p>
      <p class="text-muted small">Mã lớp học: <span id="maLop">XXXXXXX</span></p>
      <div id="amount" class="amount">0 VNĐ</div>

      <div id="qrSection">
        <img id="qrImage" src="" alt="QR Code" width="200" height="200" class="rounded shadow-sm mb-3">
        <div class="d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Để sau</button>
          <button type="button" class="btn btn-primary" id="confirmPayBtn">Kiểm tra trạng thái</button>
        </div>
      </div>

      <div id="successSection" class="d-none">
        <div class="success-icon">✔</div>
        <h5 class="text-success fw-bold mb-3">Thanh toán thành công!</h5>
        <p>Bạn đã trở thành học viên chính thức của lớp học này.</p>
        <button type="button" class="btn btn-primary" onclick="location.reload()">In hóa đơn</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let selectedHD = null;
let selectedClass = null;
let selectedPrice = null;

const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
const qrSection = document.getElementById('qrSection');
const successSection = document.getElementById('successSection');

document.querySelectorAll('.btn-pay').forEach(btn => {
  btn.addEventListener('click', () => {
    selectedHD = btn.dataset.id;
    selectedClass = btn.dataset.class;
    selectedPrice = btn.dataset.price;

    document.getElementById('maHD').textContent = selectedHD;
    document.getElementById('maLop').textContent = selectedClass;
    document.getElementById('amount').textContent = selectedPrice + " VNĐ";
    document.getElementById('qrImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ThanhToan_${selectedHD}`;

    qrSection.classList.remove('d-none');
    successSection.classList.add('d-none');
    paymentModal.show();
  });
});

document.getElementById('confirmPayBtn').addEventListener('click', () => {
  // disable button để tránh bấm nhiều lần
  const btn = document.getElementById('confirmPayBtn');
  btn.disabled = true;
  btn.textContent = 'Đang xử lý...';

  fetch('update_payment.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `mahd=${encodeURIComponent(selectedHD)}&class_id=${encodeURIComponent(selectedClass)}`
  })
  .then(res => res.json())
  .then(obj => {
    btn.disabled = false;
    btn.textContent = 'Kiểm tra thanh toán';
    if (obj.status === 'success') {
      // chuyển modal sang success
      qrSection.classList.add('d-none');
      successSection.classList.remove('d-none');
    } else {
      // Hiển thị lỗi chi tiết giúp debug
      const msg = obj.message || 'Thanh toán thất bại';
      alert('Lỗi khi thanh toán: ' + msg);
      console.error('update_payment error:', msg);
    }
  })
  .catch(err => {
    btn.disabled = false;
    btn.textContent = 'Thanh toán';
    alert('Lỗi mạng hoặc lỗi server. Mở DevTools -> Network để xem chi tiết.');
    console.error('Fetch error:', err);
  });
});
    
    document.getElementById('btnPrintInvoice').addEventListener('click', () => {
  window.print();
});

</script>

</body>
</html>
