@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher Client" as TeacherClient

Invigilator -> TeacherClient: Chọn chức năng quản lý phòng thi
alt (Có chọn chức năng)
    TeacherClient -> TeacherClient: Hiển thị thông tin phòng thi
    alt (Bật máy chủ)
        Invigilator -> TeacherClient: Bật máy chủ
        TeacherClient -> TeacherClient: Khởi động máy chủ phòng thi
        TeacherClient -> Invigilator: Thông báo máy chủ đã được bật
    end
    alt (Cấu hình máy chủ)
        Invigilator -> TeacherClient: Nhập danh sách IP cho phép và IP cấm
        TeacherClient -> TeacherClient: Cập nhật cấu hình IP
        TeacherClient -> Invigilator: Thông báo cấu hình thành công
    end
    alt (Xem danh sách máy thi)
        Invigilator -> TeacherClient: Xem danh sách máy thi
        TeacherClient -> TeacherClient: Hiển thị danh sách máy thi
    end
end

@enduml
