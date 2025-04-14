@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

    ThaoTac --> ThemCauHoi : Thêm câu hỏi
    ThaoTac --> ChinhSuaCauHoi : Chỉnh sửa
    ThaoTac --> XoaCauHoi : Xóa
    ThaoTac --> NhapExcel : Nhập từ Excel
    ThaoTac --> XuatExcel : Xuất ra Excel

state "Thêm câu hỏi" as ThemCauHoi {
    [*] --> KiemTraThongTin
    KiemTraThongTin --> HopLe : Hợp lệ
    KiemTraThongTin --> KhongHopLe : Không hợp lệ
    HopLe --> LuuCauHoi
    LuuCauHoi --> TBThanhCong
    KhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Chỉnh sửa câu hỏi" as ChinhSuaCauHoi {
    [*] --> CapNhatThongTin
    CapNhatThongTin --> XacNhanCapNhat
    XacNhanCapNhat --> [*]
}

state "Xóa câu hỏi" as XoaCauHoi {
    [*] --> XacNhanXoa
    XacNhanXoa --> CapNhatNganHang
    CapNhatNganHang --> [*]
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
