@startuml
|Người dùng|
start
:Truy cập chức năng Báo cáo kết quả thi;
:Chọn kỳ thi;
|Hệ thống|
:Hiển thị trang báo cáo;

|Người dùng|
if (Xem báo cáo theo môn thi?) then (Có)
    |Hệ thống|
    :Hiển thị thống kê theo môn thi;
else (Không)
endif

    |Người dùng|
    if (Xem chi tiết môn thi?) then (Có)
        :Click vào môn thi cụ thể;
        |Hệ thống|
        :Tải dữ liệu bằng AJAX;
        :Hiển thị modal chi tiết môn thi;
    else (Không)
    endif

|Người dùng|
if (Xem báo cáo theo phòng thi?) then (Có)
    |Hệ thống|
    :Hiển thị thống kê theo phòng thi;
else (Không)
endif

    |Người dùng|
    if (Xem chi tiết phòng thi?) then (Có)
        :Click vào phòng thi cụ thể;
        |Hệ thống|
        :Tải dữ liệu bằng AJAX;
        :Hiển thị modal chi tiết phòng thi;
    else (Không)
    endif

|Người dùng|
if (Xem báo cáo xếp hạng thí sinh?) then (Có)
    |Hệ thống|
    :Hiển thị form tìm kiếm và lọc;
    |Người dùng|
    :Thiết lập các bộ lọc (môn thi, tìm kiếm, top X...);
    :Gửi yêu cầu tìm kiếm;
    |Hệ thống|
    :Tính toán và hiển thị bảng xếp hạng;
else (Không)
endif

|Người dùng|
if (Xem báo cáo tỷ lệ hoàn thành?) then (Có)
    |Hệ thống|
    :Tính toán tỷ lệ hoàn thành;
    :Hiển thị biểu đồ tỷ lệ hoàn thành;
    :Hiển thị bảng thống kê chi tiết;
else (Không)
endif

|Người dùng|
if (Xuất báo cáo?) then (Có)
    if (Chọn định dạng file?) then (Excel)
        |Hệ thống|
        :Tạo file Excel với dữ liệu báo cáo;
        :Tải xuống file Excel;
    else (Word)
        |Hệ thống|
        :Tạo file Word với dữ liệu báo cáo;
        :Tải xuống file Word;
    endif
else (Không)
endif
|Người dùng|
stop
@enduml
