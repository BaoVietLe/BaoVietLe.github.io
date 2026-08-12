<?php
    include 'config.php';
    $insert = "
	INSERT INTO courses (name, level, goal, price, duration, address, img, description)
    VALUES
    ('Tiếng Anh Giao Tiếp Cơ Bản', 'Beginner', 'Giao tiếp hằng ngày', '2.500.000 VND', '8 tuần', 'Cơ sở Nguyễn Văn Cừ', 'img/course1.jpg', 'Khóa học giúp người 	mới bắt đầu tự tin nói tiếng Anh trong các tình huống cơ bản.'),
    ('Tiếng Anh Trung Cấp', 'Intermediate', 'Phát triển kỹ năng giao tiếp', '3.000.000 VND', '10 tuần', 'Cơ sở Võ Văn Tần', 'img/course2.jpg', 'Khóa học giúp nâng 		cao khả năng giao tiếp và phản xạ tự nhiên.'),
    ('Tiếng Anh Nâng Cao', 'Advanced', 'Hoàn thiện 4 kỹ năng', '3.500.000 VND', '12 tuần', 'Cơ sở Quang Trung', 'img/course3.jpg', 'Khóa học chuyên sâu, hướng tới 		sự tự tin và lưu loát khi sử dụng tiếng Anh.'),
    ('Luyện Thi IELTS', 'Advanced', 'Đạt 6.5+ IELTS', '4.500.000 VND', '14 tuần', 'Cơ sở D2 Bình Thạnh', 'img/course4.jpg', 'Khóa luyện thi chuyên biệt giúp học 		viên đạt band điểm mục tiêu.'),
    ('Tiếng Anh Cho Người Đi Làm', 'Upper-Intermediate', 'Giao tiếp công sở', '3.200.000 VND', '10 tuần', 'Cơ sở Quận 1', 'img/course5.jpg', 'Tập trung vào kỹ 		năng giao tiếp và viết email trong môi trường làm việc.');
 INSERT INTO classes (course_id, class_name, teacher, schedule, enrolled, max_students)
    VALUES
    (1, 'Basic English A1', 'Cô Anna', 'Thứ 2 - 4 - 6 (18h - 19h30)', 18, 30),
    (1, 'Basic English A2', 'Thầy David', 'Thứ 3 - 5 - 7 (17h - 18h30)', 25, 30),
    (2, 'Intermediate B1', 'Cô Sarah', 'Thứ 2 - 4 - 6 (19h - 20h30)', 22, 30),
    (3, 'Advanced C1', 'Thầy John', 'Thứ 3 - 5 - 7 (18h - 19h30)', 16, 30),
    (4, 'IELTS Intensive 6.5+', 'Thầy Mark', 'Thứ 7 - CN (8h - 10h)', 28, 30),
    (5, 'Business English', 'Cô Emma', 'Thứ 2 - 4 - 6 (17h - 18h30)', 20, 30);

//Kiểm tra kết nối
    if ($conn->multi_query($insert)){
    do {
        if ($conn->errno) {
            echo "Lỗi: " .$conn->error. "<br>";
        }
    } while ($conn->next_result());
    echo "Nhập liệu thành công";
    } else {
        echo "Nhập liệu thất bại!";
    } 
?>