@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

    [*] --> NhapThongTin : Nhập số báo danh và mã sinh viên
    NhapThongTin --> GuiDangNhap : Gửi yêu cầu đăng nhập đến Teacher Client

    GuiDangNhap --> DangNhapThanhCong : Thông tin hợp lệ
    DangNhapThanhCong --> HienThiTrangTongQuan
    HienThiTrangTongQuan --> SuDungUngDung

    GuiDangNhap --> DangNhapThatBai : Thông tin không hợp lệ
    DangNhapThatBai --> ThongBaoLoi
    ThongBaoLoi --> NhapThongTin

    SuDungUngDung --> DangXuat : Chọn đăng xuất
    DangXuat --> GuiYeuCauDangXuat : Gửi yêu cầu đến Teacher Client
    GuiYeuCauDangXuat --> XacNhanDangXuat : Nhận xác nhận đăng xuất
    XacNhanDangXuat --> HienThiManHinhDangNhap
    HienThiManHinhDangNhap --> [*]
    ThongBaoLoi --> [*]

@enduml
