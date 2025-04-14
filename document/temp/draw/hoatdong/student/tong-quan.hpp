@startuml
|Thí sinh|
start
:Khởi động ứng dụng Student Client;
:Nhập thông tin đăng nhập và đăng nhập thành công;
|Student Client|
:Gửi yêu cầu lấy dữ liệu thi từ Teacher Client;
|Teacher Client|
:Nhận yêu cầu lấy dữ liệu thi;
:Truy vấn dữ liệu kỳ thi, ca thi, đề thi, phòng thi;
:Trả về dữ liệu kỳ thi, ca thi, đề thi, phòng thi cho Student Client;
|Student Client|
:Nhận dữ liệu kỳ thi, ca thi, đề thi, phòng thi;
:Hiển thị thông tin kỳ thi, ca thi, đề thi, phòng thi;
:Hiển thị thông tin thí sinh (số báo danh, tên, mã sinh viên);
stop
@enduml
