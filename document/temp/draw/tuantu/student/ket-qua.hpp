@startuml
actor "Thí sinh" as Student
boundary "Student Client" as StudentClient
boundary "Teacher Client" as TeacherClient

TeacherClient -> StudentClient: Trả kết quả (số câu đúng, điểm số)
StudentClient -> StudentClient: Hiển thị kết quả bài thi (số câu đúng, điểm số)

Student -> StudentClient: Chọn hành động

alt Chọn "Kết thúc"
    StudentClient -> StudentClient: Quay lại trang tổng quan
else Chọn "In kết quả"
    StudentClient -> StudentClient: Hiển thị tùy chọn lưu file hoặc in kết quả
    Student -> StudentClient: Chọn "Lưu file" hoặc "In kết quả"
    alt Lưu file
        StudentClient -> StudentClient: Lưu kết quả bài thi vào file
    else In kết quả
        StudentClient -> StudentClient: In kết quả bài thi
    end
end

@enduml
