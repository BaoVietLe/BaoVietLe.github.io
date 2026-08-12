<?php
include 'config/config.php';
mysqli_set_charset($conn, "utf8");

// Thêm lớp học
if (isset($_POST['add_class'])) {
    $course_id = $_POST['course_id'];
    $class_name = $_POST['class_name'];
    $teacher = $_POST['teacher'];
    $schedule = $_POST['schedule'];
    $enrolled = $_POST['enrolled'];
    $max_students = $_POST['max_students'];

    $sql = "INSERT INTO classes (course_id, class_name, teacher, schedule, enrolled, max_students)
            VALUES ('$course_id', '$class_name', '$teacher', '$schedule', '$enrolled', '$max_students')";
    mysqli_query($conn, $sql);
    header("Location: quanlylophoc.php");
    exit;
}

// Xóa lớp học
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM classes WHERE id = $id");
    header("Location: quanlylophoc.php");
    exit;
}

// Lấy danh sách lớp học kèm tên khóa học
$sql = "
SELECT c.id, c.class_name, c.teacher, c.schedule, c.enrolled, c.max_students, 
       co.name AS course_name, co.level, co.goal
FROM classes c
JOIN courses co ON c.course_id = co.id
ORDER BY c.id DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý lớp học</title>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f9faff;
    margin: 0;
    padding: 0;
}
.container {
    width: 90%;
    max-width: 1200px;
    margin: 40px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 30px;
}
h2 {
    color: #4a4ae6;
    text-align: center;
    margin-bottom: 10px;
}
p {
    text-align: center;
    color: #555;
    margin-bottom: 30px;
}
.table-container {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table thead {
    background: #e8eaff;
    color: #333;
}
table th, table td {
    padding: 12px 16px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}
tr:nth-child(even) {
    background-color: #f7f8ff;
}
.action-btn {
    text-decoration: none;
    margin: 0 5px;
    padding: 6px 10px;
    border-radius: 8px;
    font-weight: bold;
    color: white;
}
.edit { background: #4a90e2; }
.delete { background: #e74c3c; }
.add-btn {
    display: inline-block;
    background: #4a4ae6;
    color: white;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    margin-bottom: 15px;
}
form.add-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    background: #f2f3ff;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 25px;
}
form.add-form input, form.add-form select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
form.add-form button {
    background: #4a4ae6;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    cursor: pointer;
}
</style>
</head>
<body>
<div class="container">
    <h2>QUẢN LÝ LỚP HỌC</h2>
    <p>Thêm, sửa, xóa và xem danh sách lớp học một cách nhanh chóng</p>

    <form method="POST" class="add-form">
        <select name="course_id" required>
            <option value="">-- Chọn khóa học --</option>
            <?php
            $courses = mysqli_query($conn, "SELECT * FROM courses");
            while ($row = mysqli_fetch_assoc($courses)) {
                echo "<option value='{$row['id']}'>{$row['name']} ({$row['level']})</option>";
            }
            ?>
        </select>
        <input type="text" name="class_name" placeholder="Tên lớp" required>
        <input type="text" name="teacher" placeholder="Giáo viên" required>
        <input type="text" name="schedule" placeholder="Lịch học (VD: T2-T4-CN)" required>
        <input type="number" name="enrolled" placeholder="Sĩ số hiện tại" min="0" required>
        <input type="number" name="max_students" placeholder="Tối đa" min="1" required>
        <button type="submit" name="add_class">+ Thêm lớp học</button>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên lớp</th>
                    <th>Khóa học</th>
                    <th>Giáo viên</th>
                    <th>Lịch học</th>
                    <th>Sĩ số</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?> - <?= $row['level'] ?></td>
                    <td><?= htmlspecialchars($row['teacher']) ?></td>
                    <td><?= htmlspecialchars($row['schedule']) ?></td>
                    <td><?= $row['enrolled'] ?>/<?= $row['max_students'] ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="action-btn edit">✏</a>
                        <a href="?delete=<?= $row['id'] ?>" class="action-btn delete" onclick="return confirm('Xóa lớp này?')">🗑</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
