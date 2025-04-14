@startuml
|Thí sinh|
start
:Mở ứng dụng Student Client;
|Student client|
:Tự động tìm máy teacher;
|Teacher client|
:Trả về kết quả;
|Student client|
if (Kết nối thành công?) then (Có)
    |Thí sinh|
    :Hiển thị trang đăng nhập;
else (Không)

|Student client|
    :Thông báo lỗi kết nối;
endif
stop
@enduml