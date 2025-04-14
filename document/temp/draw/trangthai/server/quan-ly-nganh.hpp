@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac



    ThaoTac --> ThemNganh : Thêm ngành
    ThaoTac --> ChinhSuaNganh : Chỉnh sửa ngành
    ThaoTac --> XoaNganh : Xoá ngành
    ThaoTac --> NhapExcel : Nhập từ Excel
    ThaoTac --> XuatExcel : Xuất ra Excel


state "Thêm ngành" as ThemNganh {
    [*] --> KiemTraThongTin
    KiemTraThongTin --> HopLe : Hợp lệ
    KiemTraThongTin --> KhongHopLe : Không hợp lệ
    HopLe --> LuuNganh
    LuuNganh --> TBThanhCong
    KhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Chỉnh sửa ngành" as ChinhSuaNganh {
    [*] --> CapNhatThongTin
    CapNhatThongTin --> XacNhanCapNhat
    XacNhanCapNhat --> [*]
}

state "Xoá ngành" as XoaNganh {
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
