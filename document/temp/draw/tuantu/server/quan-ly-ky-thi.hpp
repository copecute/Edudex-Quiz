@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý kỳ thi

alt Thêm kỳ thi mới
    Admin -> UI: Nhập thông tin kỳ thi (tên, ngày thi, thời gian, phòng thi)
    Admin -> UI: Chọn đề thi áp dụng
    UI -> System: Gửi thông tin kỳ thi và đề thi
    System -> DB: Kiểm tra thông tin kỳ thi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu kỳ thi vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa kỳ thi
    Admin -> UI: Chọn kỳ thi cần chỉnh sửa
    Admin -> UI: Cập nhật thông tin kỳ thi
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Lưu thay đổi vào cơ sở dữ liệu
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa kỳ thi
    Admin -> UI: Chọn kỳ thi cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa kỳ thi
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật danh sách kỳ thi
end

alt Khóa/Mở khóa kỳ thi
    Admin -> UI: Chọn kỳ thi cần khóa/mở khóa
    UI -> System: Gửi yêu cầu cập nhật trạng thái kỳ thi
    System -> DB: Cập nhật trạng thái kỳ thi
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Nhập danh sách kỳ thi từ Excel
    Admin -> UI: Tải lên tệp Excel
    UI -> System: Gửi tệp Excel
    System -> DB: Kiểm tra định dạng tệp
    DB --> System: Kết quả kiểm tra định dạng
    alt Định dạng hợp lệ
        System -> DB: Lưu danh sách kỳ thi vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Định dạng không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xuất danh sách kỳ thi ra Excel
    System -> UI: Tạo tệp Excel danh sách kỳ thi
    UI -> Admin: Cung cấp tệp để tải về
end

@enduml
