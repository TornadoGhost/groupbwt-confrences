import {exportFile} from "../exportFile.js";

export function exportPdf() {
  const exportBtn = document.getElementById('export-pdf');
  const conferenceId = exportBtn.closest('tr').dataset.conferenceId;

  exportBtn.addEventListener('mousedown', function() {
    exportFile(`/api/v1/conferences/${conferenceId}/export-pdf`, 'POST');
  });
}
