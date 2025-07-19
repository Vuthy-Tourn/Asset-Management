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
    formData.append("ajax", "1");

    fetch(window.location.href + "?" + new URLSearchParams(formData), {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.text())
      .then((html) => {
        // Replace the ENTIRE table container (including empty state)
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        const newContainer = doc.querySelector(
          ".bg-white.rounded-lg.shadow-sm"
        );

        if (newContainer) {
          document.querySelector(".bg-white.rounded-lg.shadow-sm").outerHTML =
            newContainer.outerHTML;
        }

        searchInput.focus();
        restoreCursorPosition();
      })
      .catch((error) => {
        console.error("Search error:", error);
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