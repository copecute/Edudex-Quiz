@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> LamBaiThi

state "Làm bài thi" as LamBaiThi {
    LamBaiThi --> BamNopBai : Nhấn nút "Nộp bài"
}

state "Xử lý nộp bài" as XuLyNopBai {
    BamNopBai -right-> GuiDuLieu : Gửi dữ liệu bài thi đến Teacher Client
    GuiDuLieu --> KiemTraDuLieu : Kiểm tra dữ liệu bài thi
    KiemTraDuLieu --> Loi : Có lỗi
    Loi --> HienThiLoi : Thông báo lỗi và hiển thị tùy chọn lưu file
    HienThiLoi --> LuuFile : Lưu kết quả ra file
    LuuFile --> [*]

    KiemTraDuLieu --> KhongLoi : Không có lỗi
    KhongLoi --> GhiNhanKetQua : Ghi nhận và tính kết quả
    GhiNhanKetQua --> TraVeKetQua : Trả lại kết quả cho Student Client
    TraVeKetQua --> HienThiKetQua : Hiển thị kết quả bài thi
    HienThiKetQua --> [*]
}

@enduml
