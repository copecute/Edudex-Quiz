@startuml
actor "Cán bộ coi thi" as Invigilator
boundary "Giao diện" as UI
control "Hệ thống" as System

Invigilator -> UI: Chọn chức năng Xem lịch phân công
UI -> System: Gửi yêu cầu xem lịch phân công
System -> DB: Truy vấn danh sách phân công của người dùng
DB --> System: Kết quả truy vấn
System -> UI: Hiển thị danh sách phân công của người dùng
@enduml