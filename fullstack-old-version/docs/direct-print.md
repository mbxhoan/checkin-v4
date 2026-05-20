# Direct Print (Phase 1)

Tai lieu nay mo ta cach bat che do in truc tiep cho tinh nang in tem.

## 1) Muc tieu

- Giam phu thuoc vao popup print cua browser.
- Cho phep in truc tiep neu co local print bridge (QZ Tray).
- Neu bridge chua san sang, tu dong fallback sang browser print de khong gian doan van hanh.

## 2) Bien moi trong `.env`

```env
DIRECT_PRINT_ENABLED=false
DIRECT_PRINT_PROVIDER=browser
DIRECT_PRINT_FALLBACK_TO_BROWSER=true
DIRECT_PRINT_QZ_SCRIPT_URL=https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js
DIRECT_PRINT_QZ_PRINTER=
```

Y nghia:

- `DIRECT_PRINT_ENABLED`: bat/tat direct print.
- `DIRECT_PRINT_PROVIDER`: hien ho tro `browser` va `qz`.
- `DIRECT_PRINT_FALLBACK_TO_BROWSER`: neu in truc tiep that bai, co fallback sang browser print hay khong.
- `DIRECT_PRINT_QZ_SCRIPT_URL`: duong dan script QZ Tray.
- `DIRECT_PRINT_QZ_PRINTER`: ten may in uu tien (neu de trong thi lay default printer tu QZ).

## 3) Cach bat nhanh de pilot

```env
DIRECT_PRINT_ENABLED=true
DIRECT_PRINT_PROVIDER=qz
DIRECT_PRINT_FALLBACK_TO_BROWSER=true
DIRECT_PRINT_QZ_PRINTER=TenMayInCuaBan
```

Sau do:

1. Cai QZ Tray tren may in.
2. Mo QZ Tray (dang chay background).
3. Dang nhap he thong va bam in tem nhu binh thuong.

## 4) Hanh vi hien tai

- Neu QZ san sang: in qua direct print mode.
- Neu QZ loi/chua cai: hien canh bao, roi fallback qua browser print.
- Browser print fallback da doi tu popup window sang hidden iframe de giam mo tab/popup moi.

## 5) Ghi chu van hanh

- Browser thuong khong cho bo print dialog neu khong co local bridge.
- Direct print tren web bat buoc can mot thanh phan local (vd: QZ Tray) de noi voi may in.
