export function commentsLazyLoading(path) {
  let page = 1;
  const commentsContainer = document.getElementById('comment-list');
  let isLoading = false;

  function loadComments() {
    if (isLoading) return;

    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
      isLoading = true;

      fetch(path + '?page=' + page++)
        .then(response => response.json())
        .then(data => {
          if (data.comments) {
            commentsContainer.insertAdjacentHTML('beforeend', data.comments);
          }

          if (!data.nextPage) {
            window.removeEventListener('scroll', loadComments);
          }

          isLoading = false;
        });
    }
  }

  window.addEventListener('load', function() {
    loadComments();
  });

  window.addEventListener('scroll', loadComments);
}commentsLazyLoading();
