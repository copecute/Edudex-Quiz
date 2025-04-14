@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> NhapThongTin

state "Nhập thông tin đăng nhập" as NhapThongTin {
    NhapThongTin --> KiemTraThongTin : Gửi thông tin tài khoản
    KiemTraThongTin --> ThongTinHopLe : Thông tin hợp lệ
    KiemTraThongTin --> ThongTinKhongHopLe : Thông tin không hợp lệ
}

state "Thông tin hợp lệ" as ThongTinHopLe {
    [*] --> TaiDuLieuThi
    TaiDuLieuThi --> TaiDanhSachThiSinh
    TaiDanhSachThiSinh --> TrangTongQuan
}

state "Thông tin không hợp lệ" as ThongTinKhongHopLe {
    [*] --> ThongBaoLoi
    ThongBaoLoi --> [*]
}

state "Trang tổng quan" as TrangTongQuan {
    [*] --> ChonCaThi
    ChonCaThi --> TaiDuLieuThi
    TaiDuLieuThi --> TaiDanhSachThiSinh
    [*] --> DangXuat
}

state "Đăng xuất" as DangXuat {
    [*] --> XoaDauLieu
    XoaDauLieu --> ThongBaoDangXuat
    ThongBaoDangXuat --> [*]
}

state "Thông báo lỗi" as ThongBaoLoi {
    [*] --> [*]
}

@enduml
