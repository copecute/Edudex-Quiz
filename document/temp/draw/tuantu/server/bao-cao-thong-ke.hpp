@startuml
actor "Quản trị viên" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

' Luồng chính
Admin -> UI: Truy cập chức năng Báo cáo kết quả
Admin -> UI: Chọn kỳ thi cần báo cáo
UI -> System: Gửi yêu cầu lấy dữ liệu kỳ thi
System -> DB: Truy vấn dữ liệu kỳ thi
DB --> System: Trả về dữ liệu kỳ thi
System --> UI: Hiển thị dashboard báo cáo

' Các loại báo cáo
alt Báo cáo tổng quan
    Admin -> UI: Xem báo cáo tổng quan
    UI -> System: Yêu cầu dữ liệu tổng quan
    System -> DB: Truy vấn số liệu tổng quan (thí sinh, môn thi, điểm...)
    DB --> System: Trả về dữ liệu
    System --> UI: Hiển thị báo cáo tổng quan
else Báo cáo theo môn thi
    Admin -> UI: Chọn xem báo cáo theo môn thi
    UI -> System: Yêu cầu thống kê theo môn thi
    System -> DB: Truy vấn dữ liệu theo môn thi
    DB --> System: Trả về dữ liệu
    System --> UI: Hiển thị thống kê theo môn thi
    
    alt Xem chi tiết môn thi
        Admin -> UI: Click vào môn thi cụ thể 
        UI -> System: Yêu cầu dữ liệu chi tiết (AJAX)
        System -> DB: Truy vấn kết quả thí sinh theo môn
        DB --> System: Trả về dữ liệu chi tiết
        System --> UI: Hiển thị modal kết quả chi tiết
    end
else Báo cáo theo phòng thi
    Admin -> UI: Chọn xem báo cáo theo phòng thi
    UI -> System: Yêu cầu thống kê theo phòng thi
    System -> DB: Truy vấn dữ liệu theo phòng thi
    DB --> System: Trả về dữ liệu
    System --> UI: Hiển thị thống kê theo phòng thi
    
    alt Xem chi tiết phòng thi
        Admin -> UI: Click vào phòng thi cụ thể
        UI -> System: Yêu cầu dữ liệu chi tiết (AJAX)
        System -> DB: Truy vấn kết quả thí sinh theo phòng
        DB --> System: Trả về dữ liệu chi tiết
        System --> UI: Hiển thị modal kết quả chi tiết
    end
else Báo cáo xếp hạng thí sinh
    Admin -> UI: Chọn xem xếp hạng thí sinh
    Admin -> UI: Thiết lập bộ lọc (môn thi, top X...)
    UI -> System: Gửi yêu cầu xếp hạng theo bộ lọc
    System -> DB: Truy vấn dữ liệu xếp hạng
    DB --> System: Trả về dữ liệu
    System --> UI: Hiển thị xếp hạng thí sinh
else Báo cáo tỷ lệ hoàn thành
    Admin -> UI: Chọn xem tỷ lệ hoàn thành
    UI -> System: Yêu cầu dữ liệu tỷ lệ hoàn thành
    System -> DB: Truy vấn tỷ lệ hoàn thành bài thi
    DB --> System: Trả về dữ liệu
    System --> UI: Hiển thị biểu đồ và thống kê
end

' Xuất báo cáo
alt Xuất báo cáo Excel
    Admin -> UI: Chọn xuất Excel
    UI -> System: Yêu cầu xuất báo cáo Excel
    System -> DB: Truy vấn dữ liệu cần xuất
    DB --> System: Trả về dữ liệu
    System --> Admin: Tải file Excel
else Xuất báo cáo Word
    Admin -> UI: Chọn xuất Word
    UI -> System: Yêu cầu xuất báo cáo Word
    System -> DB: Truy vấn dữ liệu cần xuất
    DB --> System: Trả về dữ liệu
    System --> Admin: Tải file Word
end

@enduml
