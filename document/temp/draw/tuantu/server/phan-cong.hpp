@startuml
actor "Quản trị hệ thống" as Admin
boundary "Giao diện" as UI
control "Hệ thống" as System
database "Cơ sở dữ liệu" as DB

Admin -> UI: Chọn chức năng Phân công
Admin -> UI: Chọn kỳ thi
UI -> System: Gửi yêu cầu kiểm tra kỳ thi
System -> DB: Kiểm tra kỳ thi
DB --> System: Kết quả kiểm tra
alt Kỳ thi không hợp lệ
    System -> UI: Thông báo lỗi
else Kỳ thi hợp lệ
end

alt Phân công môn thi - ca thi
    Admin -> UI: Chọn các ca thi cho môn thi
    UI -> System: Gửi yêu cầu phân công môn thi - ca thi
    System -> DB: Kiểm tra thông tin môn thi - ca thi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu phân công môn thi - ca thi
        DB --> System: Xác nhận lưu phân công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Phân công phòng thi - ca thi
    Admin -> UI: Chọn ca thi, cán bộ coi thi cho phòng thi
    UI -> System: Gửi yêu cầu phân công phòng thi - ca thi
    System -> DB: Kiểm tra thông tin phòng thi - ca thi
    DB --> System: Kết quả kiểm tra
    alt Thông tin hợp lệ
        System -> DB: Lưu phân công phòng thi - ca thi
        DB --> System: Xác nhận lưu phân công
        System -> UI: Thông báo thành công
    else Thông tin không hợp lệ
        System -> UI: Thông báo lỗi
    end
end

alt Tự động phân công
    Admin -> UI: Nhập tổng số thí sinh
    UI -> System: Gửi yêu cầu tính toán phân công
    System -> DB: Tính toán số ca thi, số môn thi, số phòng thi
    System -> DB: Kiểm tra tổng sức chứa và số cán bộ coi thi
    DB --> System: Kết quả kiểm tra
    alt Đủ điều kiện
        System -> DB: Tự động phân thí sinh vào phòng thi
        System -> DB: Kiểm tra phân công hợp lệ
        alt Phân công hợp lệ
            System -> DB: Lưu dữ liệu phân công
            DB --> System: Xác nhận lưu dữ liệu
            System -> UI: Thông báo thành công
        else Phân công không hợp lệ
            System -> UI: Thông báo lỗi
        end
    else Không đủ điều kiện
        System -> UI: Thông báo lỗi
    end
end

alt Xóa dữ liệu phân công
    Admin -> UI: Gửi yêu cầu xóa dữ liệu phân công
    UI -> System: Xác nhận xóa dữ liệu phân công
    System -> DB: Xóa dữ liệu phân công
    DB --> System: Xác nhận xóa dữ liệu
    System -> UI: Thông báo thành công
end
@enduml
