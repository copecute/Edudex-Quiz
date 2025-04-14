@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

    ThaoTac --> PhanCongPhongThi : Phân công phòng thi
    ThaoTac --> XoaPhongThi : Xóa phòng thi
    ThaoTac --> XuatExcel : Xuất danh sách phòng thi

state "Phân công phòng thi" as PhanCongPhongThi {
    [*] --> KiemTraKyThi
    KiemTraKyThi --> KyThiHopLe : Kỳ thi hợp lệ
    KiemTraKyThi --> KyThiKhongHopLe : Kỳ thi không hợp lệ
    KyThiHopLe --> PhanCong
    PhanCong --> TBThanhCong
    KyThiKhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Xóa phòng thi khỏi danh sách" as XoaPhongThi {
    [*] --> ChonPhongThi
    ChonPhongThi --> XacNhanXoa
    XacNhanXoa --> CapNhatDanhSach
    CapNhatDanhSach --> [*]
}

state "Xuất danh sách phòng thi ra Excel" as XuatExcel {
    [*] --> TaoFile
    TaoFile --> CungCapTaiVe
    CungCapTaiVe --> [*]
}

@enduml
