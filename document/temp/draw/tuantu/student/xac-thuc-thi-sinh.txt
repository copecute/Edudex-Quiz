@startuml
actor "Thí sinh" as Student
boundary "Student Client" as StudentClient
boundary "Teacher Client" as TeacherClient

Student -> StudentClient: Khởi động ứng dụng Student Client
Student -> StudentClient: Nhập số báo danh và mã sinh viên
StudentClient -> TeacherClient: Gửi yêu cầu đăng nhập đến Teacher Client
TeacherClient -> TeacherClient: Nhận yêu cầu đăng nhập
TeacherClient -> TeacherClient: Kiểm tra thông tin đăng nhập
alt (Thông tin hợp lệ)
    TeacherClient -> StudentClient: Xác nhận đăng nhập thành công
    StudentClient -> Student: Nhận xác nhận đăng nhập thành công
    StudentClient -> Student: Hiển thị trang tổng quan
else (Không hợp lệ)
    TeacherClient -> TeacherClient: Kiểm tra lỗi
    TeacherClient -> StudentClient: Trả về lỗi thông tin đăng nhập không hợp lệ
    StudentClient -> Student: Hiển thị thông báo lỗi
end

Student -> StudentClient: Chọn đăng xuất
StudentClient -> TeacherClient: Gửi yêu cầu đăng xuất đến Teacher Client
TeacherClient -> TeacherClient: Nhận yêu cầu đăng xuất
TeacherClient -> TeacherClient: Xử lý yêu cầu đăng xuất
TeacherClient -> StudentClient: Trả về xác nhận đăng xuất thành công
StudentClient -> Student: Nhận xác nhận đăng xuất
StudentClient -> Student: Hiển thị màn hình đăng nhập

@enduml
