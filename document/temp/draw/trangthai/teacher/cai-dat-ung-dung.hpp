@startuml
top to bottom direction
hide empty description
skinparam state {
    BackgroundColor White
    BorderColor Black
    ArrowColor Black
}


    [*] --> HienThiManHinhCaiDat : Hiển thị màn hình cài đặt

    state "Giao diện" as GiaoDien {
        [*] --> ChonCheDo
        ChonCheDo --> ApDungCheDo
        ApDungCheDo --> [*]
    }

    HienThiManHinhCaiDat --> GiaoDien : Chọn chế độ giao diện

    state "Màu chủ đề" as MauChuDe {
        [*] --> ChonMau
        ChonMau --> ApDungMau
        ApDungMau --> [*]
    }

    HienThiManHinhCaiDat --> MauChuDe : Chọn màu chủ đề

    state "Hướng văn bản" as HuongVanBan {
        [*] --> ChonHuong
        ChonHuong --> ApDungHuong
        ApDungHuong --> [*]
    }

    HienThiManHinhCaiDat --> HuongVanBan : Chọn hướng văn bản

    state "Ngắt kết nối" as NgatKetNoi {
        [*] --> NhanNutNgat
        NhanNutNgat --> ThucHienNgat
        ThucHienNgat --> TBNgatThanhCong
        TBNgatThanhCong --> [*]
    }

    HienThiManHinhCaiDat --> NgatKetNoi : Ngắt kết nối máy chủ

@enduml
