document.addEventListener('DOMContentLoaded', () => {
  // Keep area/store dropdowns in sync on both the claim form and dashboard filters.
  document.querySelectorAll('[data-area-select]').forEach((areaSelect) => {
    const scope = areaSelect.closest('form');
    const storeSelect = scope ? scope.querySelector('[data-store-select]') : null;
    if (!storeSelect) return;

    const requiresArea = storeSelect.hasAttribute('data-area-filter-required');
    const storeOptions = Array.from(storeSelect.options).filter((option) => option.value !== '');

    const updateStoreOptions = (clearInvalidSelection = false) => {
      const selectedArea = areaSelect.value;
      const previouslySelectedStore = storeSelect.value;
      let previousSelectionIsVisible = false;

      storeOptions.forEach((option) => {
        const belongsToSelectedArea = selectedArea !== ''
          ? option.dataset.areaId === selectedArea
          : !requiresArea;

        option.hidden = !belongsToSelectedArea;
        option.disabled = !belongsToSelectedArea;

        if (option.value === previouslySelectedStore && belongsToSelectedArea) {
          previousSelectionIsVisible = true;
        }
      });

      if (clearInvalidSelection || !previousSelectionIsVisible) {
        storeSelect.value = '';
      }

      storeSelect.disabled = requiresArea && selectedArea === '';

      const placeholder = storeSelect.querySelector('option[value=""]');
      if (placeholder) {
        placeholder.textContent = requiresArea && selectedArea === ''
          ? 'Choose an area first'
          : (selectedArea ? 'Choose a store' : 'All stores');
      }
    };

    areaSelect.addEventListener('change', () => updateStoreOptions(true));
    updateStoreOptions(false);
  });

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
    textarea.addEventListener('input', update);
    update();
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
