@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher Client" as TeacherClient

Invigilator -> TeacherClient: Chọn chức năng danh sách thí sinh
alt (Có chọn chức năng)
    TeacherClient -> TeacherClient: Hiển thị danh sách thí sinh
end

@enduml
