@startuml
top to bottom direction
hide empty description
state "Chọn chức năng Phân công" as ChonChucNang
state "Kiểm tra kỳ thi" as KiemTraKyThi
state "Phân công môn thi - ca thi" as PhanCongMonThiCaThi
state "Phân công phòng thi - ca thi" as PhanCongPhongThiCaThi
state "Tự động phân công" as TuDongPhanCong
state "Xóa dữ liệu phân công" as XoaDuLieuPhanCong

[*] --> ChonChucNang
ChonChucNang --> KiemTraKyThi
KiemTraKyThi --> KyThiHopLe : Kỳ thi hợp lệ
KiemTraKyThi --> KyThiKhongHopLe : Kỳ thi không hợp lệ
KyThiHopLe --> PhanCongMonThiCaThi : Phân công môn thi - ca thi
KyThiHopLe --> PhanCongPhongThiCaThi : Phân công phòng thi - ca thi
KyThiHopLe --> TuDongPhanCong : Tự động phân công
KyThiHopLe --> XoaDuLieuPhanCong : Xóa dữ liệu phân công
PhanCongMonThiCaThi --> [*]
PhanCongPhongThiCaThi --> [*]
TuDongPhanCong --> [*]
XoaDuLieuPhanCong --> [*]
@enduml
