export function show() {
  const notification = document.getElementById('notifications')
  const notificationButton = notification.querySelector('button[type=button]');
  const notificationList = notification.querySelector('div.notification-window').children[0];
  notificationButton.addEventListener('mousedown', function (event) {
    if (event.currentTarget === this) {
      if (notificationList.classList.contains('d-none')) {
        notificationList.classList.remove('d-none');
      } else {
        notificationList.classList.add('d-none');
      }
    }
  });

  document.addEventListener('mousedown', function (event) {
    if (!notification.contains(event.target) && !notificationList.classList.contains('d-none')) {
      notificationList.classList.add('d-none');
    }
  })
}
