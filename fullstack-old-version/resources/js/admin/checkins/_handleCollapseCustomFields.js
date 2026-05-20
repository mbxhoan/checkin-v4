;

export const handleCollapseCustomFields = () => {
  // Use delegated handler because this list can be re-rendered.
  $(document)
    .off('click.delfiCheckinCollapse', '.checkin-collapse-toggle')
    .on('click.delfiCheckinCollapse', '.checkin-collapse-toggle', function (e) {
      // Clicking the dedicated button always toggles.
      const $btn = $(e.target).closest('.checkin-collapse-btn');
      if ($btn.length) {
        e.preventDefault();
        e.stopPropagation();
        toggle($btn.data('collapseTarget'), $btn);
        return;
      }

      // If user clicks on a form control, don't toggle (avoid annoying accidental closes).
      const $target = $(e.target);
      if ($target.is('input, textarea, select, button, a, label') || $target.closest('input, textarea, select, button, a, label').length) {
        return;
      }

      const selector = $(this).find('.checkin-collapse-btn').data('collapseTarget') || $(this).data('collapseTarget');
      if (!selector) return;

      const $headerBtn = $(this).find('.checkin-collapse-btn').first();
      toggle(selector, $headerBtn.length ? $headerBtn : null);
    });

  // Keep the chevron direction in sync.
  $(document)
    .off('shown.bs.collapse.delfiCheckinCollapse hidden.bs.collapse.delfiCheckinCollapse', '.collapse')
    .on('shown.bs.collapse.delfiCheckinCollapse hidden.bs.collapse.delfiCheckinCollapse', '.collapse', function (evt) {
      const id = this.id;
      if (!id) return;

      const isOpen = evt.type === 'shown';
      const $btn = $(`.checkin-collapse-btn[data-collapse-target="#${id}"]`).first();
      if ($btn.length === 0) return;

      $btn.attr('aria-expanded', isOpen ? 'true' : 'false');
      const $icon = $btn.find('i.fa-solid').first();
      if ($icon.length) {
        $icon.toggleClass('fa-chevron-down', !isOpen);
        $icon.toggleClass('fa-chevron-up', isOpen);
      }
    });
};

const toggle = (selector, $btn = null) => {
  if (!selector) return;

  const el = document.querySelector(selector);
  if (!el) return;

  const Collapse = window?.bootstrap?.Collapse;
  if (!Collapse) return;

  const instance = Collapse.getOrCreateInstance(el, { toggle: false });
  instance.toggle();

  // Optimistically flip aria-expanded for immediate UI feedback.
  if ($btn && $btn.length) {
    const cur = $btn.attr('aria-expanded') === 'true';
    $btn.attr('aria-expanded', cur ? 'false' : 'true');
  }
};
