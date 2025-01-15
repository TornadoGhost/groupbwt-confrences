// TODO: Script does not work properly, request return data, but script does not showing it
export function globalSearch() {
  const searchInput = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');
  const reportsContainer = document.getElementById('fill-search-result-reports');
  const conferencesContainer = document.getElementById('fill-search-result-conferences');
  let controller;
  modalEvents();
  filterReportCheckboxEvent();
  filterConferenceCheckboxEvent();

  function filterReportCheckboxEvent() {
    const filterReport = document.getElementById('filter-reports');
    filterReport.addEventListener('change', function() {
      const inputData = searchInput.value;
      fetchDataFromSearchByTitle(inputData);
    });
  }

  function filterConferenceCheckboxEvent() {
    const filterConference = document.getElementById('filter-conferences');
    filterConference.addEventListener('change', function() {
      const inputData = searchInput.value;
      fetchDataFromSearchByTitle(inputData);
    });
  }

  function modalEvents() {
    let opened = false;
    searchInput.addEventListener('input', function () {
      if (this.value && !opened) {
        opened = true;
        searchResults.classList.remove('d-none');
      } else if (!this.value) {
        opened = false;
        searchResults.classList.add('d-none');
      }

      if (this.value && opened) {
        fetchDataFromSearchByTitle(this.value);
      }
    });

    document.addEventListener('click', function (event) {
      if (opened && event.target.closest('div#search-results') !== searchResults) {
        closeSearch();
      }
    });

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

  function fetchDataFromSearchByTitle(title) {
    if (controller) {
      controller.abort();
    }

    controller = new AbortController();
    const signal = controller.signal;

    reportsContainer.innerHTML = '';
    conferencesContainer.innerHTML = '';
    const filterReport = document.getElementById('filter-reports');
    const filterConference = document.getElementById('filter-conferences');


    fetch(`/api/v1/global-search?title=${title}&type=${filterReport.checked ? 'report' : ''},${filterConference.checked ? 'conference' : ''}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      },
      signal: signal
    })
      .then(response => {
        if (response.status === 204) {
          return {};
        }
        return response.json();
      })
      .then(({data}) => {
        if (!data) {
          return;
        }

        if (data.reports) {
          data.reports.forEach(value => {
            const a = document.createElement('a');
            a.href = `https://localhost/conferences/${value?.conference_id}/reports/${value?.id}`;
            a.textContent = `${value?.title}`;
            a.target = "_blank";
            a.classList.add('d-block');
            a.classList.add('mb-1');
            reportsContainer.appendChild(a);
          });
        }

        if (data.conferences) {
          data.conferences.forEach(value => {
            const a = document.createElement('a');
            a.href = `https://localhost/conferences/${value?.id}`;
            a.textContent = `${value?.title}`;
            a.target = "_blank";
            a.classList.add('d-block');
            a.classList.add('mb-1');
            conferencesContainer.appendChild(a);
          });
        }
      })
      .catch(exception => {
        if (signal.aborted) {
          console.log('Запит було скасовано.');
        } else {
          console.error(`Fetch failed with exception: ${exception}`);
        }
      });
  }
}
