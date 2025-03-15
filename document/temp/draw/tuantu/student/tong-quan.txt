@startuml
actor "Thí sinh" as Student
boundary "Student Client" as StudentClient
boundary "Teacher Client" as TeacherClient

Student -> StudentClient: Khởi động ứng dụng Student Client
Student -> StudentClient: Nhập thông tin đăng nhập và đăng nhập thành công
StudentClient -> TeacherClient: Gửi yêu cầu lấy dữ liệu thi từ Teacher Client
TeacherClient -> TeacherClient: Nhận yêu cầu lấy dữ liệu thi
TeacherClient -> TeacherClient: Truy vấn dữ liệu kỳ thi, ca thi, đề thi, phòng thi
TeacherClient -> StudentClient: Trả về dữ liệu kỳ thi, ca thi, đề thi, phòng thi cho Student Client
StudentClient -> StudentClient: Nhận dữ liệu kỳ thi, ca thi, đề thi, phòng thi
StudentClient -> Student: Hiển thị thông tin kỳ thi, ca thi, đề thi, phòng thi
StudentClient -> Student: Hiển thị thông tin thí sinh (số báo danh, tên, mã sinh viên)

@enduml
