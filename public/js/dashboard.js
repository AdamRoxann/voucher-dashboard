document.addEventListener('DOMContentLoaded', () => {
  const totalInput = document.querySelector('[data-total-claimed]');
  const voucherTextareas = document.querySelectorAll('[data-voucher-textarea]');
  const duplicateWarning = document.querySelector('[data-voucher-duplicate-warning]');

  const voucherValues = {
    A: 50000,
    B: 50000,
    C: 50000,
    D: 50000,
    E: 100000,
  };

  function splitCodes(text) {
    return text
      .split(/[\r\n,;]+/)
      .map(code => code.trim().toUpperCase())
      .filter(Boolean);
  }

  function updateVoucherTotals() {
    if (!totalInput || voucherTextareas.length === 0) return;

    let total = 0;
    const seenCodes = new Set();
    const duplicateCodes = new Set();

    voucherTextareas.forEach(textarea => {
      const match = textarea.name.match(/^voucher_codes\[([A-E])\]$/);
      if (!match) return;

      const type = match[1];
      const codes = splitCodes(textarea.value);
      const uniqueInType = new Set(codes);
      const card = textarea.closest('.voucher-input-card');
      const countLabel = card?.querySelector('[data-count-for]');
      const countNumber = card?.querySelector('[data-count-number]');

      if (countLabel) {
        countLabel.textContent = `${uniqueInType.size} ${uniqueInType.size === 1 ? 'code' : 'codes'}`;
      }
      if (countNumber) {
        countNumber.textContent = uniqueInType.size;
      }

      uniqueInType.forEach(code => {
        if (seenCodes.has(code)) {
          duplicateCodes.add(code);
        } else {
          seenCodes.add(code);
          total += voucherValues[type];
        }
      });

      // Repeated code in the same voucher textarea is also invalid.
      if (codes.length !== uniqueInType.size) {
        const counts = {};
        codes.forEach(code => counts[code] = (counts[code] || 0) + 1);
        Object.entries(counts).forEach(([code, count]) => {
          if (count > 1) duplicateCodes.add(code);
        });
      }
    });

    totalInput.value = total;

    if (duplicateWarning) {
      if (duplicateCodes.size > 0) {
        duplicateWarning.hidden = false;
        duplicateWarning.textContent = `Duplicate code(s): ${Array.from(duplicateCodes).join(', ')}. Each voucher code can only be used once.`;
      } else {
        duplicateWarning.hidden = true;
        duplicateWarning.textContent = '';
      }
    }
  }

  voucherTextareas.forEach(textarea => {
    textarea.addEventListener('input', updateVoucherTotals);
  });

  updateVoucherTotals();

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

  const fileInput = document.querySelector('[data-file-input]');
  if (fileInput) fileInput.addEventListener('change', () => {
    const label = document.querySelector('[data-file-name]');
    if (fileInput.files && fileInput.files[0]) label.textContent = fileInput.files[0].name;
  });

  document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  }));
});
