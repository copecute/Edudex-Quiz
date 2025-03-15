@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý phòng thi;

if (Thêm phòng thi mới?) then (Có)
    :Nhập thông tin phòng thi;
    |Hệ thống|
    :Kiểm tra thông tin;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu phòng thi vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa phòng thi?) then (Có)
    :Chọn phòng thi cần chỉnh sửa;
    :Cập nhật thông tin phòng thi;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa phòng thi?) then (Có)
    :Chọn phòng thi cần xóa;
    |Hệ thống|
    :Xác nhận xóa phòng thi;
    :Cập nhật danh sách phòng thi;
else (Không)
endif

|Quản trị hệ thống|
if (Khóa/Mở khóa phòng thi?) then (Có)
    :Chọn phòng thi cần khóa/mở khóa;
    |Hệ thống|
    :Cập nhật trạng thái phòng thi;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách phòng thi từ Excel?) then (Có)
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
if (Xuất danh sách phòng thi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách phòng thi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml