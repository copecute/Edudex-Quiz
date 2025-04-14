@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher Client" as TeacherClient
control "Server" as Server
database "Database" as DB

Invigilator -> TeacherClient: Nhập username và password
TeacherClient -> Server: Gửi thông tin tài khoản đến server
Server -> DB: Kiểm tra thông tin đăng nhập
DB --> Server: Trả về thông tin đăng nhập
alt Thông tin hợp lệ
    Server -> TeacherClient: Trả về dữ liệu thành công
    TeacherClient -> TeacherClient: Lưu dữ liệu tài khoản vào client
    Invigilator -> TeacherClient: Chọn ca thi được phân
    TeacherClient -> Server: Gửi yêu cầu tải dữ liệu thi cho ca thi đã chọn
    Server -> DB: Truy vấn dữ liệu thi cho ca thi đã chọn
    DB --> Server: Trả về dữ liệu thi cho ca thi đã chọn
    Server -> TeacherClient: Trả về dữ liệu thi cho ca thi đã chọn
    TeacherClient -> TeacherClient: Lưu dữ liệu thi vào client
    TeacherClient -> Server: Gửi yêu cầu tải danh sách thí sinh
    Server -> DB: Truy vấn danh sách thí sinh
    DB --> Server: Trả về danh sách thí sinh
    Server -> TeacherClient: Trả về danh sách thí sinh
    TeacherClient -> TeacherClient: Lưu danh sách thí sinh vào client
    TeacherClient -> Invigilator: Hiển thị trang tổng quan

    alt Đăng xuất
        Invigilator -> TeacherClient: Nhấn nút "Đăng xuất"
        TeacherClient -> TeacherClient: Xóa dữ liệu tài khoản và dữ liệu liên quan khỏi client
        TeacherClient -> Server: Gửi yêu cầu đăng xuất đến server
        Server -> DB: Xóa session đăng nhập trong database
        Server -> TeacherClient: Trả về kết quả đăng xuất thành công
        TeacherClient -> Invigilator: Hiển thị màn hình đăng nhập
    else Không
    end
else Thông tin không hợp lệ
    Server -> TeacherClient: Kiểm tra lỗi
    Server -> TeacherClient: Trả về lỗi đăng nhập
    TeacherClient -> Invigilator: Hiển thị thông báo lỗi
end

@enduml
