@startuml
top to bottom direction
hide empty description

state "Chọn chức năng Quản lý địa điểm thi" as ManageLocation
state "Thêm địa điểm thi mới" as AddLocation
state "Chỉnh sửa địa điểm thi" as EditLocation
state "Xóa địa điểm thi" as DeleteLocation
state "Khóa/Mở khóa địa điểm thi" as LockUnlockLocation
state "Nhập danh sách địa điểm từ Excel" as ImportFromExcel
state "Xuất danh sách địa điểm ra Excel" as ExportToExcel

[*] --> ManageLocation
ManageLocation --> AddLocation
ManageLocation --> EditLocation
ManageLocation --> DeleteLocation
ManageLocation --> LockUnlockLocation
ManageLocation --> ImportFromExcel
ManageLocation --> ExportToExcel

AddLocation --> [*]
EditLocation --> [*]
DeleteLocation --> [*]
LockUnlockLocation --> [*]
ImportFromExcel --> [*]
ExportToExcel --> [*]

@enduml
