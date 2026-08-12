<?php 
    error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/config.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'vi';

$texts = [
    'vi' => [
        'title' => 'NĂM CHÂU CENTER',
        'subtitle' => 'SMART LEARNING',
        'login' => 'Đăng nhập',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'role' => [
            'hocvien' => 'Học viên',
            'giaovien' => 'Giáo viên',
            'qlhocvu' => 'Quản lý học vụ',
            'quantrivien' => 'Quản trị viên'
        ],
        'forgot' => 'Quên mật khẩu?',
        'back_home' => '← Quay lại trang chủ',
        'error_empty' => 'Vui lòng nhập đầy đủ thông tin!',
        'error_wrong' => 'Sai email hoặc mật khẩu!',
        'error_role' => 'Vui lòng chọn vai trò hợp lệ!'
    ],
    'en' => [
        'title' => 'NAM CHAU CENTER',
        'subtitle' => 'SMART LEARNING',
        'login' => 'Login',
        'email' => 'Email',
        'password' => 'Password',
        'role' => [
            'hocvien' => 'Student',
            'giaovien' => 'Teacher',
            'qlhocvu' => 'Academic Officer',
            'quantrivien' => 'Administrator'
        ],
        'forgot' => 'Forgot password?',
        'back_home' => '← Back to Home',
        'error_empty' => 'Please fill in all fields!',
        'error_wrong' => 'Incorrect email or password!',
        'error_role' => 'Please select a valid role!'
    ]
];
$t = $texts[$lang];
// ======= Hàm lọc dữ liệu nhập vào =======
function inputdata($data) {
    $data = trim($data);              // Loại bỏ khoảng trắng đầu/cuối
    $data = stripslashes($data);      // Xóa ký tự escape "\"
    $data = htmlspecialchars($data);  // Chống XSS (hiển thị ký tự HTML an toàn)
    return $data;
}


