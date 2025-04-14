@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

    ThaoTac --> ThemDeThi : Thêm đề thi
    ThaoTac --> ChinhSuaDeThi : Chỉnh sửa
    ThaoTac --> XoaDeThi : Xóa
    ThaoTac --> NhapExcel : Nhập từ Excel
    ThaoTac --> XuatExcel : Xuất ra Excel

state "Thêm đề thi" as ThemDeThi {
    [*] --> KiemTraThongTin
    KiemTraThongTin --> HopLe : Hợp lệ
    KiemTraThongTin --> KhongHopLe : Không hợp lệ
    HopLe --> LuuDeThi
    LuuDeThi --> TBThanhCong
    KhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Chỉnh sửa đề thi" as ChinhSuaDeThi {
    [*] --> CapNhatThongTin
    CapNhatThongTin --> LuuCapNhat
    LuuCapNhat --> TBCapNhat
    TBCapNhat --> [*]
}

state "Xóa đề thi" as XoaDeThi {
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
