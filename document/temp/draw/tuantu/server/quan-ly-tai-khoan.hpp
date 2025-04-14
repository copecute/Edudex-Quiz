@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý tài khoản

alt Thêm tài khoản mới
    Admin -> UI: Nhập thông tin tài khoản
    UI -> System: Gửi thông tin tài khoản
    System -> DB: Kiểm tra thông tin tài khoản
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu tài khoản vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa tài khoản
    Admin -> UI: Chọn tài khoản cần chỉnh sửa
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Cập nhật thông tin tài khoản
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa tài khoản
    Admin -> UI: Chọn tài khoản cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa tài khoản
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật danh sách tài khoản
end

alt Khóa/Mở khóa tài khoản
    Admin -> UI: Chọn tài khoản cần khóa/mở khóa
    UI -> System: Gửi yêu cầu cập nhật trạng thái
    System -> DB: Cập nhật trạng thái tài khoản
    DB --> System: Xác nhận thành công
    System -> UI: Thông báo thành công
end

alt Nhập danh sách tài khoản từ Excel
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

alt Xuất danh sách tài khoản ra Excel
    System -> UI: Tạo tệp Excel danh sách tài khoản
    UI -> Admin: Cung cấp tệp để tải về
end

@enduml
