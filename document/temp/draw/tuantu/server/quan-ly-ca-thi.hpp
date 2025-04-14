@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Quản lý ca thi
Admin -> UI: Chọn kỳ thi
UI -> System: Gửi yêu cầu kiểm tra kỳ thi
System -> DB: Kiểm tra kỳ thi
DB --> System: Kết quả kiểm tra
alt Kỳ thi không hợp lệ
    System -> UI: Thông báo lỗi
else Kỳ thi hợp lệ
end

alt Thêm ca thi mới
    Admin -> UI: Nhập thông tin ca thi (tên ca, ngày thi, giờ bắt đầu, giờ kết thúc, phòng thi)
    Admin -> UI: Chọn đề thi áp dụng
    UI -> System: Gửi thông tin ca thi và đề thi
    System -> DB: Kiểm tra thông tin ca thi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu ca thi vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa ca thi
    Admin -> UI: Chọn ca thi cần chỉnh sửa
    Admin -> UI: Cập nhật thông tin ca thi
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Lưu thay đổi vào cơ sở dữ liệu
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa ca thi
    Admin -> UI: Chọn ca thi cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa ca thi
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật danh sách ca thi
end

alt Khóa/Mở khóa ca thi
    Admin -> UI: Chọn ca thi cần khóa/mở khóa
    UI -> System: Gửi yêu cầu cập nhật trạng thái ca thi
    System -> DB: Cập nhật trạng thái ca thi
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Nhập danh sách ca thi từ Excel
    Admin -> UI: Tải lên tệp Excel
    UI -> System: Gửi tệp Excel
    System -> DB: Kiểm tra định dạng tệp
    DB --> System: Kết quả kiểm tra định dạng
    alt Định dạng hợp lệ
        System -> DB: Lưu danh sách ca thi vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Định dạng không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xuất danh sách ca thi ra Excel
    System -> UI: Tạo tệp Excel danh sách ca thi
    UI -> Admin: Cung cấp tệp để tải về
end
@enduml