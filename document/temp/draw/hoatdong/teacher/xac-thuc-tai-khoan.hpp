@startuml
|Cán bộ coi thi|
start
:Nhập username và password;
|Teacher Client|
:Gửi thông tin tài khoản đến server;
|Server|
:Kiểm tra thông tin đăng nhập;
if (Thông tin hợp lệ?) then (Có)
    :Trả về dữ liệu thành công;
    |Teacher Client|
    :Lưu dữ liệu tài khoản vào client;
    :Chọn ca thi được phân;
    |Teacher Client|
    :Gửi yêu cầu tải dữ liệu thi cho ca thi đã chọn;
    |Server|
    :Trả về dữ liệu thi cho ca thi đã chọn;
    |Teacher Client|
    :Lưu dữ liệu thi vào client;
    :Gửi yêu cầu tải danh sách thí sinh;
    |Server|
    :Trả về danh sách thí sinh;
    |Teacher Client|
    :Lưu danh sách thí sinh vào client;
    :Hiển thị trang tổng quan;

    |Cán bộ coi thi|
    if (Đăng xuất?) then (Có)
        :Nhấn nút "Đăng xuất";
        |Teacher Client|
        :Xóa dữ liệu tài khoản và dữ liệu liên quan khỏi client;
        :Gửi yêu cầu đăng xuất đến server;
        |Server|
        :Xóa session đăng nhập của người dùng;
        :Trả về kết quả đăng xuất thành công;
        |Teacher Client|
        :Hiển thị màn hình đăng nhập;
    else (Không)
    endif
else (Không)
    |Server|
    :Kiểm tra lỗi;
    :Trả về lỗi đăng nhập;
    |Teacher Client|
    :Hiển thị thông báo lỗi;
endif

|Server|
stop
@enduml
