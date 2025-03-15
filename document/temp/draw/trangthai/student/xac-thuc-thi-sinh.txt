@startuml
|Thí sinh|
start
:Khởi động ứng dụng Student Client;
:Nhập số báo danh và mã sinh viên;
|Student Client|
:Gửi yêu cầu đăng nhập đến Teacher Client;
|Teacher Client|
:Nhận yêu cầu đăng nhập;
:Kiểm tra thông tin đăng nhập;
if (Thông tin hợp lệ?) then (Có)
    :Xác nhận đăng nhập thành công;
    |Student Client|
    :Nhận xác nhận đăng nhập thành công;
    :Hiển thị trang tổng quan;
else (Không)
    |Teacher Client|
    :Kiểm tra lỗi;
    :Trả về lỗi thông tin đăng nhập không hợp lệ;
    |Student Client|
    :Hiển thị thông báo lỗi;
endif

|Thí sinh|
:Chọn đăng xuất;
|Student Client|
:Gửi yêu cầu đăng xuất đến Teacher Client;
|Teacher Client|
:Nhận yêu cầu đăng xuất;
:Xử lý yêu cầu đăng xuất;
:Trả về xác nhận đăng xuất thành công;
|Student Client|
:Nhận xác nhận đăng xuất;
:Hiển thị màn hình đăng nhập;
stop
@enduml
