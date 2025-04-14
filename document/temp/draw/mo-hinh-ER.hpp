@startuml
' Entities
entity TaiKhoan {
    + id: bigint
    + username: varchar
    + email: varchar
    + password: varchar
    + is_active: tinyint
    + remember_token: varchar
    + role: tinyint
    + created_at: timestamp
    + updated_at: timestamp
}

entity ThongTinTaiKhoan {
    + id: bigint
    + tai_khoan_id: bigint
    + hoTen: varchar
    + ngaySinh: date
    + avatar: varchar
    + gioiTinh: tinyint
    + soDienThoai: varchar
    + diaChi: varchar
    + created_at: timestamp
    + updated_at: timestamp
}

entity CauTraLoi {
    + id: bigint
    + cauHoi_id: bigint
    + noiDung: text
    + link_media: varchar
    + is_correct: tinyint
    + created_at: timestamp
    + updated_at: timestamp
}

entity DeThi {
    + id: bigint
    + ten: varchar
    + moTa: text
    + thoiGianLamBai: int
    + tongSoCauHoi: int
    + maMonHoc: varchar
    + tiLeDe: decimal
    + tiLeTrungBinh: decimal
    + tiLeKho: decimal
    + created_at: timestamp
    + updated_at: timestamp
}

entity KyThi {
    + id: bigint
    + ten: varchar
    + moTa: text
    + thoiGianBatDau: datetime
    + thoiGianKetThuc: datetime
    + is_active: tinyint
    + created_at: timestamp
    + updated_at: timestamp
}

entity GiamThiKyThi {
    + id: bigint
    + kyThi_id: bigint
    + taiKhoan_id: bigint
    + created_at: timestamp
    + updated_at: timestamp
}

entity PhongThiKyThi {
    + id: bigint
    + kyThi_id: bigint
    + phongThi_id: bigint
    + created_at: timestamp
    + updated_at: timestamp
}

entity SinhVienPhongThi {
    + id: bigint
    + kyThi_id: bigint
    + monThi_id: bigint
    + sinhVien_id: bigint
    + phongThi_id: bigint
    + caThi_id: bigint
    + soGhe: int
    + created_at: timestamp
    + updated_at: timestamp
}

entity MonThiKyThi {
    + id: bigint
    + kyThi_id: bigint
    + monHoc_id: bigint
    + deThi_id: bigint
    + created_at: timestamp
    + updated_at: timestamp
}

entity CaThiMonThi {
    + id: bigint
    + monThi_id: bigint
    + caThi_id: bigint
    + created_at: timestamp
    + updated_at: timestamp
}

entity SinhVienMonThi {
    + id: bigint
    + kyThi_id: bigint
    + monThi_id: bigint
    + maThiSinh: varchar
    + maSinhVien: varchar
    + hoTen: varchar
    + soDienThoai: varchar
    + diaChi: text
    + ngaySinh: date
    + gioiTinh: tinyint
    + created_at: timestamp
    + updated_at: timestamp
}

entity KetQuaThi {
    + id: bigint
    + kyThi_id: bigint
    + caThi_id: bigint
    + monThi_id: bigint
    + deThi_id: bigint
    + phongThi_id: bigint
    + giamThi_id: bigint
    + sinhVien_id: bigint
    + maKyThi: varchar
    + maCaThi: varchar
    + maMonThi: varchar
    + maDeThi: varchar
    + maPhongThi: varchar
    + maGiamThi: varchar
    + maSinhVien: varchar
    + soCauDung: int
    + soCauDungSauPhucKhao: int
    + tongSoCau: int
    + diem: decimal
    + diemSauPhucKhao: decimal
    + ghiChu: text
    + ghiChuPhucKhao: text
    + thoiGianPhucKhao: timestamp
    + nguoiPhucKhao: bigint
    + log_file: longtext
    + created_at: timestamp
    + updated_at: timestamp
}

entity CaThi {
    + id: bigint
    + kyThi_id: bigint
    + ten: varchar
    + moTa: text
    + thoiGianBatDau: datetime
    + thoiGianKetThuc: datetime
    + is_active: tinyint
    + created_at: timestamp
    + updated_at: timestamp
}

entity PhongThiCaThi {
    + id: bigint
    + caThi_id: bigint
    + phongThi_id: bigint
    + monThi_id: bigint
    + giamThi_id: bigint
    + created_at: timestamp
    + updated_at: timestamp
}

entity TheDeThi {
    + id: bigint
    + deThi_id: bigint
    + the_id: bigint
    + soLuongCauHoi: int
    + tiLeDe: decimal
    + tiLeTrungBinh: decimal
    + tiLeKho: decimal
    + created_at: timestamp
    + updated_at: timestamp
}

entity CoSoVatChat {
    + id: bigint
    + ma: varchar
    + ten: varchar
    + diaChi: varchar
    + moTa: text
    + is_active: tinyint
    + created_at: timestamp
    + updated_at: timestamp
}

entity Khoa {
    + id: bigint
    + ma: varchar
    + ten: varchar
    + moTa: text
    + created_at: timestamp
    + updated_at: timestamp
}

entity Nganh {
    + id: bigint
    + ma: varchar
    + ten: varchar
    + khoa_id: bigint
    + moTa: text
    + created_at: timestamp
    + updated_at: timestamp
}

entity CauHoi {
    + id: bigint
    + noiDung: text
    + link_media: varchar
    + maMonHoc: varchar
    + doKho: enum
    + created_at: timestamp
    + updated_at: timestamp
}

entity TheCauHoi {
    + cauHoi_id: bigint
    + the_id: bigint
}

entity PhongThi {
    + id: bigint
    + ma: varchar
    + ten: varchar
    + coSo_id: bigint
    + sucChua: int
    + moTa: text
    + is_active: tinyint
    + created_at: timestamp
    + updated_at: timestamp
}

entity MonHoc {
    + id: bigint
    + ma: varchar
    + ten: varchar
    + soTinChi: int
    + nganh_id: bigint
    + moTa: text
    + created_at: timestamp
    + updated_at: timestamp
}

entity The {
    + id: bigint
    + ten: varchar
    + maMonHoc: varchar
    + created_at: timestamp
    + updated_at: timestamp
}

' Relationships
TaiKhoan ||--o{ ThongTinTaiKhoan : co
CauHoi ||--o{ CauTraLoi : co
DeThi ||--o{ TheDeThi : co
KyThi ||--o{ GiamThiKyThi : co
KyThi ||--o{ PhongThiKyThi : co
KyThi ||--o{ MonThiKyThi : co
MonThiKyThi ||--o{ CaThiMonThi : co
MonThiKyThi ||--o{ SinhVienMonThi : co
PhongThiKyThi ||--o{ SinhVienPhongThi : co
CaThi ||--o{ PhongThiCaThi : co
MonThiKyThi ||--o{ KetQuaThi : co
SinhVienMonThi ||--o{ KetQuaThi : co
CaThi ||--o{ KetQuaThi : co
PhongThiKyThi ||--o{ KetQuaThi : co
GiamThiKyThi ||--o{ KetQuaThi : co
Khoa ||--o{ Nganh : co
Nganh ||--o{ MonHoc : co
MonHoc ||--o{ CauHoi : co
MonHoc ||--o{ The : co
CauHoi ||--o{ TheCauHoi : co
The ||--o{ TheCauHoi : co
CoSoVatChat ||--o{ PhongThi : co
PhongThiKyThi ||--|| PhongThi : suDung
GiamThiKyThi ||--|| TaiKhoan : suDung
KetQuaThi ||--|| TaiKhoan : duocPhucKhaoBoi

@enduml