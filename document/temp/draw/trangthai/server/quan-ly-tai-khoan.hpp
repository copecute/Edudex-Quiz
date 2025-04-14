@startuml
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ChonChucNang

state "Chọn chức năng\nQuản lý tài khoản" as ChonChucNang {
    [*] --> ChoThaoTac
}

    ChoThaoTac --> ThemTaiKhoan : Thêm tài khoản
    ChoThaoTac --> ChinhSuaTaiKhoan : Chỉnh sửa tài khoản
    ChoThaoTac --> XoaTaiKhoan : Xoá tài khoản
    ChoThaoTac --> KhoaTaiKhoan : Khoá/Mở khoá
    ChoThaoTac --> NhapExcel : Nhập từ Excel
    ChoThaoTac --> XuatExcel : Xuất ra Excel

state "Thêm tài khoản" as ThemTaiKhoan {
    [*] --> KiemTraThongTin
    KiemTraThongTin --> HopLe : Hợp lệ
    KiemTraThongTin --> KhongHopLe : Không hợp lệ
    HopLe --> LuuTaiKhoan
    LuuTaiKhoan --> ThongBaoThanhCong
    KhongHopLe --> ThongBaoLoi
    ThongBaoThanhCong --> [*]
    ThongBaoLoi --> [*]
}

state "Chỉnh sửa tài khoản" as ChinhSuaTaiKhoan {
    [*] --> CapNhatThongTin
    CapNhatThongTin --> XacNhanCapNhat
    XacNhanCapNhat --> [*]
}

state "Xoá tài khoản" as XoaTaiKhoan {
    [*] --> XacNhanXoa
    XacNhanXoa --> CapNhatDanhSach
    CapNhatDanhSach --> [*]
}

state "Khoá/Mở khoá tài khoản" as KhoaTaiKhoan {
    [*] --> CapNhatTrangThai
    CapNhatTrangThai --> ThongBaoKQ
    ThongBaoKQ --> [*]
}

state "Nhập từ Excel" as NhapExcel {
    [*] --> KiemTraDinhDang
    KiemTraDinhDang --> DinhDangOK : Hợp lệ
    KiemTraDinhDang --> DinhDangSai : Không hợp lệ
    DinhDangOK --> LuuDanhSach
    LuuDanhSach --> ThongBaoOK
    DinhDangSai --> BaoLoi
    ThongBaoOK --> [*]
    BaoLoi --> [*]
}

state "Xuất ra Excel" as XuatExcel {
    [*] --> TaoTep
    TaoTep --> TaiVe
    TaiVe --> [*]
}

@enduml
