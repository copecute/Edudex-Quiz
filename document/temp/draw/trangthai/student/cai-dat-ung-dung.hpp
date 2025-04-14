@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> ChonCaiDat
state "Chọn cài đặt" as ChonCaiDat
    ChonCaiDat --> ChonCheDoGiaoDien : Chọn chế độ giao diện
    ChonCaiDat --> ChonMauChuDe : Chọn màu chủ đề
    ChonCaiDat --> ChonHuongVanBan : Chọn hướng văn bản
    ChonCaiDat --> CaiDatNangCao : Chọn cài đặt nâng cao

state "Chế độ giao diện" as ChonCheDoGiaoDien
    ChonCheDoGiaoDien --> ChonSang : Chọn giao diện sáng
    ChonCheDoGiaoDien --> ChonToi : Chọn giao diện tối
    ChonCheDoGiaoDien --> ChonHeThong : Chọn giao diện hệ thống

state "Màu chủ đề" as ChonMauChuDe
    ChonMauChuDe --> LuaMauChuDe : Chọn màu chủ đề yêu thích

state "Hướng văn bản" as ChonHuongVanBan
    ChonHuongVanBan --> LuaHuongVanBan : Chọn hướng văn bản

state "Cài đặt nâng cao" as CaiDatNangCao 
    CaiDatNangCao --> NhapMatKhau : Nhập mật khẩu xác nhận
    NhapMatKhau --> MatKhauDung : Mật khẩu đúng
    NhapMatKhau --> MatKhauSai : Mật khẩu sai
    MatKhauDung --> SuaSoMay : Sửa số máy
    MatKhauSai --> ThongBaoSai : Thông báo mật khẩu không đúng

state "Sửa số máy" as SuaSoMay 
    SuaSoMay --> CapNhatSoMay : Cập nhật số máy vào hệ thống
    CapNhatSoMay --> ChonCaiDat

state "Thông báo mật khẩu không đúng" as ThongBaoSai
    ThongBaoSai --> [*]
@enduml
