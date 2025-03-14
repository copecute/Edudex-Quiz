@startuml
|Thí sinh|
start
:Nhận kết quả bài thi (số câu đúng, điểm số) từ Teacher Client;
|Student Client|
:Hiển thị kết quả bài thi (số câu đúng, điểm số);

|Thí sinh|
:Chọn hành động;

|Student Client|
if (Chọn "Kết thúc"?) then (Có)
    :Quay lại trang tổng quan;
else (Không)
    if (Chọn "In kết quả"?) then (Có)
        :Hiển thị tùy chọn lưu file hoặc in kết quả;
        :Chọn "Lưu file" hoặc "In kết quả";
        if (Lưu file?) then (Có)
            :Lưu kết quả bài thi vào file;
        else (In kết quả?)
            :In kết quả bài thi;
        endif
    else (Không)
        :Quay lại trang tổng quan;
    endif
endif

stop
@enduml
