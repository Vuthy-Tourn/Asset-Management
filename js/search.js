document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const searchForm = document.getElementById("searchForm");
  let searchTimer;

  // Only proceed if this is a live search form
  if (!searchForm || !searchForm.dataset.liveSearch) return;

  // Save cursor position before any potential focus loss
  function saveCursorPosition() {
    sessionStorage.setItem(
      "searchCursorPos",
      searchInput.selectionStart.toString()
    );
  }

  // Restore cursor position after AJAX update
  function restoreCursorPosition() {
    const savedPos = sessionStorage.getItem("searchCursorPos");
    if (savedPos) {
      searchInput.setSelectionRange(savedPos, savedPos);
      sessionStorage.removeItem("searchCursorPos");
    }
  }

  searchInput.addEventListener("input", function () {
    saveCursorPosition();
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () {
      performLiveSearch();
    }, 500); // 500ms delay before search
  });

  searchInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault(); // Prevent form submission
      clearTimeout(searchTimer);
      performLiveSearch();
    }
  });

  function performLiveSearch() {
    const formData = new FormData(searchForm);
    formData.append("ajax", "1"); // Add AJAX flag

    // Show loading indicator if you have one
    // document.getElementById('searchLoading').classList.remove('hidden');

    fetch(window.location.href + "?" + new URLSearchParams(formData), {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.text())
      .then((html) => {
        // Replace just the table content
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        const newTable = doc.querySelector(".overflow-x-auto");

        if (newTable) {
          document.querySelector(".overflow-x-auto").outerHTML =
            newTable.outerHTML;
        }

        // Restore focus and cursor position
        searchInput.focus();
        restoreCursorPosition();
      })
      .catch((error) => {
        console.error("Search error:", error);
      })
      .finally(() => {
        // Hide loading indicator if you have one
        // document.getElementById('searchLoading').classList.add('hidden');
      });
  }
});
document.addEventListener("DOMContentLoaded", function () {
  // Handle filter form submission
  const filterForm = document.getElementById("filterForm");
  if (filterForm) {
    // Submit form when any filter changes
    filterForm.querySelectorAll("select").forEach((select) => {
      select.addEventListener("change", function () {
        // Reset to page 1 when filters change
        filterForm.querySelector('input[name="page"]').value = 1;
        filterForm.submit();
      });
    });
  }
});