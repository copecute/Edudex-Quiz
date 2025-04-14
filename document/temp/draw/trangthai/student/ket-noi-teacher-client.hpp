@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

    [*] --> TimMayTeacher : Tự động tìm máy Teacher
    TimMayTeacher --> KetNoiThanhCong : Kết nối thành công
    KetNoiThanhCong --> HienThiDangNhap
    HienThiDangNhap --> [*]

    TimMayTeacher --> KetNoiThatBai : Kết nối thất bại
    KetNoiThatBai --> ThongBaoLoi
    ThongBaoLoi --> [*]


@enduml
