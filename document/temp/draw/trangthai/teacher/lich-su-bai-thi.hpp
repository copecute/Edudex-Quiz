@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}

[*] --> XemLichSu

state "Xem lịch sử bài thi" as XemLichSu {
    [*] --> HienThiDanhSachFile : Hiển thị danh sách file lịch sử

    state "Mở file kết quả" as MoFileKetQua {
        [*] --> ChonBaiThiMo
        ChonBaiThiMo --> MoFile
        MoFile --> HienThiKetQua
        HienThiKetQua --> [*]
    }

    HienThiDanhSachFile --> MoFileKetQua : Mở file kết quả bài thi

    state "In kết quả" as InKetQua {
        [*] --> ChonBaiThiIn
        ChonBaiThiIn --> GuiYeuCauIn
        GuiYeuCauIn --> InKetQuaBaiThi
        InKetQuaBaiThi --> TBInThanhCong
        TBInThanhCong --> [*]
    }

    HienThiDanhSachFile --> InKetQua : In kết quả bài thi
}

@enduml
