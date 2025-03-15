@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý ngành;

if (Thêm ngành mới?) then (Có)
    :Nhập thông tin ngành;
    |Hệ thống|
    :Kiểm tra thông tin;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu thông tin ngành vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa ngành?) then (Có)
    :Chọn ngành cần chỉnh sửa;
    :Cập nhật thông tin ngành;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa ngành?) then (Có)
    :Chọn ngành cần xóa;
    |Hệ thống|
    :Xác nhận xóa ngành;
    :Cập nhật danh sách ngành;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách ngành từ Excel?) then (Có)
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
if (Xuất danh sách ngành ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách ngành;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
