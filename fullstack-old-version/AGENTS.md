# AGENTS.md

Tai lieu nay quy dinh cach Codex lam viec trong repo `checkin-v3-cloud`.
Muc tieu la ship thay doi dung yeu cau, it rui ro, de review, va de bao tri.

## 1) Scope va uu tien

- Uu tien giai phap don gian, thay doi nho, dung trong boi canh code hien tai.
- Chi sua cac file lien quan truc tiep den yeu cau.
- Khong refactor rong neu khong duoc yeu cau ro rang.
- Khong tac dong den thay doi dang dang do cua nguoi khac.
- End users là người dùng trong lĩnh vực sự kiện, họ hiểu về cách vận hành sự kiện nhưng sẽ không am hiểu về chuyên môn phần mềm hay giải pháp, ưu tiên tối ưu giao diện gần gũi, thân thiện người dùng, hướng tới việc dễ dùng và hiệu quả cho đại đa số end users, đảm bảo truyền tải thông tin chính xác và trực quan

## 2) Stack chinh cua repo

- Backend: Laravel 12, PHP 8.2
- Frontend: Vite, Bootstrap, Turbo, jQuery
- Test backend: PHPUnit qua `php artisan test`
- Lint/format:
  - PHP style: `./vendor/bin/pint --test`
  - JS lint: `npm run lint`
  - Frontend build: `npm run build`

## 3) Nguyen tac ky thuat bat buoc

- Ton trong conventions cua Laravel va structure hien co.
- Validate input ro rang (Form Request hoac validator hop ly).
- Cac thao tac ghi DB nhieu buoc can can nhac `DB::transaction()`.
- Tranh N+1 query cho list/page quan trong (`with`, `load` khi can).
- Khong hard-code secrets hoac config nhạy cam vao code.
- Neu them env moi, cap nhat `.env.example`.
- Khong pha vo contract API hien co neu chua co yeu cau migration contract.
- Tieng Anh cho code/ten bien/ten ham; giao tiep voi user bang tieng Viet ngan gon.

## 4) Quy trinh lam viec cho moi task

1. Doc nhanh context lien quan (`README`, route, controller, service, view, test).
2. Xac dinh pham vi thay doi toi thieu de dap ung yeu cau.
3. Implement theo huong it rui ro, giu backward compatibility toi da.
4. Chay verify phu hop voi pham vi thay doi:
   - Backend logic: `php artisan test` (uu tien test lien quan truoc, full test khi can)
   - Sua PHP: `./vendor/bin/pint --test`
   - Sua JS/CSS: `npm run lint`
   - Thay doi asset pipeline/UI lon: `npm run build`
5. Bao cao ket qua ro rang: file da sua, ly do, lenh da chay, ket qua pass/fail, rui ro con lai.

## 5) Test expectations

- Bug fix nen co it nhat 1 test tai hien va chan hoi quy neu kha thi.
- Feature moi nen co:
  - Happy path test
  - It nhat 1 test cho validation/error path
- Neu khong the viet/chay test, phai noi ro ly do va rui ro.

## 6) Quy tac an toan va git hygiene

- Khong dung lenh destruct ve git/filesystem neu chua duoc yeu cau ro rang:
  - `git reset --hard`
  - `git checkout -- <file>`
  - `rm -rf`
- Khong tu y amend commit cua nguoi dung.
- Khong revert thay doi khong lien quan.
- Neu phat hien thay doi bat thuong trong qua trinh lam viec, dung lai va hoi user.

## 7) Checklist truoc khi ket thuc task

- Yeu cau goc da duoc dap ung day du.
- Code da duoc format/lint/test muc can thiet theo pham vi.
- Khong co debug code, commented dead code, hoac log nhay cam.
- Bao cao cuoi cung co:
  - Tom tat giai phap
  - Danh sach file da sua
  - Lenh verify da chay + ket qua
  - Ghi chu follow-up (neu co)
