@startuml
actor "Người dùng" as User
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

User -> UI: Nhập thông tin đăng nhập
UI -> System: Gửi thông tin đăng nhập
System -> DB: Kiểm tra tài khoản
DB --> System: Trả về kết quả xác minh

alt Đăng nhập thành công
    System -> UI: Đăng nhập thành công
    UI -> User: Chuyển đến trang chính
else Đăng nhập không thành công
    System -> UI: Thông báo lỗi
    UI -> User: Hiển thị lỗi đăng nhập
end

User -> UI: Chọn "Quên mật khẩu"
UI -> System: Gửi yêu cầu đặt lại mật khẩu
System -> DB: Kiểm tra email người dùng
DB --> System: Xác nhận tài khoản tồn tại

System -> User: Gửi email đặt lại mật khẩu
User -> UI: Nhập mật khẩu mới
UI -> System: Gửi mật khẩu mới
System -> DB: Cập nhật mật khẩu
DB --> System: Xác nhận cập nhật thành công

System -> User: Thông báo đổi mật khẩu thành công
@enduml
