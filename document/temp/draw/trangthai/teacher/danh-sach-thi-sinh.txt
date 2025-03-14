@startuml
|Cán bộ coi thi|
start

if (Chọn chức năng danh sách thí sinh) then (Có)
    |Teacher Client|
    :Hiển thị danh sách thí sinh;
else (Không)
endif
|Cán bộ coi thi|
stop
@enduml
