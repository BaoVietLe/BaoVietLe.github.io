<?php
    include 'config.php';
    $create = "
    CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        level VARCHAR(50),
        goal VARCHAR(100),
        price VARCHAR(50),
        duration VARCHAR(100),
        address VARCHAR(255),
        img VARCHAR(255),
        description TEXT
    );

    CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT,
        class_name VARCHAR(100),
        teacher VARCHAR(100),
        schedule VARCHAR(100),
        enrolled INT DEFAULT 0,
        max_students INT DEFAULT 30,
        FOREIGN KEY (course_id) REFERENCES courses(id)
    );
    
    CREATE TABLE IF NOT EXISTS HoaDon (
    MaHD CHAR(10) PRIMARY KEY,
    NgayThanhToan DATE,
    TongTien DECIMAL(10,2),
    MaPGD CHAR(10),
    FOREIGN KEY (MaPGD) REFERENCES PhieuGhiDanh(MaPGD)
);
 CREATE TABLE IF NOT EXISTS PhieuGhiDanh (
   MaPGD CHAR(10) PRIMARY KEY,
    NgayGhiDanh DATE,
    MaLop CHAR(10),
    MaHV CHAR(10),
    FOREIGN KEY (MaLop) REFERENCES classes(id),
    FOREIGN KEY (MaHV) REFERENCES HocVien(MaHV)
);
CREATE TABLE IF NOT EXISTS HocVien (
    MaHV CHAR(10) PRIMARY KEY,
    TenHV VARCHAR(100),
    Email VARCHAR(100),
    SDT VARCHAR(20)
);
CREATE TABLE IF NOT EXISTS TaiKhoan (
    MaTK CHAR(10) PRIMARY KEY,
    TenDangNhap VARCHAR(50) NOT NULL,
    MatKhau VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS NguoiDung (
    MaNguoiDung CHAR(10) PRIMARY KEY,
    HoTen NVARCHAR(50),
    GioiTinh BIT,
    NgaySinh DATE,
    Email VARCHAR(100),
    SDT VARCHAR(15),
    MaTK CHAR(10),
    FOREIGN KEY (MaTK) REFERENCES TaiKhoan(MaTK),
    CONSTRAINT chk_GioiTinh CHECK (GioiTinh IN (0, 1))
);

CREATE TABLE IF NOT EXISTS GiaoVien (
    MaGV CHAR(10) PRIMARY KEY,
    ChuyenMon NVARCHAR(50),
    TrinhDo NVARCHAR(50),
    MaNguoiDung CHAR(10),
    FOREIGN KEY (MaNguoiDung) REFERENCES NguoiDung(MaNguoiDung)
);

CREATE TABLE IF NOT EXISTS QuanTriVien (
    MaQTV CHAR(10) PRIMARY KEY,
    MaNguoiDung CHAR(10),
    FOREIGN KEY (MaNguoiDung) REFERENCES NguoiDung(MaNguoiDung)
);

CREATE TABLE IF NOT EXISTS QuanLyHocVu (
    MaQLHV CHAR(10) PRIMARY KEY,
    MaNguoiDung CHAR(10),
    FOREIGN KEY (MaNguoiDung) REFERENCES NguoiDung(MaNguoiDung)
);

    ";

//Kiểm tra kết nối
    if ($conn->multi_query($create)){
    do {
        if ($conn->errno) {
            echo "Lỗi: " .$conn->error. "<br>";
        }
    } while ($conn->next_result());
    echo "Tạo bảng thành công";
    } else {
        echo "Tạo bảng thất bại!";
    } 
?>