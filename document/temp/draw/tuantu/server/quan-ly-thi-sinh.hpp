@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý thí sinh
Admin -> UI: Chọn kỳ thi
UI -> System: Gửi yêu cầu kiểm tra kỳ thi
System -> DB: Kiểm tra kỳ thi
DB --> System: Kết quả kiểm tra
alt Kỳ thi không hợp lệ
    System -> UI: Thông báo lỗi
else Kỳ thi hợp lệ
end

alt Thêm thí sinh mới
    Admin -> UI: Nhập thông tin thí sinh (Họ tên, SĐT, địa chỉ...)
    UI -> System: Gửi thông tin thí sinh
    System -> DB: Kiểm tra thông tin thí sinh
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu thông tin thí sinh vào kỳ thi
        DB --> System: Xác nhận lưu thí sinh
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa thông tin thí sinh
    Admin -> UI: Chọn thí sinh cần chỉnh sửa
    Admin -> UI: Cập nhật thông tin
    UI -> System: Gửi yêu cầu chỉnh sửa
    System -> DB: Lưu thay đổi
    DB --> System: Xác nhận lưu thay đổi
    System -> UI: Thông báo thành công
end

alt Xóa thí sinh khỏi kỳ thi
    Admin -> UI: Chọn thí sinh cần xóa
    UI -> System: Gửi yêu cầu xóa thí sinh
    System -> DB: Xác nhận xóa thí sinh
    DB --> System: Xác nhận xóa
    System -> UI: Cập nhật danh sách thí sinh
end

alt Nhập danh sách thí sinh từ Excel
    Admin -> UI: Tải lên tệp Excel
    UI -> System: Gửi tệp Excel
    System -> DB: Kiểm tra định dạng tệp
    DB --> System: Kết quả kiểm tra
    alt Định dạng hợp lệ
        System -> DB: Thêm danh sách thí sinh vào kỳ thi
        DB --> System: Xác nhận thêm thành công
        System -> UI: Thông báo thành công
    else Định dạng không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xuất danh sách thí sinh ra Excel
    System -> UI: Tạo tệp Excel danh sách thí sinh
    UI -> Admin: Cung cấp tệp để tải về
end
@enduml
