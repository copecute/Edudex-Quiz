@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher Client" as TeacherClient

Invigilator -> TeacherClient: Chọn chức năng cài đặt ứng dụng
alt (Có chọn chức năng)
    TeacherClient -> TeacherClient: Hiển thị màn hình cài đặt ứng dụng

    alt (Chế độ giao diện)
        Invigilator -> TeacherClient: Chọn chế độ giao diện (Sáng, Tối, Hệ thống)
        TeacherClient -> TeacherClient: Áp dụng chế độ giao diện đã chọn
    end

    alt (Chọn màu chủ đề)
        Invigilator -> TeacherClient: Chọn màu chủ đề yêu thích
        TeacherClient -> TeacherClient: Áp dụng màu chủ đề đã chọn
    end

    alt (Chọn hướng văn bản)
        Invigilator -> TeacherClient: Chọn hướng văn bản (Trái phải, Phải trái)
        TeacherClient -> TeacherClient: Áp dụng hướng văn bản đã chọn
    else (Không)
        Invigilator -> TeacherClient: Quay lại màn hình chính
    end

    alt (Ngắt kết nối máy chủ)
        Invigilator -> TeacherClient: Nhấn nút "Ngắt kết nối"
        TeacherClient -> TeacherClient: Ngắt kết nối với máy chủ
        TeacherClient -> Invigilator: Thông báo ngắt kết nối thành công
    end
end

@enduml
