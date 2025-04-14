@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}


state "Trang tổng quan" as TrangTongQuan {
    [*] --> LayThongTin : Lấy thông tin kỳ thi, ca thi, phòng thi, đề thi
    LayThongTin --> TinhThoiGian : Tính thời gian còn lại của ca thi
    TinhThoiGian --> HienThiDuLieu : Hiển thị dữ liệu lên giao diện
    HienThiDuLieu --> [*]
}
@enduml
