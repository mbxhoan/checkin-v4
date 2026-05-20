export function initCurrentTab() {
  document
    .querySelectorAll('[data-bs-toggle="tab"]')
    .forEach(tab => {
      tab.addEventListener('shown.bs.tab', function (e) {
        const target = e.target.getAttribute('data-bs-target');
        if (!target) return;

        const tabName = target.replace('#', '');

        // update hidden input
        const input = document.getElementById('current_tab');
        if (input) {
          input.value = tabName;
        }

        // update URL (no reload)
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState(null, '', url);
      });
    });
}
