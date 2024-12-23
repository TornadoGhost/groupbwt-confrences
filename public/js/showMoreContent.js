export function showMoreContent() {
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-content-btn').forEach(button => {
      button.addEventListener('click', () => {
        const shortContent = button.previousElementSibling.previousElementSibling;
        const fullContent = button.previousElementSibling;

        if (fullContent.style.display === 'none') {
          shortContent.style.display = 'none';
          fullContent.style.display = 'inline';
          button.textContent = 'Hide';
        } else {
          shortContent.style.display = 'inline';
          fullContent.style.display = 'none';
          button.textContent = 'Read more';
        }
      });
    });
  });
}
