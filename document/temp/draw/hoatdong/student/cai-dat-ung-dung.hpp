@startuml
|Thí sinh|
start

if (Chọn chức năng cài đặt ứng dụng) then (Có)
    |Student Client|
    :Hiển thị màn hình cài đặt ứng dụng;

    |Thí sinh|
    if (Chế độ giao diện?) then (Có)
        :Chọn chế độ giao diện (Sáng, Tối, Hệ thống);
        |Student Client|
        :Áp dụng chế độ giao diện đã chọn;
    else (Không)
    endif

    |Thí sinh|
    if (Chọn màu chủ đề?) then (Có)
        :Chọn màu chủ đề yêu thích;
        |Student Client|
        :Áp dụng màu chủ đề đã chọn;
    else (Không)
    endif

    |Thí sinh|
    if (Chọn hướng văn bản?) then (Có)
        :Chọn hướng văn bản (Trái phải, Phải trái);
        |Student Client|
        :Áp dụng hướng văn bản đã chọn;
    else (Không)
        :Quay lại màn hình chính;
    endif

    |Thí sinh|
    if (Cài đặt nâng cao?) then (Có)
        :Nhập mật khẩu xác nhận;
        if (Mật khẩu đúng?) then (Có)
            :Sửa số máy;
            |Student Client|
            :Cập nhật số máy vào hệ thống;
        else (Không)
            :Thông báo mật khẩu không đúng;
        endif
    else (Không)
    endif
else (Không)
endif

|Thí sinh|
stop
@enduml
