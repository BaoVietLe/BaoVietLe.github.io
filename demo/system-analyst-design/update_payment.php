<?php
// update_payment.php
// Thao tác: cập nhật NgayThanhToan trong HoaDon, tăng số học viên của lớp
// Trả về JSON: {status: "success"} hoặc {status: "error", message: "..."}

header('Content-Type: application/json; charset=utf-8');
include 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Phải dùng POST']);
    exit;
}

$mahd = $_POST['mahd'] ?? '';
$class_id = $_POST['class_id'] ?? '';

if (!$mahd || !$class_id) {
    echo json_encode(['status'=>'error','message'=>'Thiếu mahd hoặc class_id']);
    exit;
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // 1) Cập nhật NgayThanhToan cho hóa đơn
    $stmt = $conn->prepare("UPDATE HoaDon SET NgayThanhToan = NOW() WHERE MaHD = ?");
    if (!$stmt) throw new Exception('Prepare HoaDon failed: '.$conn->error);
    $stmt->bind_param('s', $mahd);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Execute HoaDon failed: '.$stmt->error);
    }
    // Kiểm tra có cập nhật được hàng (nếu không có hóa đơn tương ứng thì báo lỗi)
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        throw new Exception('Không tìm thấy hóa đơn hoặc đã thanh toán trước đó.');
    }
    $stmt->close();

    // 2) Tăng số học viên cho lớp
    // Nhưng tên cột có thể khác (current_students, enrolled, cnt, ...).
    // Ta thử dò tên cột: ưu tiên 'current_students' rồi 'enrolled' rồi 'enrolled_count'
    $possibleCols = ['current_students','enrolled','enrolled_count','students','count_students'];
    $foundCol = null;
    foreach ($possibleCols as $col) {
        $check = $conn->query("SHOW COLUMNS FROM `classes` LIKE '".$conn->real_escape_string($col)."'");
        if ($check && $check->num_rows > 0) {
            $foundCol = $col;
            break;
        }
    }

    if ($foundCol === null) {
        // không tìm thấy cột dự đoán -> thử tăng 'enrolled' bằng câu lệnh bất chấp (nếu thất bại sẽ ném)
        // nhưng để an toàn, báo lỗi rõ
        throw new Exception('Không tìm thấy cột lưu số học viên trên bảng classes. Cần kiểm tra tên cột (vd: enrolled/current_students).');
    }

    // Chuẩn bị câu update tăng +1
    // Ví dụ: UPDATE classes SET enrolled = enrolled + 1 WHERE id = ?
    $sql = "UPDATE classes SET `$foundCol` = COALESCE(`$foundCol`,0) + 1 WHERE id = ?";
    $stmt2 = $conn->prepare($sql);
    if (!$stmt2) throw new Exception('Prepare classes failed: '.$conn->error);
    $stmt2->bind_param('i', $class_id);
    if (!$stmt2->execute()) {
        $stmt2->close();
        throw new Exception('Execute classes failed: '.$stmt2->error);
    }
    if ($stmt2->affected_rows === 0) {
        // có thể id lớp không tồn tại
        $stmt2->close();
        throw new Exception('Không tìm thấy lớp với id = ' . $class_id);
    }
    $stmt2->close();

    // Nếu tới đây không lỗi, commit
    $conn->commit();
    echo json_encode(['status'=>'success']);
    exit;
} catch (Exception $ex) {
    // rollback khi lỗi
    if ($conn->errno) {
        $conn->rollback();
    } else {
        // ensure rollback anyway
        $conn->rollback();
    }
    // Trả về thông báo lỗi chi tiết (dùng cho debug). Khi đưa vào production, bạn có thể rút ngắn message.
    echo json_encode(['status'=>'error','message'=> $ex->getMessage()]);
    exit;
}
