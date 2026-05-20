# Rules — Core Operating Rules

## 1. Golden Rules

1. **Không đoán khi có thể đọc source.** Trước khi sửa, luôn inspect file liên quan bằng search/read.
2. **Không phá backward compatibility nếu chưa được yêu cầu.** Check-in, QR, email campaign, landing page, webhook, payment là các luồng production-sensitive.
3. **Không sửa DB/API contract âm thầm.** Mọi thay đổi schema, response shape, enum, permission, route, webhook payload phải được ghi rõ trong docs và phần Done.
4. **Không hard-code tenant/event/user.** Check-in V4 là SaaS đa tenant. Mọi query phải có tenant/event boundary rõ ràng.
5. **Không commit secret.** Không ghi token, OnePay hash secret, Postmark token, DB password, app key vào code/docs/log.
6. **Mọi thay đổi phải có verify.** Ít nhất chạy test/build/lint phù hợp hoặc giải thích rõ vì sao chưa chạy được.
7. **Mỗi request trả đúng 1 commit message duy nhất.** Không đưa nhiều lựa chọn commit message ở phần Done.
8. **Luôn cập nhật `docs/commit_prompt_map.md`.** Mỗi lần hoàn tất task phải thêm entry truy vết.
9. **Ưu tiên small diff.** Sửa đúng phạm vi yêu cầu, tránh refactor lan rộng nếu không cần.
10. **Fail closed cho security/payment/check-in.** Khi không chắc quyền, trạng thái thanh toán, trạng thái QR hoặc payload webhook, hệ thống phải từ chối an toàn thay vì chấp nhận sai.

## 2. Response Behavior

Khi được yêu cầu implement:

- Làm trực tiếp từ source hiện có.
- Không hỏi lại nếu có thể đưa ra giả định an toàn.
- Nếu thiếu quyết định nghiệp vụ quan trọng, ghi rõ blocker/risk.
- Final response ngắn, đúng `## Done` format.

Khi được yêu cầu plan/review:

- Nêu scope, DB/API/UI impact, test cần chạy, rủi ro và commit breakdown.
- Ưu tiên phát hiện lỗi logic, security, data integrity, tenant boundary, test gap.

## 3. Anti-Patterns

Không làm các việc sau:

- Đưa business logic vào UI component hoặc controller quá dày.
- Query data event-bound mà không có `event_id`/tenant scope.
- Tin frontend permission check thay cho backend authorization.
- Gửi bulk email synchronously trong request.
- Import/export file lớn synchronously.
- Mark payment `PAID` từ return URL mà không verify callback/hash server-side.
- Dùng open tracking như bằng chứng người dùng thật sự mở email.
- Lưu file upload bằng path public dễ đoán.
- Trả stack trace ra frontend.
- Che giấu test/build fail.
- Bỏ qua `docs/commit_prompt_map.md`.
