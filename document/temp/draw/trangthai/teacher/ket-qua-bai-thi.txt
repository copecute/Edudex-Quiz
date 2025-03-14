@startuml
|Cán bộ coi thi|
start

if (Chọn chức năng xem kết quả bài thi) then (Có)
    |Teacher Client|
    :Hiển thị danh sách bài thi đã nộp của thí sinh;

    |Cán bộ coi thi|
    if (Xem chi tiết bài thi?) then (Có)
        :Chọn bài thi cần xem;
        |Teacher Client|
        :Hiển thị chi tiết bài thi;
    else (Không)
    endif

    |Cán bộ coi thi|
    if (Nộp tất cả bài thi lên server?) then (Có)
        |Teacher Client|
        :Gửi yêu cầu nộp tất cả bài thi lên server;
        :Nhận tất cả bài thi và xử lý;
        if (Lỗi khi nộp?) then (Có)
            :Thông báo lỗi nộp bài thi không thành công;
            |Teacher Client|
            :Hiển thị nút "Thử lại";
            :Cán bộ coi thi nhấn "Thử lại";
            |Teacher Client|
            :Gửi lại yêu cầu nộp bài thi lên server;
        else (Không)
            :Thông báo nộp bài thi thành công;
        endif
    else (Không)
    endif
else (Không)
endif
    |Cán bộ coi thi|
stop
@enduml
