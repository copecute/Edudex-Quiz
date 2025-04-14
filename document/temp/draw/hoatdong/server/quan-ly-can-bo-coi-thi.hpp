@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý cán bộ coi thi;
:Chọn kỳ thi;
|Hệ thống|
if (Kỳ thi hợp lệ?) then (Không)
    :Thông báo lỗi;
    stop
else (Có)
endif

|Quản trị hệ thống|
if (Phân công cán bộ coi thi?) then (Có)
    :Chọn phòng thi;
    :Chọn cán bộ coi thi từ danh sách tài khoản;
    |Hệ thống|
    :Kiểm tra thông tin hợp lệ;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu phân công;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xóa cán bộ khỏi danh sách phân công?) then (Có)
    :Chọn phòng thi;
    :Chọn cán bộ cần xóa;
    |Hệ thống|
    :Xác nhận xóa;
    :Cập nhật danh sách;
else (Không)
endif

|Quản trị hệ thống|
if (Xuất danh sách cán bộ coi thi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách cán bộ coi thi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
