document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-voucher-textarea]').forEach((textarea) => {
    const card = textarea.closest('.voucher-input-card');
    const countLabel = card.querySelector('[data-count-for]');
    const countNumber = card.querySelector('[data-count-number]');
    const update = () => {
      const codes = textarea.value.split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean);
      const unique = [...new Set(codes)];
      countLabel.textContent = `${unique.length} ${unique.length === 1 ? 'code' : 'codes'}`;
      countNumber.textContent = unique.length;
    };
    textarea.addEventListener('input', update); update();
  });
  const fileInput = document.querySelector('[data-file-input]');
  if (fileInput) fileInput.addEventListener('change', () => {
    const label = document.querySelector('[data-file-name]');
    if (fileInput.files && fileInput.files[0]) label.textContent = fileInput.files[0].name;
  });
  document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  }));
});
