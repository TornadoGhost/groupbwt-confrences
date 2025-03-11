// TODO: add passing token value via parameter
export function exportFile(url, method) {
  fetch(url, {
    method: method,
    headers: {
      Authorization: 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDA5ODg0MzgsImV4cCI6MTc0MTU5MzIzOCwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQGV4YW1wbGUuY29tIn0.OCEjkc8GwJtJ3r32dlmaq_Qm7Rvky21E-O6yAsBm2zCO9hBZyOZgLVp3XJBGgpQmVTW-y4_ZR2MxVCPgw858l0lY7UM0cwYCXRJP1GqHwSK7YjJNvrsCc4F8nS5UkMDjFh2gmR4x-ug6XnghU5PyAICmeWY832FwBto9KPuR1C_BgQYLrZvPIDKNXYhSlXkt2ZZjbYbtEyXJwf0u-OWVuhUMsViJvpCt3Nu2eDkEKJcCrcyXvUOvwBzVFRz6bt0tUyvdv8Yd7apP6up7Fq-r01-RffSAHSoz1OntyBWGg2tu1i72CHUjicdPCB7mIREmIlK8z7XC0pqWOMN7PjL0Yw'
    }
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('File loading error');
      }

      const contentDisposition = response.headers.get('Content-Disposition');
      console.log(contentDisposition);
      let filename = 'exported_file.xlsx';

      if (contentDisposition) {
        const match = contentDisposition.match(/filename\*?=(?:UTF-8'')?["]?([^";]+)["]?/);
        if (match) {
          filename = match[1];
        }
      }

      return response.blob().then(blob => ({ blob, filename }));
    })
    .then(({ blob, filename }) => {
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    })
    .catch(error => console.error(error));
}
