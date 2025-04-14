@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý thí sinh;
:Chọn kỳ thi;
|Hệ thống|
if (Kỳ thi hợp lệ?) then (Không)
    :Thông báo lỗi;
    stop
else (Có)
endif

|Quản trị hệ thống|
if (Thêm thí sinh mới?) then (Có)
    :Nhập thông tin thí sinh (Họ tên, SĐT, địa chỉ...);
    |Hệ thống|
    :Kiểm tra thông tin hợp lệ;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu thông tin thí sinh vào kỳ thi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa thông tin thí sinh?) then (Có)
    :Chọn thí sinh cần chỉnh sửa;
    :Cập nhật thông tin;
    |Hệ thống|
    :Lưu thay đổi;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa thí sinh khỏi kỳ thi?) then (Có)
    :Chọn thí sinh cần xóa;
    |Hệ thống|
    :Xác nhận xóa;
    :Cập nhật danh sách;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách thí sinh từ Excel?) then (Có)
    :Tải lên tệp Excel;
    |Hệ thống|
    :Kiểm tra định dạng tệp;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Thêm danh sách thí sinh vào kỳ thi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xuất danh sách thí sinh ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách thí sinh;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
