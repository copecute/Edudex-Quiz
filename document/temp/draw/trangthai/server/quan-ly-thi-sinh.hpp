@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

    ThaoTac --> ThemThiSinhMoi : Thêm thí sinh
    ThaoTac --> ChinhSuaThiSinh : Chỉnh sửa thí sinh
    ThaoTac --> XoaThiSinh : Xóa thí sinh
    ThaoTac --> NhapExcel : Nhập từ Excel
    ThaoTac --> XuatExcel : Xuất ra Excel

state "Thêm thí sinh mới" as ThemThiSinhMoi {
    [*] --> KiemTraKyThi
    KiemTraKyThi --> KyThiHopLe : Kỳ thi hợp lệ
    KiemTraKyThi --> KyThiKhongHopLe : Kỳ thi không hợp lệ
    KyThiHopLe --> LuuThiSinh
    LuuThiSinh --> TBThanhCong
    KyThiKhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Chỉnh sửa thí sinh" as ChinhSuaThiSinh {
    [*] --> ChonThiSinh
    ChonThiSinh --> CapNhatThongTin
    CapNhatThongTin --> LuuCapNhat
    LuuCapNhat --> TBCapNhat
    TBCapNhat --> [*]
}

state "Xóa thí sinh" as XoaThiSinh {
    [*] --> XacNhanXoa
    XacNhanXoa --> CapNhatDanhSach
    CapNhatDanhSach --> [*]
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
