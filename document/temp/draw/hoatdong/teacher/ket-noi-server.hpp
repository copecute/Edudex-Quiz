@startuml
|Cán bộ coi thi|
start
:Mở ứng dụng Teacher Client;
|Teacher client|
:Tự động tìm server;
|Server|
:Trả về kết quả;
|Teacher client|
if (Kết nối thành công?) then (Có)
    |Cán bộ coi thi|
    :Hiển thị trang đăng nhập;
else (Không)

|Teacher client|
    :Thông báo lỗi kết nối;
endif
stop
@enduml