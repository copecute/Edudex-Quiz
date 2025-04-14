@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý môn thi;
:Chọn kỳ thi;
|Hệ thống|
if (Kỳ thi hợp lệ?) then (Không)
    :Thông báo lỗi;
    stop
else (Có)
endif

|Quản trị hệ thống|
if (Phân công môn thi?) then (Có)
    :Chọn môn học từ danh sách có sẵn;
    |Hệ thống|
    :Ghi nhận môn học vào kỳ thi;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa môn thi?) then (Có)
    :Chọn môn thi cần chỉnh sửa;
    :Cập nhật thông tin môn thi;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa môn thi?) then (Có)
    :Chọn môn thi cần xóa;
    |Hệ thống|
    :Xác nhận xóa môn thi khỏi kỳ thi;
    :Cập nhật danh sách môn thi;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách môn thi từ Excel?) then (Có)
    :Tải lên tệp Excel;
    |Hệ thống|
    :Kiểm tra định dạng tệp;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Thêm danh sách môn thi vào kỳ thi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xuất danh sách môn thi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách môn thi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml