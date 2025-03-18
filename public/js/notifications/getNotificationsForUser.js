import {addNotificationBudgeOnButton} from "./addNotificationBudgeOnButton.js";

export function getNotificationsForUser() {
  fetch('/api/v1/notifications/', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDIyMTcxMjAsImV4cCI6MTc0MjgyMTkyMCwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQGV4YW1wbGUuY29tIn0.CDZh8_a9g4s3Ibb2uRzioGppH1KDTNHO0wyRevBthOf2JabLXEsKahQ8YrYdmwl23zgDQ0vH8TgLICz8TA_kvW4PHOKGzVS2Fho5u9L_o7vzaRKNOqpvYzqecApNQdol5-aXDHiVH_FapIi4nfD_jBAvTXd5xiHPgoXQUltsBci_lKbdgVRyDLLbGfMlqEzSxbEcjXszgQEfxvb7uhOGLNk1AQAKVT1zhb4njmaAnr8vi41o0JBn67AU_i_oKbtJPfiflF4nJxJm-45sitwt0oPurV2pPwzJ032n9GwV7IFigwxDOq9YVzB0uIz8l4jnVlYmtWioE40p7T-f4JQPow',
    }
  }).then(response => response.json())
    .then((data) => {
      const notificationsList = document.getElementById('notification-list');
      notificationsList.innerHTML = null;
      let notWatchedNotifications = 0;
      data.forEach((notification) => {
        let elem = ``;
        if (notification.viewed) {
          elem = `
              <li class="list-group-item bg-dark border-white border-top-0 border-left-0 border-right-0">
                  <div>
                      <div class="d-flex justify-content-between">
                          <div class="notification-status">
                              <p class="h5">${notification.title}</p>
                          </div>
                          <p>${notification.createdAt}</p>
                      </div>
                      <p class="mb-0 text-pre-wrap">${notification.message}</p>
                  </div>
              </li>
          `;
        } else {
          notWatchedNotifications += 1;
          elem = `
              <li class="list-group-item bg-dark border-white border-top-0 border-left-0 border-right-0">
                  <div>
                      <div class="d-flex justify-content-between">
                          <div class="d-flex notification-status">
                              <p class="text-danger mr-1">New</p>
                              <p class="h5">${notification.title}</p>
                          </div>
                          <p>${notification.createdAt}</p>
                      </div>
                      <p class="mb-0 text-pre-wrap">${notification.message}</p>
                  </div>
              </li>
          `;
        }
        notificationsList.insertAdjacentHTML('beforeend', elem);
      });

      if (notWatchedNotifications > 1) {
        addNotificationBudgeOnButton();
      }
    });
}
