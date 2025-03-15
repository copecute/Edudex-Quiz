@startuml
actor "Thí sinh" as Student
boundary "Student Client" as StudentClient

Student -> StudentClient: Chọn chức năng xem lịch sử bài thi
alt Chọn xem lịch sử bài thi
    StudentClient -> StudentClient: Hiển thị danh sách file đã mở lịch sử
    Student -> StudentClient: Chọn bài thi cần mở
    alt Mở file kết quả
        StudentClient -> StudentClient: Mở file kết quả bài thi
        StudentClient -> StudentClient: Hiển thị kết quả bài thi
    end
    alt In kết quả
        Student -> StudentClient: Chọn bài thi cần in
        StudentClient -> StudentClient: Gửi yêu cầu in kết quả bài thi
        StudentClient -> StudentClient: In kết quả bài thi
        StudentClient -> StudentClient: Thông báo in thành công
    end
end

@enduml
