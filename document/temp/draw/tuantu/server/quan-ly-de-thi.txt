@startuml
actor "Giáo viên" as Teacher
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Teacher -> UI: Chọn chức năng Quản lý đề thi

alt Thêm đề thi mới
    Teacher -> UI: Nhập thông tin đề thi (tên, môn học, thời gian)
    Teacher -> UI: Chọn câu hỏi từ ngân hàng câu hỏi
    UI -> System: Gửi thông tin đề thi và câu hỏi
    System -> DB: Kiểm tra thông tin đề thi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu đề thi vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa đề thi
    Teacher -> UI: Chọn đề thi cần chỉnh sửa
    Teacher -> UI: Cập nhật thông tin và câu hỏi
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Lưu thay đổi vào cơ sở dữ liệu
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa đề thi
    Teacher -> UI: Chọn đề thi cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa đề thi
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật danh sách đề thi
end

alt Nhập danh sách đề thi từ Excel
    Teacher -> UI: Tải lên tệp Excel
    UI -> System: Gửi tệp Excel
    System -> DB: Kiểm tra định dạng tệp
    DB --> System: Kết quả kiểm tra định dạng
    alt Định dạng hợp lệ
        System -> DB: Lưu danh sách đề thi vào cơ sở dữ liệu
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Định dạng không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xuất danh sách đề thi ra Excel
    System -> UI: Tạo tệp Excel danh sách đề thi
    UI -> Teacher: Cung cấp tệp để tải về
end

@enduml