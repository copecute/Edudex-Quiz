@startuml
hide empty description
[*] --> KhởiTạoPhòngThi

state "Quản lý Phòng thi" as QLPT {
    KhởiTạoPhòngThi --> ĐangChờThêm : Chọn chức năng QL phòng thi
    ĐangChờThêm --> ĐangKiểmTra : Nhập thông tin phòng thi
    ĐangKiểmTra --> LỗiThêm : Thông tin không hợp lệ
    ĐangKiểmTra --> ĐãThêm : Thông tin hợp lệ

    ĐãThêm --> ĐãChỉnhSửa : Chỉnh sửa thông tin
    ĐãThêm --> ĐãXóa : Xóa phòng thi
    ĐãThêm --> Khóa : Khóa phòng thi
    Khóa --> MởKhóa : Mở khóa phòng thi

    ĐãThêm --> NhậpExcel : Nhập từ Excel
    NhậpExcel --> LỗiExcel : Định dạng không hợp lệ
    NhậpExcel --> ĐãThêm : Nhập thành công

    ĐãThêm --> XuấtExcel : Xuất ra Excel
    XuấtExcel --> ĐãThêm

    LỗiThêm --> ĐangChờThêm
    LỗiExcel --> NhậpExcel
}

ĐãChỉnhSửa --> [*]
ĐãXóa --> [*]
MởKhóa --> [*]
@enduml