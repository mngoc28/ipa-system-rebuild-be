# Starter Backend Template

Ma nguon da duoc don de bat dau du an moi tren nen Laravel, giu nguyen cau truc thu muc hien co.

## Muc tieu sau khi don source

- Giu nguyen bo khung thu muc hien tai.
- Loai bo route nghiep vu cu khoi diem vao chinh.
- Lam sach file moi truong mau, khong de thong tin nhay cam.
- Seed mac dinh de trong de ban tu xay dung du lieu moi.

## Yeu cau he thong

- PHP >= 8.0.2
- Composer
- Node.js + npm
- MySQL (hoac DB khac theo cau hinh)

## Cai dat nhanh

1. Cai dependencies PHP:
   composer install

2. Cai dependencies frontend:
   npm install

3. Tao file moi truong:
   copy .env.example .env

4. Tao app key:
   php artisan key:generate

5. Chay migration (sau khi ban cap nhat migrations moi):
   php artisan migrate

6. Chay ung dung:
   php artisan serve

## Endpoint mac dinh sau khi don

- GET / -> starter status
- GET /api/v1/health -> API health check
- GET /api/v1/me -> endpoint mau yeu cau auth:sanctum

## Ghi chu

- Cac module nghiep vu cu van con trong source de tham khao cau truc, nhung khong con duoc route den trong API starter.
- Ban co the xoa tiep cac file nghiep vu cu theo lo trinh du an moi neu can.
