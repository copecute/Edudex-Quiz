@startuml
|Người dùng|
start
:Chọn chức năng Đăng nhập;
:Nhập thông tin đăng nhập;
|Hệ thống|
:Kiểm tra tài khoản;
if (Thông tin hợp lệ?) then (Không)
    :Thông báo lỗi;
    :trở về trang đăng nhập;
else (Có)
    :Chuyển đến trang chính;
endif

|Người dùng|
if (Đổi thông tin cá nhân?) then (Có)
    :Nhập thông tin cần thay đổi;
    |Hệ thống|
    :Kiểm tra thông tin hợp lệ;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Cập nhật thông tin cá nhân;
        :Thông báo thành công;
    endif
else (Không)
endif

|Người dùng|
if (Đổi mật khẩu?) then (Có)
    :Nhập mật khẩu cũ, mật khẩu mới;
    |Hệ thống|
    :Kiểm tra mật khẩu cũ;
    if (Mật khẩu đúng?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Cập nhật mật khẩu mới;
        :Thông báo thành công;
    endif
else (Không)
endif
|Người dùng|
stop
@enduml
