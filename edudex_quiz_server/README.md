# EduDex Server

EduDex Server là thành phần trung tâm của hệ thống thi trắc nghiệm EduDex, đảm nhiệm việc quản lý dữ liệu và điều phối hoạt động giữa các client. Server được thiết kế để hoạt động trong môi trường mạng LAN, đảm bảo tính bảo mật và hiệu năng cao.

## Kiến trúc hệ thống

### Backend
- **Ngôn ngữ**: PHP ^8.x
- **Framework**: Laravel ^10.x
- **Database**: MySQL ^8.0
- **Web Server**: Apache/Nginx
- **Cache**: Redis
- **WebSocket**: Laravel WebSockets

### Cấu trúc thư mục
edudex-server/
├── app/
│   ├── Http/
│   ├── Models/
│   ├── Services/
│   └── WebSockets/
├── database/
│   ├── migrations/
│   └── seeders/
├── config/
├── routes/
└── resources/

## Tính năng chi tiết

### 1. Quản lý người dùng
- Phân quyền RBAC (Role-Based Access Control)
- Quản lý tài khoản giáo viên và học sinh
- Xác thực đa yếu tố
- Theo dõi hoạt động người dùng

### 2. Quản lý ngân hàng câu hỏi
- CRUD câu hỏi và đáp án
- Phân loại theo môn học, chương, mức độ
- Hỗ trợ câu hỏi đa phương tiện
- Nhập/xuất từ Excel
- Lưu trữ tài nguyên media

### 3. Quản lý đề thi
- Tạo đề thi tự động/thủ công
- Phân phối đề thi theo phòng
- Mã hóa nội dung đề thi
- Quản lý thời gian thi
- Backup đề thi

### 4. Quản lý kỳ thi
- Lập kế hoạch thi
- Phân công giám thị
- Theo dõi tiến độ thi
- Xử lý sự cố
- Thống kê báo cáo

### 5. Xử lý dữ liệu
- Chấm điểm tự động
- Phân tích kết quả
- Xuất báo cáo đa dạng
- Sao lưu và khôi phục
- Đồng bộ dữ liệu realtime

## Yêu cầu hệ thống

### Phần cứng tối thiểu
- CPU: 4 cores
- RAM: 8GB
- Ổ cứng: 100GB SSD
- Card mạng: 1Gbps

### Phần mềm
- Hệ điều hành: Ubuntu ^20.04 LTS/Windows Server ^2019
- PHP ^8.x
- MySQL ^8.0
- Apache/Nginx
- Redis
- Composer
- Git

## Cài đặt và triển khai
 (cập nhật sau)

## Bảo mật

### Mã hóa dữ liệu
- SSL/TLS cho kết nối
- Mã hóa dữ liệu nhạy cảm
- Mã hóa đề thi và đáp án

### Kiểm soát truy cập
- Firewall cấu hình chặt chẽ
- Rate limiting
- IP whitelisting
- Session management

### Logging và Monitoring
- Log hoạt động người dùng
- Log hệ thống
- Cảnh báo bất thường
- Backup tự động

## API Documentation
(cập nhật sau)

## Troubleshooting

### Các vấn đề thường gặp
1. Kết nối database
2. Cấu hình WebSocket
3. Permission issues
4. Memory limits

### Khắc phục
- Kiểm tra log
- Verify cấu hình
- Restart services
- Clear cache

## Maintenance

### Backup
- Database: hàng ngày
- File uploads: hàng tuần
- Cấu hình: khi thay đổi

### Updates
- Security patches: ngay lập tức
- Minor updates: hàng tháng
- Major updates: theo kế hoạch

## Hỗ trợ

### Tài liệu
- API Documentation
- Deployment Guide
- Security Guidelines
- Troubleshooting Guide

### Liên hệ
- Email: admin@minhgiang.pro
- Hotline: 088-888-9530
- Website: https://minhgiang.pro

## License

© 2024 EduDex. All rights reserved.