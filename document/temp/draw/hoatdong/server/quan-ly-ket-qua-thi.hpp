@startuml
|Quản trị hệ thống|
start
:Chọn chức năng Quản lý kết quả thi;
:Chọn kỳ thi;
|Hệ thống|
if (Kỳ thi hợp lệ?) then (Không)
    :Thông báo lỗi;
    stop
else (Có)
endif

|Quản trị hệ thống|
if (Xem kết quả thi?) then (Có)
    :Chọn thí sinh hoặc phòng thi;
    |Hệ thống|
    :Truy vấn kết quả thi;
    :Hiển thị kết quả thi;
else (Không)
endif

|Quản trị hệ thống|
if (Phúc khảo kết quả?) then (Có)
    :Chọn bài thi cần phúc khảo;
    :Nhập kết quả sau phúc khảo và lý do;
    |Hệ thống|
    :Cập nhật kết quả sau phúc khảo;
    :Thông báo kết quả phúc khảo;
else (Không)
endif

|Quản trị hệ thống|
if (Xuất kết quả thi?) then (Có)
    :Chọn kỳ thi, thí sinh hoặc danh sách phòng thi;
    |Hệ thống|
    :Xuất kết quả thi ra file Excel;
    :Thông báo thành công;
else (Không)
endif

stop
@enduml
