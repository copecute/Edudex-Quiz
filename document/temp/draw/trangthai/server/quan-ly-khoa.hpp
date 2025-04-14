@startuml
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac


state "Thao tác quản lý" as ThaoTac {
    ThaoTac --> ThemKhoa : Thêm khoa
    ThaoTac --> ChinhSuaKhoa : Chỉnh sửa khoa
    ThaoTac --> XoaKhoa : Xoá khoa
    ThaoTac --> NhapKhoa : Nhập từ Excel
    ThaoTac --> XuatKhoa : Xuất ra Excel
}

state "Thêm khoa" as ThemKhoa {
    [*] --> KiemTra
    KiemTra --> HopLe : Hợp lệ
    KiemTra --> KhongHopLe : Không hợp lệ
    HopLe --> LuuKhoa
    LuuKhoa --> ThongBaoOK
    KhongHopLe --> BaoLoi
    ThongBaoOK --> [*]
    BaoLoi --> [*]
}

state "Chỉnh sửa khoa" as ChinhSuaKhoa {
    [*] --> CapNhatThongTin
    CapNhatThongTin --> XacNhanCapNhat
    XacNhanCapNhat --> [*]
}

state "Xoá khoa" as XoaKhoa {
    [*] --> XacNhanXoa
    XacNhanXoa --> CapNhatDanhSach
    CapNhatDanhSach --> [*]
}

state "Nhập từ Excel" as NhapKhoa {
    [*] --> KiemTraDinhDang
    KiemTraDinhDang --> DinhDangDung : Hợp lệ
    KiemTraDinhDang --> DinhDangSai : Không hợp lệ
    DinhDangDung --> LuuDanhSach
    LuuDanhSach --> ThongBaoThanhCong
    DinhDangSai --> ThongBaoLoi
    ThongBaoThanhCong --> [*]
    ThongBaoLoi --> [*]
}

state "Xuất ra Excel" as XuatKhoa {
    [*] --> TaoTepExcel
    TaoTepExcel --> CungCapTaiVe
    CungCapTaiVe --> [*]
}

ThaoTac --> [*]
@enduml
