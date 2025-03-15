@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý môn thi
Admin -> UI: Chọn kỳ thi
UI -> System: Gửi yêu cầu kiểm tra kỳ thi
System -> DB: Kiểm tra kỳ thi
DB --> System: Kết quả kiểm tra
alt Kỳ thi không hợp lệ
    System -> UI: Thông báo lỗi
else Kỳ thi hợp lệ
end

alt Phân công môn thi
    Admin -> UI: Chọn môn học từ danh sách có sẵn
    UI -> System: Gửi yêu cầu phân công môn thi
    System -> DB: Ghi nhận môn học vào kỳ thi
    DB --> System: Xác nhận lưu môn học
    System -> UI: Thông báo thành công
end

alt Chỉnh sửa môn thi
    Admin -> UI: Chọn môn thi cần chỉnh sửa
    Admin -> UI: Cập nhật thông tin môn thi
    UI -> System: Gửi yêu cầu cập nhật môn thi
    System -> DB: Lưu thay đổi vào cơ sở dữ liệu
    DB --> System: Xác nhận lưu thay đổi
    System -> UI: Thông báo thành công
end

alt Xóa môn thi
    Admin -> UI: Chọn môn thi cần xóa
    UI -> System: Gửi yêu cầu xóa môn thi
    System -> DB: Xác nhận xóa môn thi khỏi kỳ thi
    DB --> System: Xác nhận xóa
    System -> UI: Cập nhật danh sách môn thi
end

alt Nhập danh sách môn thi từ Excel
    Admin -> UI: Tải lên tệp Excel
    UI -> System: Gửi tệp Excel
    System -> DB: Kiểm tra định dạng tệp
    DB --> System: Kết quả kiểm tra
    alt Định dạng hợp lệ
        System -> DB: Thêm danh sách môn thi vào kỳ thi
        DB --> System: Xác nhận thêm thành công
        System -> UI: Thông báo thành công
    else Định dạng không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xuất danh sách môn thi ra Excel
    System -> UI: Tạo tệp Excel danh sách môn thi
    UI -> Admin: Cung cấp tệp để tải về
end
@enduml
