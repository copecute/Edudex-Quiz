@startuml
|Cán bộ coi thi|
start

if (Chọn chức năng cài đặt ứng dụng) then (Có)
    |Teacher Client|
    :Hiển thị màn hình cài đặt ứng dụng;

    |Cán bộ coi thi|
    if (Chế độ giao diện?) then (Có)
        :Chọn chế độ giao diện (Sáng, Tối, Hệ thống);
        |Teacher Client|
        :Áp dụng chế độ giao diện đã chọn;
    else (Không)
    endif

    |Cán bộ coi thi|
    if (Chọn màu chủ đề?) then (Có)
        :Chọn màu chủ đề yêu thích;
        |Teacher Client|
        :Áp dụng màu chủ đề đã chọn;
    else (Không)
    endif

    |Cán bộ coi thi|
    if (Chọn hướng văn bản?) then (Có)
        :Chọn hướng văn bản (Trái phải, Phải trái);
        |Teacher Client|
        :Áp dụng hướng văn bản đã chọn;
    else (Không)
        :Quay lại màn hình chính;
    endif

    |Cán bộ coi thi|
    if (Ngắt kết nối máy chủ?) then (Có)
        :Nhấn nút "Ngắt kết nối";
        |Teacher Client|
        :Ngắt kết nối với máy chủ;
        :Thông báo ngắt kết nối thành công;
    else (Không)
    endif
else (Không)
endif

|Cán bộ coi thi|
stop
@enduml