// ======= Xử lý đăng nhập =======
if (isset($_POST['dangnhap'])) {
    $email = inputdata($_POST['email']);
    $matkhau = inputdata($_POST['matkhau']);
    $vaitro = inputdata($_POST['vaitro']);

    if (empty($email) || empty($matkhau)) {
        $error = $t['error_empty'];
    } else {
        // Lấy thông tin người dùng từ email
        $sql = "SELECT NguoiDung.*, TaiKhoan.MatKhau 
                FROM NguoiDung 
                JOIN TaiKhoan ON NguoiDung.MaTK = TaiKhoan.MaTK
                WHERE NguoiDung.Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();

            if ($user['MatKhau'] === $matkhau) {
                // Kiểm tra vai trò
                $tableRole = '';
                $colMaNguoiDung = 'MaNguoiDung';
                switch ($vaitro) {
                    case 'hocvien': $tableRole = 'HocVien'; break;
                    case 'giaovien': $tableRole = 'GiaoVien'; break;
                    case 'qlhocvu': $tableRole = 'QuanLyHocVu'; break;
                    case 'quantrivien': $tableRole = 'QuanTriVien'; break;
                    default: $error = $t['error_role'];
                }

                if ($tableRole) {
                    $check = $conn->prepare("SELECT * FROM $tableRole WHERE MaNguoiDung=?");
                    $check->bind_param("s", $user['MaNguoiDung']);
                    $check->execute();
                    $r = $check->get_result();

                    if ($r->num_rows > 0) {
                      $_SESSION['VaiTro'] = $vaitro;
$_SESSION['HoTen'] = $user['HoTen'];
$_SESSION['Email'] = $user['Email'];
$_SESSION['MaNguoiDung'] = $user['MaNguoiDung'];

switch ($vaitro) {
    case 'hocvien':
        header("Location:hocvien.php");
        break;
    case 'giaovien':
        header("Location:developing.html");
        break;
    case 'qlhocvu':
        header("Location:developing.html");
        break;
    case 'quantrivien':
        header("Location:developing.html");
        break;
    default:
        header("Location:index.html");
}
exit();

                    } else {
                        $error = $t['error_role'];
                    }
                }
            } else {
                $error = $t['error_wrong'];
            }
        } else {
            $error = $t['error_wrong'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<title><?= $t['title'] ?> - <?= $t['subtitle'] ?></title>
<style>
body { 
    background: url('Group 02.png') no-repeat center center/cover;
    font-family: 'Poppins', sans-serif;
    display: flex; align-items: center; justify-content: center;
    height: 100vh; margin: 0;
}
.login-box {
    width: 420px; background:url('UC1. Đăng nhập') no-repeat center center/cover;
    border-radius: 25px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    padding: 40px 35px; text-align: center; position: relative;
    backdrop-filter: blur(5px);
}
.login-box h2 { color: #1e40af; font-weight: 700; margin: 0; }
.login-box h4 { color: #dc2626; font-weight: 600; margin-bottom: 25px; }
.roles { display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 15px; }
.roles button {
    flex: 1; margin: 3px; border: none; background: #e0f2fe;
    color: #2563eb; font-weight: 500; border-radius: 15px;
    padding: 8px 0; cursor: pointer; transition: 0.2s;
}
.roles button:hover { background: #bfdbfe; }
.roles button.active { background: #2563eb; color: white; }
input {
    width: 100%; padding: 10px; border: 1px solid #cbd5e1;
    border-radius: 10px; margin-bottom: 15px; font-size: 14px;
}
.btn {
    background: #2563eb; color: white; border: none;
    padding: 10px; border-radius: 10px; width: 100%;
    cursor: pointer; font-weight: 600;
}
.btn:hover { background: #1d4ed8; }
.lang {
    position: absolute; top: 15px; right: 15px; display: flex; gap: 10px;
}
.lang a { text-decoration: none; }
.lang img { width: 26px; height: 18px; border-radius: 3px; border: 1px solid #d1d5db; transition: 0.2s; }
.lang img:hover { transform: scale(1.1); }
.error { color: red; font-size: 14px; margin-top: 10px; }
a { color: #2563eb; font-size: 13px; text-decoration: none; }
a:hover { text-decoration: underline; }
.back-home {
    position: absolute; top: 15px; left: 20px;
    color: #2563eb; font-size: 14px; text-decoration: none; font-weight: 500;
}
.back-home:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="login-box">
    <a href="index.html" class="back-home"><?= $t['back_home'] ?></a>
    <div class="lang">
        <a href="?lang=vi"><img src="https://flagcdn.com/w20/vn.png" alt="VN"></a>
        <a href="?lang=en"><img src="https://flagcdn.com/w20/gb.png" alt="EN"></a>
    </div>
    <h2><?= $t['title'] ?></h2>
    <h4><?= $t['subtitle'] ?></h4>

    <form method="POST">
        <div class="roles">
            <input type="hidden" name="vaitro" id="vaitro" value="hocvien">
            <?php foreach ($t['role'] as $key => $val): ?>
                <button type="button" onclick="setRole('<?= $key ?>', event)" <?= $key==='hocvien'?'class="active"':'' ?>><?= $val ?></button>
            <?php endforeach; ?>
        </div>

        <input type="email" name="email" placeholder="<?= $t['email'] ?>" required>
        <input type="password" name="matkhau" placeholder="<?= $t['password'] ?>" required>
        <button type="submit" name="dangnhap" class="btn"><?= $t['login'] ?></button>

        <div style="margin-top:10px;">
            <a href="#"><?= $t['forgot'] ?></a>
        </div>

        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    </form>
</div>

<script>
function setRole(role, e) {
    document.getElementById('vaitro').value = role;
    const btns = document.querySelectorAll('.roles button');
    btns.forEach(b => b.classList.remove('active'));
    e.target.classList.add('active');
}
</script>
</body>
</html>
