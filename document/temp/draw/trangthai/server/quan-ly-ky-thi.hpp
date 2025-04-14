@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

    ThaoTac --> ThemKyThi : Thêm kỳ thi
    ThaoTac --> ChinhSuaKyThi : Chỉnh sửa
    ThaoTac --> XoaKyThi : Xóa
    ThaoTac --> KhoaMoKyThi : Khóa/Mở khóa
    ThaoTac --> NhapExcel : Nhập từ Excel
    ThaoTac --> XuatExcel : Xuất ra Excel

state "Thêm kỳ thi" as ThemKyThi {
    [*] --> KiemTraThongTin
    KiemTraThongTin --> HopLe : Hợp lệ
    KiemTraThongTin --> KhongHopLe : Không hợp lệ
    HopLe --> LuuKyThi
    LuuKyThi --> TBThanhCong
    KhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Chỉnh sửa kỳ thi" as ChinhSuaKyThi {
    [*] --> CapNhatThongTin
    CapNhatThongTin --> LuuCapNhat
    LuuCapNhat --> TBCapNhat
    TBCapNhat --> [*]
}

state "Xóa kỳ thi" as XoaKyThi {
    [*] --> XacNhanXoa
    XacNhanXoa --> CapNhatDanhSach
    CapNhatDanhSach --> [*]
}

state "Khóa/Mở khóa kỳ thi" as KhoaMoKyThi {
    [*] --> CapNhatTrangThai
    CapNhatTrangThai --> TBThanhCong
    TBThanhCong --> [*]
}

state "Nhập từ Excel" as NhapExcel {
    [*] --> KiemTraDinhDang
    KiemTraDinhDang --> DinhDangHopLe : Hợp lệ
    KiemTraDinhDang --> DinhDangKhongHopLe : Không hợp lệ
    DinhDangHopLe --> LuuDanhSach
    LuuDanhSach --> TBExcelOK
    DinhDangKhongHopLe --> TBExcelLoi
    TBExcelOK --> [*]
    TBExcelLoi --> [*]
}

state "Xuất ra Excel" as XuatExcel {
    [*] --> TaoFile
    TaoFile --> CungCapTaiVe
    CungCapTaiVe --> [*]
}

ThaoTac --> [*]
@enduml
