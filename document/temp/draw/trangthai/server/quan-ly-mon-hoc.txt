@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý môn học;

if (Thêm môn học mới?) then (Có)
    :Nhập thông tin môn học;
    |Hệ thống|
    :Kiểm tra thông tin;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu thông tin môn học vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa môn học?) then (Có)
    :Chọn môn học cần chỉnh sửa;
    :Cập nhật thông tin môn học;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa môn học?) then (Có)
    :Chọn môn học cần xóa;
    |Hệ thống|
    :Xác nhận xóa môn học;
    :Cập nhật danh sách môn học;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách môn học từ Excel?) then (Có)
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
if (Xuất danh sách môn học ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách môn học;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
