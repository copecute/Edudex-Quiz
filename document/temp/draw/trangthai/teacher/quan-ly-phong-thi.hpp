@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> QuanLyPhongThi

state "Quản lý phòng thi" as QuanLyPhongThi {
    [*] --> HienThiPhongThi : Hiển thị thông tin phòng thi

        [*] --> BatMayChu : Bật máy chủ
        BatMayChu --> TBMayChuBat : Thông báo máy chủ đã được bật
        TBMayChuBat --> [*]

        [*] --> CauHinhMayChu : Cấu hình máy chủ
        CauHinhMayChu --> CapNhatCauHinh : Cập nhật cấu hình IP
        CapNhatCauHinh --> TBCauHinh : Thông báo cấu hình thành công
        TBCauHinh --> [*]

        [*] --> XemDanhSachMay : Xem danh sách máy thi
        XemDanhSachMay --> HienThiMayThi : Hiển thị danh sách máy thi
        HienThiMayThi --> [*]
    
}

@enduml
