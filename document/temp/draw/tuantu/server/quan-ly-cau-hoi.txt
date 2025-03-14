@startuml
actor "Giáo viên" as Teacher
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Ngân hàng câu hỏi" as DB

Teacher -> UI: Chọn chức năng Quản lý câu hỏi

alt Thêm câu hỏi mới
    Teacher -> UI: Nhập nội dung câu hỏi, đáp án
    UI -> System: Gửi thông tin câu hỏi
    System -> DB: Kiểm tra thông tin câu hỏi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu câu hỏi vào ngân hàng câu hỏi
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Chỉnh sửa câu hỏi
    Teacher -> UI: Chọn câu hỏi cần chỉnh sửa
    UI -> System: Gửi thông tin cập nhật
    System -> DB: Cập nhật câu hỏi
    DB --> System: Xác nhận cập nhật thành công
    System -> UI: Thông báo thành công
end

alt Xóa câu hỏi
    Teacher -> UI: Chọn câu hỏi cần xóa
    UI -> System: Gửi yêu cầu xóa
    System -> DB: Xác nhận xóa câu hỏi
    DB --> System: Xác nhận xóa thành công
    System -> UI: Cập nhật ngân hàng câu hỏi
end

alt Nhập danh sách câu hỏi từ Excel
    Teacher -> UI: Tải lên tệp Excel
    UI -> System: Gửi tệp Excel
    System -> DB: Kiểm tra định dạng tệp
    DB --> System: Kết quả kiểm tra định dạng
    alt Định dạng hợp lệ
        System -> DB: Lưu danh sách câu hỏi vào ngân hàng câu hỏi
        DB --> System: Xác nhận lưu thành công
        System -> UI: Thông báo thành công
    else Định dạng không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Xuất danh sách câu hỏi ra Excel
    System -> UI: Tạo tệp Excel danh sách câu hỏi
    UI -> Teacher: Cung cấp tệp để tải về
end

@enduml