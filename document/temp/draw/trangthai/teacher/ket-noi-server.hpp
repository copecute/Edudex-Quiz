@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> MởỨngDụng

state "Mở ứng dụng Teacher Client" as MởỨngDụng {
    MởỨngDụng --> TìmServer : Tự động tìm server
    TìmServer --> TrangĐăngNhập : Trả về kết quả
}

state "Trang đăng nhập" as TrangĐăngNhập {
    [*] --> NhậpThôngTin
    NhậpThôngTin --> XácThựcThôngTin
    XácThựcThôngTin --> ThànhCông : Đăng nhập thành công
    XácThựcThôngTin --> ThấtBại : Đăng nhập thất bại
    ThànhCông --> TrangChính
    ThấtBại --> ThôngBáoLỗi
}

state "Trang chính" as TrangChính {
    [*] --> [*]
}

state "Thông báo lỗi" as ThôngBáoLỗi {
    [*] --> [*]
}

@enduml
