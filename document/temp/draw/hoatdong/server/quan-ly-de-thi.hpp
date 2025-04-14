@startuml
|Giáo viên|
start
:Chọn chức năng Quản lý đề thi;

if (Thêm đề thi mới?) then (Có)
    :Nhập thông tin đề thi (tên, môn học, thời gian);
    :Chọn câu hỏi từ ngân hàng câu hỏi;
    |Hệ thống|
    :Kiểm tra thông tin đề thi;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu đề thi vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Giáo viên|
if (Chỉnh sửa đề thi?) then (Có)
    :Chọn đề thi cần chỉnh sửa;
    :Cập nhật thông tin, câu hỏi;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Giáo viên|
if (Xóa đề thi?) then (Có)
    :Chọn đề thi cần xóa;
    |Hệ thống|
    :Xác nhận xóa đề thi;
    :Cập nhật danh sách đề thi;
else (Không)
endif

|Giáo viên|
if (Nhập danh sách đề thi từ Excel?) then (Có)
    :Tải lên tệp Excel;
    |Hệ thống|
    :Kiểm tra định dạng tệp;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu danh sách đề thi vào cơ sở dữ liệu;
        :Thông báo thành công;
    endif
else (Không)
endif

|Giáo viên|
if (Xuất danh sách đề thi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách đề thi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
