@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

state "Thao tác quản lý" as ThaoTac {
    ThaoTac --> ThemCaThi : Thêm ca thi
    ThaoTac --> ChinhSuaCaThi : Chỉnh sửa ca thi
    ThaoTac --> XoaCaThi : Xóa ca thi
    ThaoTac --> KhoaMoCaThi : Khóa/Mở khóa ca thi
    ThaoTac --> NhapExcel : Nhập từ Excel
    ThaoTac --> XuatExcel : Xuất ra Excel
}

state "Thêm ca thi mới" as ThemCaThi {
    [*] --> KiemTraThongTin
    KiemTraThongTin --> HopLe : Thông tin hợp lệ
    KiemTraThongTin --> KhongHopLe : Thông tin không hợp lệ
    HopLe --> LuuCaThi
    LuuCaThi --> TBThanhCong
    KhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Chỉnh sửa ca thi" as ChinhSuaCaThi {
    [*] --> CapNhatThongTin
    CapNhatThongTin --> LuuCapNhat
    LuuCapNhat --> TBCapNhat
    TBCapNhat --> [*]
}

state "Xóa ca thi" as XoaCaThi {
    [*] --> XacNhanXoa
    XacNhanXoa --> CapNhatDanhSach
    CapNhatDanhSach --> [*]
}

state "Khóa/Mở khóa ca thi" as KhoaMoCaThi {
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
