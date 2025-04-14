@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher Client" as TeacherClient

Invigilator -> TeacherClient: Hiển thị trang tổng quan
TeacherClient -> TeacherClient: Lấy thông tin kỳ thi, ca thi, phòng thi, đề thi
TeacherClient -> TeacherClient: Tính thời gian còn lại của ca thi
TeacherClient -> Invigilator: Hiển thị dữ liệu lên giao diện

@enduml
