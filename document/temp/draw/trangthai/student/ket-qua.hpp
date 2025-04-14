@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> HienThiKetQua

HienThiKetQua --> ChonHanhDong : Thí sinh chọn hành động

    ChonHanhDong --> KetThuc : Chọn "Kết thúc"
    KetThuc --> [*]

    ChonHanhDong --> InKetQua : Chọn "In kết quả"
    InKetQua --> LuaChonIn : Hiển thị tùy chọn in hoặc lưu file

        LuaChonIn --> LuuFile : Chọn "Lưu file"
        LuuFile --> [*]

        LuaChonIn --> InRaMay : Chọn "In kết quả"
        InRaMay --> [*]

@enduml
