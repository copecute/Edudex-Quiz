@startuml
|Giáo viên|
start
:Chọn chức năng Quản lý câu hỏi;

if (Thêm câu hỏi mới?) then (Có)
    :Nhập nội dung câu hỏi, đáp án;
    |Hệ thống|
    :Kiểm tra thông tin;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu câu hỏi vào ngân hàng câu hỏi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Giáo viên|
if (Chỉnh sửa câu hỏi?) then (Có)
    :Chọn câu hỏi cần chỉnh sửa;
    :Cập nhật nội dung, đáp án;
    |Hệ thống|
    :Lưu thay đổi vào cơ sở dữ liệu;
    :Thông báo thành công;
else (Không)
endif

|Giáo viên|
if (Xóa câu hỏi?) then (Có)
    :Chọn câu hỏi cần xóa;
    |Hệ thống|
    :Xác nhận xóa câu hỏi;
    :Cập nhật ngân hàng câu hỏi;
else (Không)
endif

|Giáo viên|
if (Nhập danh sách câu hỏi từ Excel?) then (Có)
    :Tải lên tệp Excel;
    |Hệ thống|
    :Kiểm tra định dạng tệp;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu danh sách câu hỏi vào ngân hàng câu hỏi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Giáo viên|
if (Xuất danh sách câu hỏi ra Excel?) then (Có)
    |Hệ thống|
    :Tạo tệp Excel chứa danh sách câu hỏi;
    :Cung cấp tệp để tải về;
else (Không)
endif

stop
@enduml
