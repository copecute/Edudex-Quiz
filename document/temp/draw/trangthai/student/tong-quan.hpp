@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*]  --> GuiYeuCauDuLieu : Gửi yêu cầu lấy dữ liệu thi
    GuiYeuCauDuLieu --> NhanDuLieu : Nhận dữ liệu kỳ thi, ca thi, đề thi, phòng thi
    GuiYeuCauDuLieu --> Loi : Hiển thị lỗi
    Loi --> [*]
    NhanDuLieu --> HienThiThongTinThi : Hiển thị thông tin thi
    HienThiThongTinThi --> HienThiThongTinThiSinh : Hiển thị thông tin thí sinh
    HienThiThongTinThiSinh --> [*]

@enduml
