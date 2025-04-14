@startuml
top to bottom direction
hide empty description
state "Chọn chức năng Xem lịch phân công" as ChonChucNang
state "Gửi yêu cầu xem lịch phân công" as GuiYeuCau
state "Truy vấn danh sách phân công" as TruyVanDanhSach
state "Hiển thị danh sách phân công" as HienThiDanhSach

[*] --> ChonChucNang
ChonChucNang --> GuiYeuCau
GuiYeuCau --> TruyVanDanhSach
TruyVanDanhSach --> HienThiDanhSach : Trả về danh sách phân công
HienThiDanhSach --> [*]
@enduml
