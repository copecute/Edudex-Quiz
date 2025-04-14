@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Phân công;
:Chọn kỳ thi;
|Hệ thống|
if (Kỳ thi hợp lệ?) then (Không)
    :Thông báo lỗi;
    stop
else (Có)
endif

|Quản trị hệ thống|
if (Phân công môn thi - ca thi?) then (Có)
    :Chọn các ca thi cho môn thi;
    |Hệ thống|
    :Kiểm tra thông tin hợp lệ;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu phân công môn thi - ca thi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Phân công phòng thi - ca thi?) then (Có)
    :Chọn ca thi, cán bộ coi thi cho phòng thi;
    |Hệ thống|
    :Kiểm tra thông tin hợp lệ;
    if (Hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu phân công phòng thi - ca thi;
        :Thông báo thành công;
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Tự động phân công?) then (Có)
    :Nhập tổng số thí sinh;
    |Hệ thống|
    :Tính toán số ca thi, số môn thi, số phòng thi;
    :Kiểm tra tổng sức chứa và số CBCT;
    if (đủ điều kiện?) then (Có)
        :Tự động phân thí sinh vào phòng thi;
    if (Phân công hợp lệ?) then (Không)
        :Thông báo lỗi;
    else (Có)
        :Lưu dữ liệu phân công;
        :Thông báo thành công;
    endif
    else (Không)
    endif
else (Không)
endif

|Quản trị hệ thống|
if (Xoá dữ liệu phân công?) then (Có)
    |Hệ thống|
    :Xác nhận xoá;
    :Xoá dữ liệu phân công;
    :Thông báo thành công;
else (Không)
endif

stop
@enduml
