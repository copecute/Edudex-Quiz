@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ThaoTac

    ThaoTac --> PhanCongCanBo : Phân công cán bộ
    ThaoTac --> XoaCanBo : Xóa cán bộ
    ThaoTac --> XuatExcel : Xuất danh sách cán bộ

state "Phân công cán bộ coi thi" as PhanCongCanBo {
    [*] --> KiemTraKyThi
    KiemTraKyThi --> KyThiHopLe : Kỳ thi hợp lệ
    KiemTraKyThi --> KyThiKhongHopLe : Kỳ thi không hợp lệ
    KyThiHopLe --> PhanCong
    PhanCong --> TBThanhCong
    KyThiKhongHopLe --> TBThatBai
    TBThanhCong --> [*]
    TBThatBai --> [*]
}

state "Xóa cán bộ khỏi danh sách" as XoaCanBo {
    [*] --> ChonCanBo
    ChonCanBo --> XacNhanXoa
    XacNhanXoa --> CapNhatDanhSach
    CapNhatDanhSach --> [*]
}

state "Xuất danh sách cán bộ coi thi ra Excel" as XuatExcel {
    [*] --> TaoFile
    TaoFile --> CungCapTaiVe
    CungCapTaiVe --> [*]
}

ThaoTac --> [*]
@enduml
