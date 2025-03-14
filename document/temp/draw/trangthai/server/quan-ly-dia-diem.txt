@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý địa điểm thi;

if (Thêm địa điểm thi mới?) then (Có)
    :Nhập thông tin địa điểm thi;
    |Hệ thống|
    :Kiểm tra thông tin;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu địa điểm vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa địa điểm thi?) then (Có)
    :Chọn địa điểm thi cần chỉnh sửa;
    :Cập nhật thông tin địa điểm;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa địa điểm thi?) then (Có)
    :Chọn địa điểm thi cần xóa;
    |Hệ thống|
    :Xác nhận xóa địa điểm;
    :Cập nhật danh sách địa điểm thi;
else (Không)
endif

|Quản trị hệ thống|
if (Khóa/Mở khóa địa điểm?) then (Có)
    :Chọn địa điểm thi cần khóa/mở khóa;
    |Hệ thống|
    :Cập nhật trạng thái địa điểm;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách địa điểm từ Excel?) then (Có)
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
if (Xuất danh sách địa điểm ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách địa điểm;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
