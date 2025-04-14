@startuml
' Entities
class TaiKhoan {
    +id: bigint
    +username: varchar
    +email: varchar
    +password: varchar
    +is_active: tinyint
    +remember_token: varchar
    +role: tinyint
    +created_at: timestamp
    +updated_at: timestamp
    +dangNhap(): boolean
    +dangXuat(): void
    +doiMatKhau(newPassword: varchar): void
}

class ThongTinTaiKhoan {
    +id: bigint
    +tai_khoan_id: bigint
    +hoTen: varchar
    +ngaySinh: date
    +avatar: varchar
    +gioiTinh: tinyint
    +soDienThoai: varchar
    +diaChi: varchar
    +created_at: timestamp
    +updated_at: timestamp
    +capNhatThongTin(newHoTen: varchar, newSoDienThoai: varchar): void
}

class CauTraLoi {
    +id: bigint
    +cauHoi_id: bigint
    +noiDung: text
    +link_media: varchar
    +is_correct: tinyint
    +created_at: timestamp
    +updated_at: timestamp
    +kiemTraDapAn(): boolean
}

class DeThi {
    +id: bigint
    +ten: varchar
    +moTa: text
    +thoiGianLamBai: int
    +tongSoCauHoi: int
    +maMonHoc: varchar
    +tiLeDe: decimal
    +tiLeTrungBinh: decimal
    +tiLeKho: decimal
    +created_at: timestamp
    +updated_at: timestamp
    +tinhDoKho(): void
    +themCauHoi(cauHoi: CauHoi): void
}

class KyThi {
    +id: bigint
    +ten: varchar
    +moTa: text
    +thoiGianBatDau: datetime
    +thoiGianKetThuc: datetime
    +is_active: tinyint
    +created_at: timestamp
    +updated_at: timestamp
    +kichHoatKyThi(): void
    +huyKichHoatKyThi(): void
}

class GiamThiKyThi {
    +id: bigint
    +kyThi_id: bigint
    +taiKhoan_id: bigint
    +created_at: timestamp
    +updated_at: timestamp
    +phanCongGiamThi(taiKhoan: TaiKhoan): void
}

class PhongThiKyThi {
    +id: bigint
    +kyThi_id: bigint
    +phongThi_id: bigint
    +created_at: timestamp
    +updated_at: timestamp
    +phanCongPhongThi(phongThi: PhongThi): void
}

class SinhVienPhongThi {
    +id: bigint
    +kyThi_id: bigint
    +monThi_id: bigint
    +sinhVien_id: bigint
    +phongThi_id: bigint
    +caThi_id: bigint
    +soGhe: int
    +created_at: timestamp
    +updated_at: timestamp
    +phanCongGhe(sinhVien: SinhVienMonThi): void
}

class MonThiKyThi {
    +id: bigint
    +kyThi_id: bigint
    +monHoc_id: bigint
    +deThi_id: bigint
    +created_at: timestamp
    +updated_at: timestamp
    +themMonThi(monHoc: MonHoc): void
}

class CaThiMonThi {
    +id: bigint
    +monThi_id: bigint
    +caThi_id: bigint
    +created_at: timestamp
    +updated_at: timestamp
    +phanCongCaThi(caThi: CaThi): void
}

class SinhVienMonThi {
    +id: bigint
    +kyThi_id: bigint
    +monThi_id: bigint
    +maThiSinh: varchar
    +maSinhVien: varchar
    +hoTen: varchar
    +soDienThoai: varchar
    +diaChi: text
    +ngaySinh: date
    +gioiTinh: tinyint
    +created_at: timestamp
    +updated_at: timestamp
    +dangKyThi(): void
}

class KetQuaThi {
    +id: bigint
    +kyThi_id: bigint
    +caThi_id: bigint
    +monThi_id: bigint
    +deThi_id: bigint
    +phongThi_id: bigint
    +giamThi_id: bigint
    +sinhVien_id: bigint
    +maKyThi: varchar
    +maCaThi: varchar
    +maMonThi: varchar
    +maDeThi: varchar
    +maPhongThi: varchar
    +maGiamThi: varchar
    +maSinhVien: varchar
    +soCauDung: int
    +soCauDungSauPhucKhao: int
    +tongSoCau: int
    +diem: decimal
    +diemSauPhucKhao: decimal
    +ghiChu: text
    +ghiChuPhucKhao: text
    +thoiGianPhucKhao: timestamp
    +nguoiPhucKhao: bigint
    +log_file: longtext
    +created_at: timestamp
    +updated_at: timestamp
    +tinhDiem(): decimal
    +yeuCauPhucKhao(): void
}

