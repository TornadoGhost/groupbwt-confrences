import {exportFile} from "../exportFile.js";

export function exportExcel(isGranted) {
  if (!isGranted) {
    return false;
  }

  const exportExcelBtn = document.getElementById('export-excel');
  const conferenceId = exportExcelBtn.closest('tr').dataset.conferenceId;

  exportExcelBtn.addEventListener('mousedown', () => {
    exportFile(`/api/v1/conferences/${conferenceId}/export-excel`, 'POST');
  });
}
