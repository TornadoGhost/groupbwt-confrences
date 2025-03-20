import {addNotificationBudgeOnButton} from "../notifications/addNotificationBudgeOnButton.js";

export function testPusher(isGranted) {
  /*if (!isGranted) {
    return false;
  }
  const helloHandler = function () {
    fetch('/api/pusher/say-hello', {
      method: 'POST'
    });

    document.removeEventListener('click', helloHandler);
  }

  document.getElementById('say-hello').addEventListener('click', helloHandler);

  const pusher = new Pusher('9ee3cd5959ce0b5242f0', {
    cluster: 'eu'
  });

  const channel = pusher
    .subscribe('greetings')
    .bind('new-greeting', function (data) {
      console.log(data);
      const list = document.getElementById('test-list')
      list.innerHTML = '';
      if (data) {
        Object.entries(data).forEach(elem => {
          list.insertAdjacentHTML('beforeend',
            `<li>${elem[0]}</li>`
          );
        });
      }
    });

  const unsubscribeHandler = function () {
    pusher.unsubscribe('new-greeting');
    console.log('Unsubscribe Event Done');
    document.removeEventListener('click', unsubscribeHandler);
  }
  document.getElementById('unsubscribe').addEventListener('click', unsubscribeHandler);

  const privatePusher = new Pusher('9ee3cd5959ce0b5242f0', {
    cluster: 'eu',
    authEndpoint: 'https://localhost/api/pusher/auth',
    auth: {
      headers: {
        'Authorization': 'Bearer ' + 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDA2NDg4MTUsImV4cCI6MTc0MTI1MzYxNSwicm9sZXMiOlsiUk9MRV9MSVNURU5FUiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6InNpcGVzLm5lbHNvbkBydW5vbGZzZG90dGlyLmNvbSJ9.ObcgPBYm0Wa9yed1DYoFu4wSTEachYcJAwKvWQmPNgM4cUbDMS_XNGZz3C7cP9HNXOLSJGE11jl_WUSnJ9CFXUuwK5paQSOMLMxHeDbExuGVK4dN-fbRlWw0WnRl7nh9q3eJP56YEWn9OgF5W11wdgXfONQ-FZWi5swZJ3pw0LMvjHMHFy54dac56QTP13ZbjQGflmpHYnxbBd4tl_5jKSwkDoW9ehppg1cj-_U9y_lJiulF61r8HJg7q55aOmczm9KnvrACGgpWjyBe5i_tmUsPIt0-vqunuAmZzz4tc-ps5adx6CivgwDvbG15V9Zo3JQiiYbMTugpaxmQNuUodA',
      }
    }
  });

  privatePusher
    .subscribe('private-v-chat.260')
    .bind('big-troubles', function (data) {
      document.getElementById('admin-message').innerHTML = '';

      if (data?.message) {
        document.getElementById('admin-message').innerHTML = data.message;
      }

      console.log(data);
    });

  const adminPrivateMessageHandler = function () {
    fetch('/api/pusher/admin-notify', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDA1NzUzNTcsImV4cCI6MTc0MTE4MDE1Nywicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQGV4YW1wbGUuY29tIn0.VSw3pUTI3_5dT7Fh3nwS_G1jDJpx1qZ-alrpCBccBH0n7KkNfbB7jjtpMWEa-HJMLYc64CGHDmTcfMUA_5sBchfCtDB9oxcVNUvNDl8u6kXyHktXrq_h9ImCVt7t5bRuRwIBbYzjflomiMu1XgtgVQTrzULARTXPlFoojMD66JW18kGxc3tmQge6F16OV7a6D2jaKuvCg-a3JgTbJuBsIzrBGyLEp0el8iFBgn0heiSZS40zH2LUt8u_vXlpoSZb1-Gl2WUxfGJPI41lytoUuxBGAZpxJv9nPVxz-mxe1u5fwfOluEkWDi-zMDnKhUXHXa3yUh9e68zaf4suAS0Zjw',
      },
      params: {
        socket_id: privatePusher.connection.socket_id
      }
    });
    document.removeEventListener('click', adminPrivateMessageHandler);
  };

  document.getElementById('admin-private-message').addEventListener('click', adminPrivateMessageHandler);*/

  // Import csv conferences notification test


  // TODO: change to private channel, so user can get notification addressed to him
  const pusher = new Pusher('9ee3cd5959ce0b5242f0', {
    cluster: 'eu',
    forceTLS: true,  // Гарантує використання WebSockets через HTTPS
    disableStats: true, // Вимикає додаткові статистичні запити, які відправляються на сервер Pusher
    enabledTransports: ['ws', 'wss'] // Примушує використовувати тільки WebSockets
  });

  const notificationChannel = pusher.subscribe('notification');
  notificationChannel.bind('success-import', function (data) {
    console.log('Success import message - ', data);
    addNewNotification(data);
  });
  notificationChannel.bind('error-import', function (data) {
    console.log('Error import message - ', data);
    addNewNotification(data);
  });

  // TODO: move this function and reuse it by import here and in getNotificationsForUser
  function addNewNotification(data) {
    const notificationList = document.getElementById('notification-list');
    notificationList.insertAdjacentHTML('afterbegin', `
        <li class="list-group-item bg-dark border-white border-top-0 border-left-0 border-right-0">
            <div>
                <div class="d-flex justify-content-between">
                    <div class="d-flex notification-status">
                        <p class="text-danger mr-1">New</p>
                        <p class="h5">${data.title}</p>
                    </div>
                    <p>${data.createdAt}</p>
                </div>
                <p class="mb-0 text-pre-wrap">${data.message}</p>
            </div>
        </li>
    `);
    addNotificationBudgeOnButton();
  }
}
