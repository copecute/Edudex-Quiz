@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher Client" as TeacherClient

Invigilator -> TeacherClient: Chọn chức năng xem lịch sử bài thi
alt (Có chọn chức năng)
    TeacherClient -> TeacherClient: Hiển thị danh sách file đã mở lịch sử
    alt (Mở file kết quả)
        Invigilator -> TeacherClient: Chọn bài thi cần mở
        TeacherClient -> TeacherClient: Mở file kết quả bài thi
        TeacherClient -> Invigilator: Hiển thị kết quả bài thi
    end
    alt (In kết quả)
        Invigilator -> TeacherClient: Chọn bài thi cần in
        TeacherClient -> TeacherClient: Gửi yêu cầu in kết quả bài thi
        TeacherClient -> TeacherClient: In kết quả bài thi
        TeacherClient -> Invigilator: Thông báo in thành công
    end
end

@enduml
