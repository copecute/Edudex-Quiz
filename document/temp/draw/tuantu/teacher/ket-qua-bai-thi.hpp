@startuml
actor "Cán bộ coi thi" as Teacher
boundary "Teacher Client" as TeacherClient
control "Server" as Server
database "Database" as DB

Teacher -> TeacherClient: Chọn chức năng xem kết quả bài thi
alt Chọn xem kết quả
    TeacherClient -> TeacherClient: Hiển thị danh sách bài thi đã nộp của thí sinh
    
    alt Xem chi tiết bài thi
        Teacher -> TeacherClient: Chọn bài thi cần xem
        TeacherClient -> TeacherClient: Hiển thị chi tiết bài thi
    end

    alt Nộp tất cả bài thi lên server
        Teacher -> TeacherClient: Gửi yêu cầu nộp tất cả bài thi lên server
        TeacherClient -> Server: Gửi bài thi lên server
        Server -> DB: Kiểm tra bài thi (trùng, chưa đến giờ, hết giờ)
        
        alt Kiểm tra lỗi
            Server -> TeacherClient: Thông báo lỗi
            TeacherClient -> Teacher: Hiển thị thông báo lỗi
            TeacherClient -> Teacher: Hiển thị nút "Thử lại"
            Teacher -> TeacherClient: Nhấn "Thử lại"
            TeacherClient -> Server: Gửi lại yêu cầu nộp bài thi lên server
            Server -> DB: Lưu lại bài thi vào cơ sở dữ liệu
        else Không có lỗi
            Server -> DB: Lưu bài thi vào cơ sở dữ liệu
            Server -> TeacherClient: Thông báo nộp bài thi thành công
        end
    end
end

@enduml
