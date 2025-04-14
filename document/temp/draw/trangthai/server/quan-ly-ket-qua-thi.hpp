@startuml
top to bottom direction
hide empty description
state "Chọn chức năng Quản lý kết quả thi" as ChonChucNang
state "Kiểm tra kỳ thi" as KiemTraKyThi
state "Xem kết quả thi" as XemKetQuaThi
state "Phúc khảo kết quả" as PhucKhaoKetQua
state "Xuất kết quả thi" as XuatKetQuaThi

[*] --> ChonChucNang
ChonChucNang --> KiemTraKyThi
KiemTraKyThi --> KyThiHopLe : Kỳ thi hợp lệ
KiemTraKyThi --> KyThiKhongHopLe : Kỳ thi không hợp lệ
KyThiHopLe --> XemKetQuaThi : Xem kết quả thi
KyThiHopLe --> PhucKhaoKetQua : Phúc khảo kết quả
KyThiHopLe --> XuatKetQuaThi : Xuất kết quả thi
XemKetQuaThi --> [*]
PhucKhaoKetQua --> [*]
XuatKetQuaThi --> [*]
@enduml
