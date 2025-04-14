@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý tài khoản;

if (Thêm tài khoản mới?) then (Có)
    :Nhập thông tin tài khoản;
    |Hệ thống|
    :Kiểm tra thông tin;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu tài khoản vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa tài khoản?) then (Có)
    :Chọn tài khoản cần chỉnh sửa;
    :Cập nhật thông tin tài khoản;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa tài khoản?) then (Có)
    :Chọn tài khoản cần xóa;
    |Hệ thống|
    :Xác nhận xóa tài khoản;
    :Cập nhật danh sách tài khoản;
else (Không)
endif

|Quản trị hệ thống|
if (Khóa/Mở khóa tài khoản?) then (Có)
    :Chọn tài khoản cần khóa/mở khóa;
    |Hệ thống|
    :Cập nhật trạng thái tài khoản;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách tài khoản từ Excel?) then (Có)
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
if (Xuất danh sách tài khoản ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách tài khoản;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
