export function globalSearch() {
  const reportsTest = [
    {title: "Report-1", link: "https://example.com/Report-1"},
    {title: "Report-2", link: "https://example.com/Report-2"},
    {title: "Report-3", link: "https://example.com/Report-3"},
    {title: "Report-4", link: "https://example.com/Report-4"},
  ];

  const conferencesTest = [
    {title: "Conferences-1", link: "https://example.com/Conferences-1"},
    {title: "Conferences-2", link: "https://example.com/Conferences-2"},
    {title: "Conferences-3", link: "https://example.com/Conferences-3"},
    {title: "Conferences-4", link: "https://example.com/RConferences-4"},
  ];

  const searchInput = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');

  // TODO: Turn off Report or Conference search by checkbox
  let opened = false;
  searchInput.addEventListener('input', function () {
    if (this.value && !opened) {
      opened = true;
      searchResults.classList.remove('d-none');
      const reportsContainer = document.getElementById('fill-search-result-reports');
      const conferencesContainer = document.getElementById('fill-search-result-conferences');

      // TODO: replace example with real
      reportsTest.forEach(value => {
        const a = document.createElement('a');
        a.href = `${value.link}`;
        a.textContent = `${value.title}`;
        a.target = "_blank";
        reportsContainer.appendChild(a);
        a.classList.add('d-block');
        a.classList.add('mb-1');
      });

      // TODO: replace example with real
      conferencesTest.forEach(value => {
        const a = document.createElement('a');
        a.href = `${value.link}`;
        a.textContent = `${value.title}`;
        a.target = "_blank";
        a.classList.add('d-block');
        a.classList.add('mb-1');
        conferencesContainer.appendChild(a);
      });

    } else if (!this.value) {
      opened = false;
      searchResults.classList.add('d-none');
    }
  });
  document.addEventListener('click', function (event) {
    if (opened && event.target.closest('div#search-results') !== searchResults) {
      closeSearch();
    }
  })
  document.addEventListener('keyup', function (event) {
    if (event.key === 'Escape' && opened) {
      closeSearch();
    }
  });

  function closeSearch() {
    opened = false;
    searchResults.classList.add('d-none');
  }
}
