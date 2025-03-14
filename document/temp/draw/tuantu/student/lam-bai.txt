@startuml
actor "Thí sinh" as Student
boundary "Student Client" as StudentClient
boundary "Teacher Client" as TeacherClient

Student -> StudentClient: Hoàn thành bài thi
Student -> StudentClient: Nhấn nút "Nộp bài" trên Student Client
StudentClient -> TeacherClient: Gửi dữ liệu bài thi (câu trả lời) tới Teacher Client
TeacherClient -> TeacherClient: Nhận dữ liệu bài thi từ Student Client
TeacherClient -> TeacherClient: Kiểm tra dữ liệu bài thi (kiểm tra lỗi)
alt Có lỗi
    TeacherClient -> StudentClient: Trả về thông báo lỗi cho Student Client
    TeacherClient -> StudentClient: Hiển thị tùy chọn lưu file bài thi
    StudentClient -> Student: Hiển thị thông báo lỗi và yêu cầu lưu bài thi
    Student -> StudentClient: Chọn lưu bài thi vào file
    StudentClient -> StudentClient: Lưu kết quả ra file
else Không
    TeacherClient -> TeacherClient: Ghi nhận bài làm và tính kết quả
    TeacherClient -> StudentClient: Trả lại kết quả (số câu đúng, điểm) cho Student Client
    StudentClient -> StudentClient: Nhận kết quả bài thi
    StudentClient -> Student: Chuyển tới màn hình kết quả
end

@enduml
