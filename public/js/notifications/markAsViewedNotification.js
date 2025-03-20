export function markAsViewedNotification(notificationId) {
  fetch(`/api/v1/notifications/${notificationId}/viewed`, {
    method: 'PATCH',
    headers: {
      'Authorization': 'Bearer ' + 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDIzMDc4NDQsImV4cCI6MTc0MjkxMjY0NCwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQGV4YW1wbGUuY29tIn0.s4wc__pNQHetM_XBd6ESRDTuHI8BCnh4U_awaqlnaq69REZ0pUj4uD1dXqcCa6vACAj-6QT8M0yT5vRbkGdBomv8LPZob_vjLRwSCq3oOlnwWesD-cV7Kc13ZcxCeK9bp1BNeUmf_ci_J2-2xNDpSmBMP1eIpqwp9vs_KKkzzc6df33DLcEd6EtPdJrGrwb4DWA7Oxc0knfn4cQA0H4PUn3k_qNuyqz1LBTkvcdPekgRz0t3Dv0dqRWxwqGdUER73Y5zYmLjzVD7iyz_cP_SM1aeyE2lvAw7Si1q6d_pl3IsOA_PddnWP08rQuGPsSv155byitDcnZcQlrfc9RVrjw',
    }
  })
    .catch(errors => console.error(errors))
  ;
}
