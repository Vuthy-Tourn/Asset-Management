class PaginationComponent {
  constructor(container, options = {}) {
    this.container = container;
    this.currentPage = parseInt(container.dataset.currentPage) || 1;
    this.totalPages = parseInt(container.dataset.totalPages) || 1;
    this.totalItems = parseInt(container.dataset.totalItems) || 0;
    this.baseUrl = options.baseUrl || window.location.pathname;
    this.onPageChange = options.onPageChange || this.defaultPageChange;
    this.maxVisiblePages = options.maxVisiblePages || 5;

    this.render();
  }

  render() {
    const paginationHTML = this.generatePaginationHTML();
    this.container.innerHTML = paginationHTML;
    this.attachEventListeners();
  }

  generatePaginationHTML() {
    if (this.totalPages <= 1) return "";

    let html = `
                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                        <!-- Results Info -->
                        <div class="text-sm text-slate-600">
                            Showing <span class="font-semibold text-slate-800">${this.getStartItem()}</span> 
                            to <span class="font-semibold text-slate-800">${this.getEndItem()}</span> 
                            of <span class="font-semibold text-slate-800">${
                              this.totalItems
                            }</span> results
                        </div>
                        
                        <!-- Pagination Controls -->
                        <div class="flex items-center space-x-2">
                `;

    // Previous button
    html += this.generateButton(
      "prev",
      '<i class="fas fa-chevron-left"></i>',
      this.currentPage - 1,
      this.currentPage <= 1,
      "px-3 py-2 text-sm"
    );

    // Page numbers
    const pageNumbers = this.getVisiblePageNumbers();

    pageNumbers.forEach((page) => {
      if (page === "...") {
        html += '<span class="px-3 py-2 text-slate-400">...</span>';
      } else {
        const isActive = page === this.currentPage;
        html += this.generateButton(
          `page-${page}`,
          page.toString(),
          page,
          false,
          `px-3 py-2 text-sm ${
            isActive
              ? "bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg"
              : "text-slate-600 hover:text-slate-800"
          }`
        );
      }
    });

    // Next button
    html += this.generateButton(
      "next",
      '<i class="fas fa-chevron-right"></i>',
      this.currentPage + 1,
      this.currentPage >= this.totalPages,
      "px-3 py-2 text-sm"
    );

    html += `
                        </div>
                    </div>
                `;

    return html;
  }

  generateButton(id, content, page, disabled, classes) {
    const baseClasses =
      "rounded-xl font-medium transition-all duration-200 border";
    const disabledClasses = disabled
      ? "bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200"
      : "bg-white hover:bg-slate-50 border-slate-200 hover:border-slate-300 hover:shadow-md transform hover:scale-105";

    return `
                    <button 
                        data-page="${page}" 
                        data-action="${id}"
                        class="${baseClasses} ${disabledClasses} ${classes}"
                        ${disabled ? "disabled" : ""}
                    >
                        ${content}
                    </button>
                `;
  }

  getVisiblePageNumbers() {
    const pages = [];
    const half = Math.floor(this.maxVisiblePages / 2);
    let start = Math.max(1, this.currentPage - half);
    let end = Math.min(this.totalPages, start + this.maxVisiblePages - 1);

    // Adjust start if we're near the end
    if (end - start + 1 < this.maxVisiblePages) {
      start = Math.max(1, end - this.maxVisiblePages + 1);
    }

    // Add first page and ellipsis if needed
    if (start > 1) {
      pages.push(1);
      if (start > 2) pages.push("...");
    }

    // Add visible page numbers
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }

    // Add ellipsis and last page if needed
    if (end < this.totalPages) {
      if (end < this.totalPages - 1) pages.push("...");
      pages.push(this.totalPages);
    }

    return pages;
  }

  getStartItem() {
    return Math.min((this.currentPage - 1) * 5 + 1, this.totalItems);
  }

  getEndItem() {
    return Math.min(this.currentPage * 5, this.totalItems);
  }

  attachEventListeners() {
    const buttons = this.container.querySelectorAll("button[data-page]");
    buttons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        const page = parseInt(button.dataset.page);
        if (!button.disabled && page !== this.currentPage) {
          this.onPageChange(page);
        }
      });
    });
  }

  defaultPageChange(page) {
    const url = new URL(this.baseUrl, window.location.origin);
    url.searchParams.set("page", page);
    window.location.href = url.toString();
  }

  // Method to update pagination state (useful for AJAX)
  updateState(currentPage, totalPages, totalItems) {
    this.currentPage = currentPage;
    this.totalPages = totalPages;
    this.totalItems = totalItems;
    this.render();
  }
}

// Initialize pagination when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  const paginationContainer = document.querySelector(".pagination-container");
  if (paginationContainer) {
    new PaginationComponent(paginationContainer, {
      maxVisiblePages: 5,
      // You can add custom onPageChange handler for AJAX functionality
      // onPageChange: function(page) {
      //     loadActivityPage(page);
      // }
    });
  }
});

// Function for refresh button
function refreshActivity() {
  window.location.reload();
}

// CSS animations
const style = document.createElement("style");
style.textContent = `
            @keyframes fade-in {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fade-in 0.5s ease-out forwards;
            }
        `;
document.head.appendChild(style);
