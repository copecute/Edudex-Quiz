@startuml
actor "Thí sinh" as Student
boundary "Student Client" as StudentClient

Student -> StudentClient: Chọn chức năng cài đặt ứng dụng
alt Chọn cài đặt ứng dụng
    StudentClient -> StudentClient: Hiển thị màn hình cài đặt ứng dụng
    
    alt Chế độ giao diện
        Student -> StudentClient: Chọn chế độ giao diện (Sáng, Tối, Hệ thống)
        StudentClient -> StudentClient: Áp dụng chế độ giao diện đã chọn
    end

    alt Chọn màu chủ đề
        Student -> StudentClient: Chọn màu chủ đề yêu thích
        StudentClient -> StudentClient: Áp dụng màu chủ đề đã chọn
    end

    alt Chọn hướng văn bản
        Student -> StudentClient: Chọn hướng văn bản (Trái phải, Phải trái)
        StudentClient -> StudentClient: Áp dụng hướng văn bản đã chọn
    end

    alt Cài đặt nâng cao
        Student -> StudentClient: Nhập mật khẩu xác nhận
        alt Mật khẩu đúng
            StudentClient -> StudentClient: Sửa số máy
            StudentClient -> StudentClient: Cập nhật số máy vào hệ thống
        else Mật khẩu sai
            StudentClient -> StudentClient: Thông báo mật khẩu không đúng
        end
    end
end

@enduml
