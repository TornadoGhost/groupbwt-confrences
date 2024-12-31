export function resetConferencesFilters(buttonId, formId) {
  const form = document.getElementById(formId);
  document.getElementById(buttonId).addEventListener('click', function () {
    form.reset();
  });
}
