@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ChonDanhSachThiSinh

state "Chọn chức năng danh sách thí sinh" as ChonDanhSachThiSinh {
    ChonDanhSachThiSinh --> HienThiDanhSach : Hiển thị danh sách thí sinh
    HienThiDanhSach --> [*]
}

@enduml
