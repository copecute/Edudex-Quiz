@startuml
top to bottom direction
hide empty description

state "Truy cập chức năng Báo cáo kết quả" as TruyCapBaiBaoCao
state "Chọn kỳ thi" as ChonKyThi
state "Lấy dữ liệu kỳ thi" as LayDuLieuKyThi
state "Hiển thị dashboard báo cáo" as HienThiDashboard

[*] --> TruyCapBaiBaoCao
TruyCapBaiBaoCao --> ChonKyThi
ChonKyThi --> LayDuLieuKyThi
LayDuLieuKyThi --> HienThiDashboard : Dữ liệu kỳ thi hợp lệ

state "Báo cáo tổng quan" as BaoCaoTongQuan
state "Báo cáo theo môn thi" as BaoCaoTheoMon
state "Báo cáo theo phòng thi" as BaoCaoTheoPhong
state "Báo cáo xếp hạng thí sinh" as BaoCaoXepHang
state "Báo cáo tỷ lệ hoàn thành" as BaoCaoTyLeHoanThanh

HienThiDashboard --> BaoCaoTongQuan : Chọn báo cáo tổng quan
HienThiDashboard --> BaoCaoTheoMon : Chọn báo cáo theo môn thi
HienThiDashboard --> BaoCaoTheoPhong : Chọn báo cáo theo phòng thi
HienThiDashboard --> BaoCaoXepHang : Chọn báo cáo xếp hạng
HienThiDashboard --> BaoCaoTyLeHoanThanh : Chọn báo cáo tỷ lệ hoàn thành

    BaoCaoTongQuan --> [*]

    BaoCaoTheoMon --> [*]

    BaoCaoTheoPhong --> [*]

    BaoCaoXepHang --> [*]

    BaoCaoTyLeHoanThanh --> [*]

state "Xuất báo cáo" as XuatBaoCao
HienThiDashboard --> XuatBaoCao : Chọn xuất báo cáo

    XuatBaoCao --> [*]

    XuatBaoCao --> [*]
@enduml
