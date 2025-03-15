@startuml
' Tables
entity TaiKhoan {
    * id: bigint [PK]
    --
    username: varchar [UNIQUE]
    email: varchar [UNIQUE]
    password: varchar
    is_active: tinyint
    remember_token: varchar
    role: tinyint
    created_at: timestamp
    updated_at: timestamp
}

entity ThongTinTaiKhoan {
    * id: bigint [PK]
    --
    tai_khoan_id: bigint [FK -> TaiKhoan.id]
    hoTen: varchar
    ngaySinh: date
    avatar: varchar
    gioiTinh: tinyint
    soDienThoai: varchar
    diaChi: varchar
    created_at: timestamp
    updated_at: timestamp
}

entity CauHoi {
    * id: bigint [PK]
    --
    noiDung: text
    link_media: varchar
    maMonHoc: varchar [FK -> MonHoc.ma]
    doKho: enum
    created_at: timestamp
    updated_at: timestamp
}

entity CauTraLoi {
    * id: bigint [PK]
    --
    cauHoi_id: bigint [FK -> CauHoi.id]
    noiDung: text
    link_media: varchar
    is_correct: tinyint
    created_at: timestamp
    updated_at: timestamp
}

entity DeThi {
    * id: bigint [PK]
    --
    ten: varchar
    moTa: text
    thoiGianLamBai: int
    tongSoCauHoi: int
    maMonHoc: varchar [FK -> MonHoc.ma]
    tiLeDe: decimal
    tiLeTrungBinh: decimal
    tiLeKho: decimal
    created_at: timestamp
    updated_at: timestamp
}

entity TheDeThi {
    * id: bigint [PK]
    --
    deThi_id: bigint [FK -> DeThi.id]
    the_id: bigint [FK -> The.id]
    soLuongCauHoi: int
    tiLeDe: decimal
    tiLeTrungBinh: decimal
    tiLeKho: decimal
    created_at: timestamp
    updated_at: timestamp
}

entity KyThi {
    * id: bigint [PK]
    --
    ten: varchar
    moTa: text
    thoiGianBatDau: datetime
    thoiGianKetThuc: datetime
    is_active: tinyint
    created_at: timestamp
    updated_at: timestamp
}

entity GiamThiKyThi {
    * id: bigint [PK]
    --
    kyThi_id: bigint [FK -> KyThi.id]
    taiKhoan_id: bigint [FK -> TaiKhoan.id]
    created_at: timestamp
    updated_at: timestamp
}

entity PhongThiKyThi {
    * id: bigint [PK]
    --
    kyThi_id: bigint [FK -> KyThi.id]
    phongThi_id: bigint [FK -> PhongThi.id]
    created_at: timestamp
    updated_at: timestamp
}

entity SinhVienPhongThi {
    * id: bigint [PK]
    --
    kyThi_id: bigint [FK -> KyThi.id]
    sinhVien_id: bigint [FK -> SinhVienMonThi.id]
    phongThi_id: bigint [FK -> PhongThi.id]
    caThi_id: bigint [FK -> CaThi.id]
    soGhe: int
    created_at: timestamp
    updated_at: timestamp
}

entity MonThiKyThi {
    * id: bigint [PK]
    --
    kyThi_id: bigint [FK -> KyThi.id]
    monHoc_id: bigint [FK -> MonHoc.id]
    deThi_id: bigint [FK -> DeThi.id]
    created_at: timestamp
    updated_at: timestamp
}

entity CaThiMonThi {
    * id: bigint [PK]
    --
    monThi_id: bigint [FK -> MonThiKyThi.id]
    caThi_id: bigint [FK -> CaThi.id]
    created_at: timestamp
    updated_at: timestamp
}

entity SinhVienMonThi {
    * id: bigint [PK]
    --
    kyThi_id: bigint [FK -> KyThi.id]
    monThi_id: bigint [FK -> MonThiKyThi.id]
    maThiSinh: varchar
    maSinhVien: varchar
    hoTen: varchar
    soDienThoai: varchar
    diaChi: text
    ngaySinh: date
    gioiTinh: tinyint
    created_at: timestamp
    updated_at: timestamp
}

entity KetQuaThi {
    * id: bigint [PK]
    --
    kyThi_id: bigint [FK -> KyThi.id]
    caThi_id: bigint [FK -> CaThi.id]
    monThi_id: bigint [FK -> MonThiKyThi.id]
    deThi_id: bigint [FK -> DeThi.id]
    phongThi_id: bigint [FK -> PhongThi.id]
    giamThi_id: bigint [FK -> GiamThiKyThi.id]
    sinhVien_id: bigint [FK -> SinhVienMonThi.id]
    maKyThi: varchar
    maCaThi: varchar
    maMonThi: varchar
    maDeThi: varchar
    maPhongThi: varchar
    maGiamThi: varchar
    maSinhVien: varchar
    soCauDung: int
    soCauDungSauPhucKhao: int
    tongSoCau: int
    diem: decimal
    diemSauPhucKhao: decimal
    ghiChu: text
    ghiChuPhucKhao: text
    thoiGianPhucKhao: timestamp
    nguoiPhucKhao: bigint [FK -> TaiKhoan.id]
    log_file: longtext
    created_at: timestamp
    updated_at: timestamp
}

entity CaThi {
    * id: bigint [PK]
    --
    kyThi_id: bigint [FK -> KyThi.id]
    ten: varchar
    moTa: text
    thoiGianBatDau: datetime
    thoiGianKetThuc: datetime
    is_active: tinyint
    created_at: timestamp
    updated_at: timestamp
}

entity PhongThiCaThi {
    * id: bigint [PK]
    --
    caThi_id: bigint [FK -> CaThi.id]
    phongThi_id: bigint [FK -> PhongThi.id]
    monThi_id: bigint [FK -> MonThiKyThi.id]
    giamThi_id: bigint [FK -> GiamThiKyThi.id]
    created_at: timestamp
    updated_at: timestamp
}

entity CoSoVatChat {
    * id: bigint [PK]
    --
    ma: varchar
    ten: varchar
    diaChi: varchar
    moTa: text
    is_active: tinyint
    created_at: timestamp
    updated_at: timestamp
}

entity Khoa {
    * id: bigint [PK]
    --
    ma: varchar
    ten: varchar
    moTa: text
    created_at: timestamp
    updated_at: timestamp
}

entity Nganh {
    * id: bigint [PK]
    --
    ma: varchar
    ten: varchar
    khoa_id: bigint [FK -> Khoa.id]
    moTa: text
    created_at: timestamp
    updated_at: timestamp
}

entity MonHoc {
    * id: bigint [PK]
    --
    ma: varchar
    ten: varchar
    soTinChi: int
    nganh_id: bigint [FK -> Nganh.id]
    moTa: text
    created_at: timestamp
    updated_at: timestamp
}

entity The {
    * id: bigint [PK]
    --
    ten: varchar
    maMonHoc: varchar [FK -> MonHoc.ma]
    created_at: timestamp
    updated_at: timestamp
}

entity TheCauHoi {
    * cauHoi_id: bigint [PK, FK -> CauHoi.id]
    * the_id: bigint [PK, FK -> The.id]
    --
    created_at: timestamp
    updated_at: timestamp
}

entity PhongThi {
    * id: bigint [PK]
    --
    ma: varchar
    ten: varchar
    coSo_id: bigint [FK -> CoSoVatChat.id]
    sucChua: int
    moTa: text
    is_active: tinyint
    created_at: timestamp
    updated_at: timestamp
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