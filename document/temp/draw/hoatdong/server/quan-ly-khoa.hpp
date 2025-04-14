@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý khoa;

if (Thêm khoa mới?) then (Có)
    :Nhập thông tin khoa;
    |Hệ thống|
    :Kiểm tra thông tin;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu thông tin khoa vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa khoa?) then (Có)
    :Chọn khoa cần chỉnh sửa;
    :Cập nhật thông tin khoa;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa khoa?) then (Có)
    :Chọn khoa cần xóa;
    |Hệ thống|
    :Xác nhận xóa khoa;
    :Cập nhật danh sách khoa;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách khoa từ Excel?) then (Có)
    :Tải lên tệp Excel;
    |Hệ thống|
    :Kiểm tra định dạng tệp;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu danh sách vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xuất danh sách khoa ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách khoa;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
