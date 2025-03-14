@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý phòng thi

alt Thêm phòng thi mới
    Admin -> UI: Nhập thông tin phòng thi
    UI -> System: Gửi thông tin phòng thi
    System -> DB: Kiểm tra thông tin phòng thi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu phòng thi vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa phòng thi
    Admin -> UI: Chọn phòng thi cần chỉnh sửa
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Cập nhật thông tin vào cơ sở dữ liệu
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa phòng thi
    Admin -> UI: Chọn phòng thi cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa phòng thi
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật danh sách phòng thi
end

alt Khóa/Mở khóa phòng thi
    Admin -> UI: Chọn phòng thi cần khóa/mở khóa
    UI -> System: Gửi yêu cầu cập nhật trạng thái
    System -> DB: Cập nhật trạng thái phòng thi
    DB --> System: Xác nhận thành công
    System -> UI: Thông báo thành công
end

alt Nhập danh sách phòng thi từ Excel
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

alt Xuất danh sách phòng thi ra Excel
    System -> UI: Tạo tệp Excel danh sách phòng thi
    UI -> Admin: Cung cấp tệp để tải về
end
@enduml
