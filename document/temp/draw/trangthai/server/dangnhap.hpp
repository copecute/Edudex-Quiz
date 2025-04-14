@startuml
top to bottom direction
hide empty description
state "Nhập thông tin đăng nhập" as LoginInfo
state "Gửi yêu cầu đăng nhập" as SendLoginRequest
state "Kiểm tra tài khoản" as CheckAccount
state "Đăng nhập thành công" as SuccessfulLogin
state "Đăng nhập không thành công" as FailedLogin
state "Chọn 'Quên mật khẩu'" as ForgotPassword
state "Gửi yêu cầu đặt lại mật khẩu" as SendResetRequest
state "Kiểm tra email" as CheckEmail
state "Gửi email đặt lại mật khẩu" as SendResetEmail
state "Nhập mật khẩu mới" as EnterNewPassword
state "Cập nhật mật khẩu" as UpdatePassword
state "Đổi mật khẩu thành công" as PasswordChanged

[*] --> LoginInfo
LoginInfo --> SendLoginRequest
SendLoginRequest --> CheckAccount
CheckAccount --> SuccessfulLogin : Đăng nhập thành công
CheckAccount --> FailedLogin : Đăng nhập không thành công
FailedLogin --> [*]
SuccessfulLogin --> ForgotPassword
ForgotPassword --> SendResetRequest
SendResetRequest --> CheckEmail
CheckEmail --> SendResetEmail
SendResetEmail --> EnterNewPassword
EnterNewPassword --> UpdatePassword
UpdatePassword --> PasswordChanged
PasswordChanged --> [*]
@enduml
