@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý phòng thi thuộc kỳ thi;
:Chọn kỳ thi;
|Hệ thống|
if (Kỳ thi hợp lệ?) then (Không)
    :Thông báo lỗi;
    stop
else (Có)
endif

|Quản trị hệ thống|
if (Phân công phòng thi?) then (Có)
    :Chọn phòng thi từ danh sách có sẵn;
    |Hệ thống|
    :Kiểm tra thông tin hợp lệ;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu phân công phòng thi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xóa phòng thi khỏi danh sách phân công?) then (Có)
    :Chọn phòng thi cần xóa;
    |Hệ thống|
    :Xác nhận xóa;
    :Cập nhật danh sách phòng thi;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xuất danh sách phòng thi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách phòng thi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
