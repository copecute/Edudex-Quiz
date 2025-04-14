@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý địa điểm thi

alt Thêm địa điểm thi mới
    Admin -> UI: Nhập thông tin địa điểm thi
    UI -> System: Gửi thông tin địa điểm
    System -> DB: Kiểm tra thông tin địa điểm
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu địa điểm vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa địa điểm thi
    Admin -> UI: Chọn địa điểm cần chỉnh sửa
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Cập nhật thông tin vào cơ sở dữ liệu
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa địa điểm thi
    Admin -> UI: Chọn địa điểm cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa địa điểm
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật danh sách địa điểm thi
end

alt Khóa/Mở khóa địa điểm thi
    Admin -> UI: Chọn địa điểm cần khóa/mở khóa
    UI -> System: Gửi yêu cầu cập nhật trạng thái
    System -> DB: Cập nhật trạng thái địa điểm
    DB --> System: Xác nhận thành công
    System -> UI: Thông báo thành công
end

alt Nhập danh sách địa điểm từ Excel
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

alt Xuất danh sách địa điểm ra Excel
    System -> UI: Tạo tệp Excel danh sách địa điểm
    UI -> Admin: Cung cấp tệp để tải về
end

@enduml
