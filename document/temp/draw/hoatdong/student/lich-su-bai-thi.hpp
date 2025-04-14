@startuml
|Thí sinh|
start

if (Chọn chức năng xem lịch sử bài thi) then (Có)
    |Student Client|
    :Hiển thị danh sách file đã mở lịch sử;

    |Thí sinh|
    if (Mở file kết quả?) then (Có)
        :Chọn bài thi cần mở;
        |Student Client|
        :Mở file kết quả bài thi;
        :Hiển thị kết quả bài thi;
    else (Không)
    endif

    |Thí sinh|
    if (In kết quả?) then (Có)
        :Chọn bài thi cần in;
        |Student Client|
        :Gửi yêu cầu in kết quả bài thi;
        :In kết quả bài thi;
        :Thông báo in thành công;
    else (Không)
    endif
else (Không)
endif
    |Thí sinh|
stop
@enduml
