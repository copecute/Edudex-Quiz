@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý kết quả thi
Admin -> UI: Chọn kỳ thi
UI -> System: Gửi yêu cầu kiểm tra kỳ thi
System -> DB: Kiểm tra kỳ thi
DB --> System: Kết quả kiểm tra
alt Kỳ thi không hợp lệ
    System -> UI: Thông báo lỗi
else Kỳ thi hợp lệ
end

alt Xem kết quả thi
    Admin -> UI: Chọn thí sinh hoặc phòng thi
    UI -> System: Gửi yêu cầu truy vấn kết quả thi
    System -> DB: Truy vấn kết quả thi
    DB --> System: Kết quả truy vấn
    System -> UI: Hiển thị kết quả thi
end

alt Phúc khảo kết quả
    Admin -> UI: Chọn bài thi cần phúc khảo
    Admin -> UI: Nhập kết quả sau phúc khảo và lý do
    UI -> System: Gửi yêu cầu cập nhật kết quả phúc khảo
    System -> DB: Cập nhật kết quả sau phúc khảo
    DB --> System: Xác nhận cập nhật
    System -> UI: Thông báo kết quả phúc khảo
end

alt Xuất kết quả thi
    Admin -> UI: Chọn kỳ thi, thí sinh hoặc danh sách phòng thi
    UI -> System: Gửi yêu cầu xuất kết quả thi ra file Excel
    System -> DB: Xuất kết quả thi
    DB --> System: Xác nhận xuất kết quả
    System -> UI: Thông báo thành công
end
@enduml
