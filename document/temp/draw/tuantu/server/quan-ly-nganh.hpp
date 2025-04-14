@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý ngành

alt Thêm ngành mới
    Admin -> UI: Nhập thông tin ngành
    UI -> System: Gửi thông tin ngành
    System -> DB: Kiểm tra thông tin ngành
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu thông tin ngành vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa ngành
    Admin -> UI: Chọn ngành cần chỉnh sửa
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Cập nhật thông tin ngành
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa ngành
    Admin -> UI: Chọn ngành cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa ngành
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật danh sách ngành
end

alt Nhập danh sách ngành từ Excel
    Admin -> UI: Tải lên tệp Excel
    UI -> System: Gửi tệp Excel
    System -> DB: Kiểm tra định dạng tệp
    DB --> System: Kết quả kiểm tra định dạng
    alt Định dạng hợp lệ
        System -> DB: Lưu danh sách vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Định dạng không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xuất danh sách ngành ra Excel
    System -> UI: Tạo tệp Excel danh sách ngành
    UI -> Admin: Cung cấp tệp để tải về
end

@enduml
