@startuml
|Cán bộ coi thi|
start

if (Chọn chức năng quản lý phòng thi) then (Có)
    |Teacher Client|
    :Hiển thị thông tin phòng thi;

    |Cán bộ coi thi|
    if (Bật máy chủ) then (Có)
        |Teacher Client|
        :Khởi động máy chủ phòng thi;
        :Thông báo máy chủ đã được bật;
    else (Không)
    endif

    |Cán bộ coi thi|
    if (Cấu hình máy chủ) then (Có)
        :Nhập danh sách IP cho phép và IP cấm;
        |Teacher Client|
        :Cập nhật cấu hình IP cho phép và IP cấm;
        :Thông báo cấu hình thành công;
    else (Không)
    endif

    |Cán bộ coi thi|
    if (Xem danh sách máy thi) then (Có)
        |Teacher Client|
        :Hiển thị danh sách máy thi;
    else (Không)
    endif
else (Không)
endif
    |Cán bộ coi thi|
stop
@enduml
