@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> XemKetQua

state "Xem kết quả bài thi" as XemKetQua {
    [*] --> HienThiDanhSachBaiNop : Hiển thị danh sách bài thi đã nộp

    state "Chi tiết bài thi" as ChiTietBaiThi {
        [*] --> ChonBaiThi
        ChonBaiThi --> HienThiChiTiet
        HienThiChiTiet --> [*]
    }

    HienThiDanhSachBaiNop --> ChiTietBaiThi : Xem chi tiết bài thi

    state "Nộp tất cả bài thi" as NopTatCa {
        [*] --> GuiYeuCau
        GuiYeuCau --> KiemTraBaiThi
        KiemTraBaiThi --> CoLoi : Có lỗi
        CoLoi --> HienThiLoi
        HienThiLoi --> NhanThuLai
        NhanThuLai --> GuiLai
        GuiLai --> LuuBaiThi : Lưu vào DB
        LuuBaiThi --> ThongBaoThanhCong
        ThongBaoThanhCong --> [*]

        KiemTraBaiThi --> KhongLoi : Không có lỗi
        KhongLoi --> LuuBaiThi
    }

    HienThiDanhSachBaiNop --> NopTatCa : Nộp tất cả bài thi
}

@enduml
