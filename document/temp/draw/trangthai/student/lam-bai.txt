@startuml
|Thí sinh|
start
:Hoàn thành bài thi;
:Nhấn nút "Nộp bài" trên Student Client;
|Student Client|
:Gửi dữ liệu bài thi (câu trả lời) tới Teacher Client;
|Teacher Client|
:Nhận dữ liệu bài thi từ Student Client;
:Kiểm tra dữ liệu bài thi (kiểm tra lỗi);
if (Có lỗi?) then (Có)
    :Trả về thông báo lỗi cho Student Client;
    :Hiển thị tùy chọn lưu file bài thi;
    |Student Client|
    :Hiển thị thông báo lỗi và yêu cầu lưu bài thi;
    
    |Thí sinh|
    :Chọn lưu bài thi vào file;
    |Student Client|
    :Lưu kết quả ra file;
else (Không)
|Teacher Client|
    :Ghi nhận bài làm và tính kết quả;
    :Trả lại kết quả (số câu đúng, điểm) cho Student Client;
    |Student Client|
    :Nhận kết quả bài thi;
    :Chuyển tới màn hình kết quả;
endif
|Teacher Client|
stop
@enduml
