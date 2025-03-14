@startuml
actor "Thí sinh" as Student
boundary "Student client" as StudentClient
boundary "Teacher client" as TeacherClient

Student -> StudentClient: Mở ứng dụng Student Client
StudentClient -> TeacherClient: Tự động tìm máy teacher
TeacherClient -> StudentClient: Trả về kết quả
alt (Kết nối thành công)
    StudentClient -> Student: Hiển thị trang đăng nhập
else (Không)
    StudentClient -> Student: Thông báo lỗi kết nối
end

@enduml