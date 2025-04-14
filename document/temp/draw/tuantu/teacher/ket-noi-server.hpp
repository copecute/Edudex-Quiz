@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Teacher client" as TeacherClient
control "Server" as Server
database "Database" as DB

Invigilator -> TeacherClient: Mở ứng dụng Teacher Client
TeacherClient -> Server: Tự động tìm server
Server --> TeacherClient: Trả về kết quả
TeacherClient -> Invigilator: Hiển thị trang đăng nhập

alt Kết nối thành công
    Invigilator -> TeacherClient: Nhập thông tin đăng nhập
    TeacherClient -> Server: Gửi yêu cầu xác thực
    Server -> DB: Kiểm tra thông tin đăng nhập
    DB --> Server: Trả về kết quả kiểm tra
    Server --> TeacherClient: Trả về kết quả đăng nhập
    TeacherClient -> Invigilator: Hiển thị trang chính
else Kết nối không thành công
    TeacherClient -> Invigilator: Thông báo lỗi kết nối
end
@enduml
