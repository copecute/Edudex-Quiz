@startuml
|Cán bộ coi thi|
start

if (Chọn chức năng quản lý đề thi) then (Có)
    |Teacher Client|
    :Hiển thị thông tin đề thi;
    
    |Cán bộ coi thi|
    :Chọn kiểm tra đề thi;
    |Teacher Client|
    :Kiểm tra các tiêu chí đề thi (đủ điều kiện không);
    if (Đề thi hợp lệ?) then (Có)
        :Hiển thị thông tin đề thi;
    else (Không)
        :Thông báo lỗi đề thi không hợp lệ;
    endif
else (Không)
endif

    |Cán bộ coi thi|
    if (Tìm kiếm và lọc câu hỏi) then (Có)
        :Nhập từ khóa tìm kiếm hoặc chọn bộ lọc;
        |Teacher Client|
        :Lọc câu hỏi theo điều kiện đã chọn;
        |Teacher Client|
        :Hiển thị danh sách câu hỏi đã lọc;
    else (Không)
    endif
|Cán bộ coi thi|
stop
@enduml