class CaThi {
    +id: bigint
    +kyThi_id: bigint
    +ten: varchar
    +moTa: text
    +thoiGianBatDau: datetime
    +thoiGianKetThuc: datetime
    +is_active: tinyint
    +created_at: timestamp
    +updated_at: timestamp
    +kichHoatCaThi(): void
    +huyKichHoatCaThi(): void
}

class PhongThiCaThi {
    +id: bigint
    +caThi_id: bigint
    +phongThi_id: bigint
    +monThi_id: bigint
    +giamThi_id: bigint
    +created_at: timestamp
    +updated_at: timestamp
    +phanCongPhongThi(phongThi: PhongThiKyThi): void
}

class TheDeThi {
    +id: bigint
    +deThi_id: bigint
    +the_id: bigint
    +soLuongCauHoi: int
    +tiLeDe: decimal
    +tiLeTrungBinh: decimal
    +tiLeKho: decimal
    +created_at: timestamp
    +updated_at: timestamp
    +themThe(the: The): void
}

class CoSoVatChat {
    +id: bigint
    +ma: varchar
    +ten: varchar
    +diaChi: varchar
    +moTa: text
    +is_active: tinyint
    +created_at: timestamp
    +updated_at: timestamp
    +kichHoatCoSo(): void
    +huyKichHoatCoSo(): void
}

class Khoa {
    +id: bigint
    +ma: varchar
    +ten: varchar
    +moTa: text
    +created_at: timestamp
    +updated_at: timestamp
    +themNganh(nganh: Nganh): void
}

class Nganh {
    +id: bigint
    +ma: varchar
    +ten: varchar
    +khoa_id: bigint
    +moTa: text
    +created_at: timestamp
    +updated_at: timestamp
    +themMonHoc(monHoc: MonHoc): void
}

class CauHoi {
    +id: bigint
    +noiDung: text
    +link_media: varchar
    +maMonHoc: varchar
    +doKho: enum
    +created_at: timestamp
    +updated_at: timestamp
    +themCauTraLoi(cauTraLoi: CauTraLoi): void
}

class TheCauHoi {
    +cauHoi_id: bigint
    +the_id: bigint
    +themThe(the: The): void
}

class PhongThi {
    +id: bigint
    +ma: varchar
    +ten: varchar
    +coSo_id: bigint
    +sucChua: int
    +moTa: text
    +is_active: tinyint
    +created_at: timestamp
    +updated_at: timestamp
    +kichHoatPhongThi(): void
    +huyKichHoatPhongThi(): void
}

class MonHoc {
    +id: bigint
    +ma: varchar
    +ten: varchar
    +soTinChi: int
    +nganh_id: bigint
    +moTa: text
    +created_at: timestamp
    +updated_at: timestamp
    +themCauHoi(cauHoi: CauHoi): void
}

class The {
    +id: bigint
    +ten: varchar
    +maMonHoc: varchar
    +created_at: timestamp
    +updated_at: timestamp
    +themCauHoi(cauHoi: CauHoi): void
}

' Relationships
TaiKhoan "1" -- "1" ThongTinTaiKhoan : co
CauHoi "1" -- "*" CauTraLoi : co
DeThi "1" -- "*" TheDeThi : co
KyThi "1" -- "*" GiamThiKyThi : co
KyThi "1" -- "*" PhongThiKyThi : co
KyThi "1" -- "*" MonThiKyThi : co
MonThiKyThi "1" -- "*" CaThiMonThi : co
MonThiKyThi "1" -- "*" SinhVienMonThi : co
PhongThiKyThi "1" -- "*" SinhVienPhongThi : co
CaThi "1" -- "*" PhongThiCaThi : co
MonThiKyThi "1" -- "*" KetQuaThi : co
SinhVienMonThi "1" -- "*" KetQuaThi : co
CaThi "1" -- "*" KetQuaThi : co
PhongThiKyThi "1" -- "*" KetQuaThi : co
GiamThiKyThi "1" -- "*" KetQuaThi : co
Khoa "1" -- "*" Nganh : co
Nganh "1" -- "*" MonHoc : co
MonHoc "1" -- "*" CauHoi : co
MonHoc "1" -- "*" The : co
CauHoi "1" -- "*" TheCauHoi : co
The "1" -- "*" TheCauHoi : co
CoSoVatChat "1" -- "*" PhongThi : co
PhongThiKyThi "1" -- "1" PhongThi : suDung
GiamThiKyThi "1" -- "1" TaiKhoan : suDung
KetQuaThi "1" -- "1" TaiKhoan : duocPhucKhaoBoi

@enduml