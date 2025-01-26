# Hệ thống thi trắc nghiệm EduDex

EduDex là hệ thống thi trắc nghiệm chuyên nghiệp dành cho các cơ sở giáo dục, cho phép tổ chức kỳ thi với nhiều phòng thi đồng thời trong môi trường mạng LAN nội bộ. Hệ thống bao gồm 3 thành phần chính: Server, Teacher Client và Student Client.

## Tổng quan hệ thống

### 1. EduDex Server
Server trung tâm quản lý toàn bộ dữ liệu và điều phối hoạt động của hệ thống.

**Tính năng chính:**
- Quản lý ngân hàng câu hỏi và đề thi
- Quản lý người dùng và phân quyền
- Điều phối kỳ thi và phòng thi
- Lưu trữ và xử lý kết quả thi
- Báo cáo và thống kê

**Công nghệ sử dụng:**
- PHP (Backend)
- MySQL (Database)
- Apache/Nginx (Web Server)
- WebSocket (Realtime Communication)

### 2. EduDex Teacher Client
Ứng dụng dành cho giáo viên và cán bộ coi thi.

**Tính năng chính:**
- Quản lý và soạn thảo câu hỏi
- Tạo và phân phối đề thi
- Giám sát quá trình thi
- Quản lý thí sinh và phòng thi
- Xem và xuất kết quả thi

**Công nghệ sử dụng:**
- Flutter/Dart
- TCP/IP (LAN)

### 3. EduDex Student Client
Ứng dụng dành cho thí sinh làm bài thi.

**Tính năng chính:**
- Giao diện làm bài thân thiện
- Hỗ trợ nhiều loại câu hỏi
- Tự động lưu bài làm
- Nộp bài an toàn
- Xem kết quả thi

**Công nghệ sử dụng:**
- Flutter/Dart
- TCP/IP (LAN)

## Ưu điểm nổi bật

### Bảo mật cao
- Hoạt động trong mạng LAN nội bộ
- Mã hóa dữ liệu end-to-end
- Xác thực nhiều lớp
- Phát hiện và ngăn chặn gian lận

### Ổn định và tin cậy
- Tự động sao lưu dữ liệu
- Khôi phục khi mất kết nối
- Đồng bộ hóa realtime
- Xử lý đồng thời nhiều phòng thi

### Dễ dàng quản lý
- Giao diện trực quan
- Tự động hóa nhiều quy trình
- Báo cáo chi tiết
- Hỗ trợ xuất dữ liệu nhiều định dạng

## Yêu cầu hệ thống

### Server
- CPU: 4 cores trở lên
- RAM: 8GB trở lên
- Ổ cứng: 100GB trở lên
- Hệ điều hành: Linux/Windows Server

### Client (Teacher & Student)
- Hệ điều hành có thể chạy được: Windows 7 trở lên (64-bit)/ Linux / MacOS / Android / iOS
- RAM: 4GB trở lên
- Độ phân giải: 1024x768 trở lên
- Mạng: Kết nối LAN ổn định

## Triển khai hệ thống

1. **Chuẩn bị hạ tầng**
   - Cấu hình mạng LAN
   - Cài đặt và cấu hình server
   - Chuẩn bị máy trạm

2. **Cài đặt phần mềm**
   - Triển khai server
   - Cài đặt client cho giáo viên
   - Cài đặt client cho học sinh

3. **Cấu hình hệ thống**
   - Thiết lập tài khoản quản trị
   - Cấu hình kết nối
   - Kiểm tra hoạt động

## Hỗ trợ kỹ thuật

### Tài liệu
- Hướng dẫn cài đặt
- Tài liệu người dùng
- Tài liệu kỹ thuật
- Video hướng dẫn

### Liên hệ
- Website: https://minhgiang.pro
- Email: admin@minhgiang.pro
- Hotline: 088-888-9530

## Giấy phép và bản quyền

© 2024 EduDex. All rights reserved.
- Không được phép sao chép, phân phối khi chưa được cho phép
- Chỉ sử dụng trong phạm vi được cấp phép
- Liên hệ để được cấp phép sử dụng thương mại

## Đối tác và khách hàng
- Trường cao đẳng công nghệ bách khoa Hà Nội (HPC)
- Các trường THPT
- Các trường Đại học/Cao đẳng
- Các trung tâm đào tạo
- Các tổ chức giáo dục

## Phát triển trong tương lai

- Tích hợp AI chấm thi tự động
- Hỗ trợ thi trực tuyến
- Phân tích học tập nâng cao
- Tích hợp với các hệ thống Edudex