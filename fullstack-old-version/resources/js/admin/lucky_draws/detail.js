;

$(document).ready(function() {
    // Giữ tab đang xem khi reload: đọc hash và kích hoạt tab tương ứng
    (function() {
        var hash = window.location.hash.slice(1);
        if (hash) {
            var tabBtn = document.querySelector('#settingsTabs button[data-bs-target="#' + hash + '"]');
            if (tabBtn && typeof bootstrap !== 'undefined') {
                var tab = bootstrap.Tab.getOrCreateInstance(tabBtn);
                tab.show();
            }
        }
        $(document).on('shown.bs.tab', '#settingsTabs button[data-bs-toggle="tab"]', function(e) {
            var target = $(e.target).attr('data-bs-target');
            if (target) {
                window.location.hash = target.replace('#', '');
            }
        });
    })();

    // Nút "Làm mới" - bỏ gán người trúng giải, giữ nguyên danh sách giải
    $('#resetRewardClient').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Bạn có chắc muốn làm mới thông tin nhận giải? Tất cả người trúng sẽ được bỏ gán giải, nhưng danh sách giải thưởng vẫn giữ nguyên.')) {
            return;
        }

        const url = $(this).data('url');
        const form = $('<form>', {
            'method': 'POST',
            'action': url
        });

        const csrfToken = $('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        });

        form.append(csrfToken);
        $('body').append(form);
        form.submit();
    });

    // Nút "Reset" - xóa tất cả giải thưởng và bỏ gán người trúng
    $('#resetButton').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('BẠN CÓ CHẮC MUỐN XÓA TẤT CẢ GIẢI THƯỞNG? Hành động này sẽ xóa toàn bộ danh sách giải và bỏ gán tất cả người trúng giải.')) {
            return;
        }

        const url = $(this).data('url');
        const form = $('<form>', {
            'method': 'POST',
            'action': url
        });

        const csrfToken = $('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        });

        form.append(csrfToken);
        $('body').append(form);
        form.submit();
    });

    // Xử lý thay đổi dropdown "Gán cơ cấu" (assignee_id)
    $('.input-assignee_id').on('change', function() {
        const $select = $(this);
        const assigneeId = $select.val();
        const $container = $select.closest('.row');
        const $hiddenInput = $container.find('#data-assignee_id');
        const url = $hiddenInput.data('url');
        const rewardId = $hiddenInput.data('reward_id');

        if (!url) {
            console.error('URL not found');
            return;
        }

        // Gửi request POST để update assignee_id
        const form = $('<form>', {
            'method': 'POST',
            'action': url
        });

        const csrfToken = $('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        });

        const assigneeInput = $('<input>', {
            'type': 'hidden',
            'name': 'reward[assignee_id]',
            'value': assigneeId
        });

        form.append(csrfToken);
        form.append(assigneeInput);
        $('body').append(form);
        form.submit();
    });

    // Link hình ảnh: select ảnh đã upload hoặc nhập link khác
    function syncRewardImgLinkHidden($select) {
        var modalId = $select.data('modal-id');
        var otherId = $select.data('other-input-id');
        var hiddenId = $select.data('hidden-id');
        var val = $select.val();
        var $other = $('#' + otherId);
        var $hidden = $('#' + hiddenId);
        if (val === '__other__') {
            $other.removeClass('d-none');
            if ($hidden.length) $hidden.val($other.val() || '');
        } else {
            $other.addClass('d-none');
            if ($hidden.length) $hidden.val(val || '');
        }
    }

    $(document).on('change', '.reward-img-link-select', function() {
        var $select = $(this);
        var modalId = $select.data('modal-id');
        var otherId = $select.data('other-input-id');
        var $other = $('#' + otherId);
        if ($select.val() === '__other__') {
            $other.removeClass('d-none').focus();
        } else {
            $other.addClass('d-none');
        }
        syncRewardImgLinkHidden($select);
    });

    $(document).on('input', '.reward-img-link-other', function() {
        var $other = $(this);
        var modalId = $other.attr('id').replace('reward-img_link-other-', '');
        var $hidden = $('#reward-img_link-hidden-' + modalId);
        if ($hidden.length) $hidden.val($other.val() || '');
    });

    $(document).on('shown.bs.modal', '[id^=createRewardModal], [id^=updateRewardModal]', function() {
        var $modal = $(this);
        $modal.find('.reward-img-link-select').each(function() {
            syncRewardImgLinkHidden($(this));
        });
    });

    // Thêm option ảnh mới vào tất cả select sau khi upload (gọi từ detail.blade sau upload)
    window.appendUploadedRewardImageOption = function(item) {
        var url = item.url || item;
        var name = (item.name != null ? item.name : url);
        $('.reward-img-link-select').each(function() {
            var $select = $(this);
            var hasUrl = $select.find('option').filter(function() { return $(this).val() === url; }).length > 0;
            if (!hasUrl) {
                var $otherOpt = $select.find('option[value="__other__"]');
                if ($otherOpt.length) {
                    $otherOpt.before($('<option>').attr('value', url).text(name));
                }
            }
        });
    };
});
