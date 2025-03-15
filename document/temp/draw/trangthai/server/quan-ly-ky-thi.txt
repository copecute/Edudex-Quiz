@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý kỳ thi;

if (Thêm kỳ thi mới?) then (Có)
    :Nhập thông tin kỳ thi (tên, ngày thi, thời gian, phòng thi);
    :Chọn đề thi áp dụng;
    |Hệ thống|
    :Kiểm tra thông tin kỳ thi;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu kỳ thi vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa kỳ thi?) then (Có)
    :Chọn kỳ thi cần chỉnh sửa;
    :Cập nhật thông tin kỳ thi;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa kỳ thi?) then (Có)
    :Chọn kỳ thi cần xóa;
    |Hệ thống|
    :Xác nhận xóa kỳ thi;
    :Cập nhật danh sách kỳ thi;
else (Không)
endif

|Quản trị hệ thống|
if (Khóa/Mở khóa kỳ thi?) then (Có)
    :Chọn kỳ thi cần khóa/mở khóa;
    |Hệ thống|
    :Cập nhật trạng thái kỳ thi;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách kỳ thi từ Excel?) then (Có)
    :Tải lên tệp Excel;
    |Hệ thống|
    :Kiểm tra định dạng tệp;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu danh sách kỳ thi vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xuất danh sách kỳ thi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách kỳ thi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
