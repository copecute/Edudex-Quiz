@startuml
|Cán bộ coi thi|
start

if (Chọn chức năng xem lịch sử bài thi) then (Có)
    |Teacher Client|
    :Hiển thị danh sách file đã mở lịch sử;

    |Cán bộ coi thi|
    if (Mở file kết quả?) then (Có)
        :Chọn bài thi cần mở;
        |Teacher Client|
        :Mở file kết quả bài thi;
        :Hiển thị kết quả bài thi;
    else (Không)
    endif

    |Cán bộ coi thi|
    if (In kết quả?) then (Có)
        :Chọn bài thi cần in;
        |Teacher Client|
        :Gửi yêu cầu in kết quả bài thi;
        :In kết quả bài thi;
        :Thông báo in thành công;
    else (Không)
    endif
else (Không)
endif

stop
@enduml
