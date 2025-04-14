@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý phòng thi thuộc kỳ thi
Admin -> UI: Chọn kỳ thi
UI -> System: Gửi yêu cầu kiểm tra kỳ thi
System -> DB: Kiểm tra kỳ thi
DB --> System: Kết quả kiểm tra
alt Kỳ thi không hợp lệ
    System -> UI: Thông báo lỗi
else Kỳ thi hợp lệ
end

alt Phân công phòng thi
    Admin -> UI: Chọn phòng thi từ danh sách có sẵn
    UI -> System: Gửi yêu cầu phân công phòng thi
    System -> DB: Kiểm tra thông tin phòng thi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu phân công phòng thi
        DB --> System: Xác nhận lưu phân công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xóa phòng thi khỏi danh sách phân công
    Admin -> UI: Chọn phòng thi cần xóa
    UI -> System: Gửi yêu cầu xóa phòng thi
    System -> DB: Xác nhận xóa phòng thi
    DB --> System: Xác nhận xóa
    System -> UI: Cập nhật danh sách phòng thi
end

alt Xuất danh sách phòng thi ra Excel
    System -> UI: Tạo tệp Excel danh sách phòng thi
    UI -> Admin: Cung cấp tệp để tải về
end
@enduml
