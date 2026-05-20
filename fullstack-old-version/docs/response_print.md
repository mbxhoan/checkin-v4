Đã triển khai Phase 1 direct print cho luồng in tem (có fallback an toàn).

Kết quả chính

Thêm cơ chế in mới dùng Direct Print (QZ Tray) trước, nếu không khả dụng thì tự fallback sang browser print.
Thay fallback cũ window.open(...).print() bằng hidden iframe print để không bật popup tab/window mới.
Áp dụng cho các luồng:
In tem đơn
In tem hàng loạt
In tem ở client modal
In tem ở scan online/offline
File đã triển khai

Config/env:
config/services.php (line 40)
.env.example (line 77)
Inject config + load bridge script:
direct-print-config.blade.php (line 1)
admin layout (line 68)
scan layout (line 131)
Core print engine:
resources/js/common/print.js (line 1)
Refactor các module in:
labels/_print.js (line 1)
labels/_multiPrint.js (line 1)
clients/_print.js (line 1)
clients/_handleClickPrintByClass.js (line 1)
scan/_print.js (line 1)
scan/_printOffline.js (line 1)
Hướng dẫn vận hành pilot:
docs/direct-print.md (line 1)
Cách bật pilot ngay

Set env:
DIRECT_PRINT_ENABLED=true
DIRECT_PRINT_PROVIDER=qz
DIRECT_PRINT_FALLBACK_TO_BROWSER=true
DIRECT_PRINT_QZ_PRINTER=<ten-may-in> (hoặc để trống để lấy default)
Cài và chạy QZ Tray trên máy in.
Reload app, bấm in như bình thường.
Verify đã chạy

php -l config/services.php -> pass
php artisan view:cache -> pass
eslint cho các file JS mới/sửa liên quan in -> pass
npm run lint toàn repo -> fail do rất nhiều lỗi lint tồn tại sẵn ngoài phạm vi task (pre-existing)
Nếu bạn muốn, bước tiếp theo mình triển khai luôn Phase 2: queue in + retry + log trạng thái từng job in (thành công/thất bại, lỗi bridge, máy in offline).
