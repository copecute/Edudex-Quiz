@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> HienThiThongTinDeThi
[*] --> TimKiemLocCauHoi

    HienThiThongTinDeThi --> ChonKiemTraDeThi : Chọn kiểm tra đề thi
    ChonKiemTraDeThi --> KiemTraTieuChi : Kiểm tra các tiêu chí đề thi
    KiemTraTieuChi --> DeThiHopLe : Đề thi hợp lệ
    KiemTraTieuChi --> DeThiKhongHopLe : Đề thi không hợp lệ
    DeThiHopLe --> HienThiThongTinDeThi : Hiển thị thông tin đề thi
    DeThiKhongHopLe --> ThongBaoLoi : Thông báo lỗi đề thi không hợp lệ
    ThongBaoLoi --> [*]

TimKiemLocCauHoi : Tìm kiếm và lọc câu hỏi
    TimKiemLocCauHoi --> NhapTuKhoaLoc : Nhập từ khóa tìm kiếm hoặc chọn bộ lọc
    NhapTuKhoaLoc --> LocCauHoi : Lọc câu hỏi theo điều kiện
    LocCauHoi --> HienThiDanhSachCauHoi : Hiển thị danh sách câu hỏi đã lọc
    HienThiDanhSachCauHoi --> [*]

@enduml
