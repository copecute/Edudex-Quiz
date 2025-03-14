@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý ca thi;
:Chọn kỳ thi;
|Hệ thống|
if (Kỳ thi hợp lệ?) then (Không)
    :Thông báo lỗi;
    stop
else (Có)
endif

|Quản trị hệ thống|
if (Thêm ca thi mới?) then (Có)
    :Nhập thông tin ca thi (tên ca, ngày thi, giờ bắt đầu, giờ kết thúc, phòng thi);
    :Chọn đề thi áp dụng;
    |Hệ thống|
    :Kiểm tra thông tin ca thi;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu ca thi vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Chỉnh sửa ca thi?) then (Có)
    :Chọn ca thi cần chỉnh sửa;
    :Cập nhật thông tin ca thi;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Xóa ca thi?) then (Có)
    :Chọn ca thi cần xóa;
    |Hệ thống|
    :Xác nhận xóa ca thi;
    :Cập nhật danh sách ca thi;
else (Không)
endif

|Quản trị hệ thống|
if (Khóa/Mở khóa ca thi?) then (Có)
    :Chọn ca thi cần khóa/mở khóa;
    |Hệ thống|
    :Cập nhật trạng thái ca thi;
    :Thông báo thành công;
else (Không)
endif

|Quản trị hệ thống|
if (Nhập danh sách ca thi từ Excel?) then (Có)
    :Tải lên tệp Excel;
    |Hệ thống|
    :Kiểm tra định dạng tệp;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu danh sách ca thi vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xuất danh sách ca thi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách ca thi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
