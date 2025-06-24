// modal.js - Dynamic Modal Management System
class ModalManager {
  constructor() {
    this.modals = {};
    this.currentOpenModal = null;
    this.deleteCallbacks = {};
    this.init();
  }

  init() {
    // Initialize all modals on the page
    document.querySelectorAll(".modal").forEach((modal) => {
      const modalId = modal.id;
      this.modals[modalId] = modal;

      // Close when clicking outside
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          this.close(modalId);
        }
      });
    });

    // Close modals with ESC key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.currentOpenModal) {
        this.close(this.currentOpenModal);
      }
    });

    // Handle all buttons with data-modal-open attribute
    document.querySelectorAll("[data-modal-open]").forEach((button) => {
      const modalId = button.getAttribute("data-modal-open");
      button.addEventListener("click", () => this.open(modalId));
    });

    // Handle all buttons with data-modal-close attribute
    document.querySelectorAll("[data-modal-close]").forEach((button) => {
      const modalId = button.getAttribute("data-modal-close");
      button.addEventListener("click", () => this.close(modalId));
    });

    // Handle all buttons with data-modal-fetch attribute (for dynamic content)
    document.querySelectorAll("[data-modal-fetch]").forEach((button) => {
      const modalId = button.getAttribute("data-modal-fetch");
      const target =
        button.getAttribute("data-modal-target") || `${modalId}Content`;
      const url = button.getAttribute("data-modal-url");

      button.addEventListener("click", async () => {
        try {
          const targetElement = document.getElementById(target);
          if (!targetElement) {
            throw new Error(`Modal target element #${target} not found`);
          }

          // Show loading state if elements exist
          const loadingContent =
            targetElement.querySelector(".loading-content");
          const actualContent = targetElement.querySelector(".actual-content");

          if (loadingContent) loadingContent.classList.remove("hidden");
          if (actualContent) actualContent.classList.add("hidden");

          const response = await fetch(url);
          if (!response.ok) throw new Error("Network response was not ok");

          const html = await response.text();

          // Insert into actual content area if exists, otherwise directly into target
          const contentTarget = actualContent || targetElement;
          contentTarget.innerHTML = html;
          contentTarget.classList.remove("hidden");

          if (loadingContent) loadingContent.classList.add("hidden");

          this.open(modalId);
        } catch (error) {
          console.error("Error loading modal content:", error);
          // Show error in modal
          const errorMessage = `<div class="p-4 text-red-600">Error loading content: ${error.message}</div>`;
          const targetElement = document.getElementById(target);
          if (targetElement) {
            targetElement.innerHTML = errorMessage;
          }
          this.open(modalId);
        }
      });
    });

    // Handle all buttons with data-modal-delete attribute
    document.querySelectorAll("[data-modal-delete]").forEach((button) => {
      const modalId =
        button.getAttribute("data-modal-delete") || "deleteConfirmModal";
      const url = button.getAttribute("data-modal-url");
      const callback = button.getAttribute("data-modal-callback");

      button.addEventListener("click", () => {
        if (callback) {
          this.deleteCallbacks[modalId] = callback;
        } else if (url) {
          this.deleteCallbacks[modalId] = () => {
            window.location.href = url;
          };
        }
        this.open(modalId);
      });
    });

    // Handle delete confirmation buttons
    document
      .querySelectorAll("[data-modal-confirm-delete]")
      .forEach((button) => {
        const modalId =
          button.getAttribute("data-modal-confirm-delete") ||
          "deleteConfirmModal";

        button.addEventListener("click", () => {
          this.showLoader(button);
          if (this.deleteCallbacks[modalId]) {
            this.deleteCallbacks[modalId]();
          }
        });
      });

    // Handle form submissions
    document.addEventListener("submit", function (e) {
      const form = e.target;
      if (form.tagName === "FORM") {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton && !submitButton.disabled) {
          submitButton.disabled = true;
          const originalText = submitButton.innerHTML;
          submitButton.innerHTML =
            '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

          // Re-enable after 5 seconds as fallback
          setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
          }, 5000);
        }
      }
    });
  }

  open(modalId) {
    if (this.currentOpenModal) {
      this.close(this.currentOpenModal);
    }

    const modal = this.modals[modalId];
    if (modal) {
      modal.classList.remove("hidden");
      modal.classList.add("flex");
      document.body.style.overflow = "hidden";
      this.currentOpenModal = modalId;
    }
  }

  close(modalId) {
    const modal = this.modals[modalId];
    if (modal) {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
      document.body.style.overflow = "auto";
      if (this.currentOpenModal === modalId) {
        this.currentOpenModal = null;
      }
    }z
  }

  showLoader(element) {
    const originalHTML = element.innerHTML;
    element.setAttribute("data-original-html", originalHTML);
    element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    element.disabled = true;
  }

  hideLoader(element) {
    const originalHTML = element.getAttribute("data-original-html");
    if (originalHTML) {
      element.innerHTML = originalHTML;
      element.removeAttribute("data-original-html");
    }
    element.disabled = false;
  }

  showNotification(message, type = "success") {
    // Implement your notification system here
    console.log(`${type}: ${message}`);
  }
}

// Initialize the modal manager when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.modalManager = new ModalManager();
});
