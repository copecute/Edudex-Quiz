@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

    ThaoTac --> PhanCongMonThi : Phân công môn thi
    ThaoTac --> ChinhSuaMonThi : Chỉnh sửa môn thi
    ThaoTac --> XoaMonThi : Xóa môn thi
    ThaoTac --> NhapExcel : Nhập từ Excel
    ThaoTac --> XuatExcel : Xuất ra Excel

state "Phân công môn thi" as PhanCongMonThi {
    [*] --> KiemTraKyThi
    KiemTraKyThi --> KyThiHopLe : Kỳ thi hợp lệ
    KiemTraKyThi --> KyThiKhongHopLe : Kỳ thi không hợp lệ
    KyThiHopLe --> LuuMonThi
    LuuMonThi --> TBThanhCong
    KyThiKhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Chỉnh sửa môn thi" as ChinhSuaMonThi {
    [*] --> ChonMonThi
    ChonMonThi --> CapNhatThongTin
    CapNhatThongTin --> LuuCapNhat
    LuuCapNhat --> TBCapNhat
    TBCapNhat --> [*]
}

state "Xóa môn thi" as XoaMonThi {
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
