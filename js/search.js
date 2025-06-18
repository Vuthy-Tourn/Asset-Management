// Search functionality
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const searchForm = document.getElementById("searchForm");
  let searchTimer;

  // Focus the input field on page load if it had focus before submit
  if (sessionStorage.getItem("hadFocus") === "true") {
    searchInput.focus();
    // Restore cursor position if available
    const cursorPos = parseInt(sessionStorage.getItem("cursorPos")) || 0;
    searchInput.setSelectionRange(cursorPos, cursorPos);
    sessionStorage.removeItem("hadFocus");
  }

  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () {
      // Save focus state and cursor position before submit
      sessionStorage.setItem("hadFocus", "true");
      sessionStorage.setItem(
        "cursorPos",
        searchInput.selectionStart.toString()
      );
      searchForm.submit();
    }, 500);
  });

  searchInput.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      clearTimeout(searchTimer);
      sessionStorage.setItem("hadFocus", "true");
      sessionStorage.setItem(
        "cursorPos",
        searchInput.selectionStart.toString()
      );
      searchForm.submit();
    }
  });
});
