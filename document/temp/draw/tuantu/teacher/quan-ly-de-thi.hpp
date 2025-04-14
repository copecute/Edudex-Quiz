@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher Client" as TeacherClient

Invigilator -> TeacherClient: Chọn chức năng quản lý đề thi
alt (Có chọn chức năng)
    TeacherClient -> TeacherClient: Hiển thị thông tin đề thi
    Invigilator -> TeacherClient: Chọn kiểm tra đề thi
    TeacherClient -> TeacherClient: Kiểm tra các tiêu chí đề thi
    alt (Đề thi hợp lệ?)
        TeacherClient -> Invigilator: Hiển thị thông tin đề thi
    else (Không hợp lệ)
        TeacherClient -> Invigilator: Thông báo lỗi đề thi không hợp lệ
    end
end

alt (Không chọn chức năng quản lý đề thi)
    Invigilator -> TeacherClient: Tìm kiếm và lọc câu hỏi
    alt (Có tìm kiếm và lọc)
        Invigilator -> TeacherClient: Nhập từ khóa tìm kiếm hoặc chọn bộ lọc
        TeacherClient -> TeacherClient: Lọc câu hỏi theo điều kiện
        TeacherClient -> Invigilator: Hiển thị danh sách câu hỏi đã lọc
    end
end

@enduml
