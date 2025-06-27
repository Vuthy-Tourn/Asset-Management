// index.js

// Chart initialization functions
function initCategoryChart() {
  const canvas = document.getElementById("categoryChart");
  if (!canvas) return null;
  const labels = JSON.parse(canvas.dataset.labels || "[]");
  const data = JSON.parse(canvas.dataset.values || "[]");

  const modernColors = [
    "#3B82F6",
    "#10B981",
    "#F59E0B",
    "#EF4444",
    "#8B5CF6",
    "#EC4899",
    "#14B8A6",
    "#F97316",
    "#6366F1",
    "#84CC16",
  ];

  return new Chart(canvas.getContext("2d"), {
    type: "doughnut",
    data: {
      labels: labels,
      datasets: [
        {
          data: data,
          backgroundColor: modernColors,
          borderWidth: 0,
          hoverBorderWidth: 4,
          hoverBorderColor: "#ffffff",
          hoverOffset: 8,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: "65%",
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            padding: 25,
            usePointStyle: true,
            pointStyle: "circle",
            font: { size: 14, weight: "600" },
            color: "#374151",
          },
        },
        tooltip: {
          backgroundColor: "rgba(0, 0, 0, 0.9)",
          titleColor: "#ffffff",
          bodyColor: "#ffffff",
          borderColor: "rgba(255, 255, 255, 0.2)",
          borderWidth: 1,
          cornerRadius: 12,
          displayColors: true,
          titleFont: { size: 16, weight: "bold" },
          bodyFont: { size: 14 },
          padding: 12,
          callbacks: {
            label: function (context) {
              return `${context.raw} products`;
            },
          },
        },
      },
      animation: {
        duration: 2500,
        easing: "easeOutCubic",
      },
    },
  });
}

function initFloorChart() {
  const canvas = document.getElementById("floorChart");
  if (!canvas) return null;

  const labels = JSON.parse(canvas.dataset.labels || "[]");
  const data = JSON.parse(canvas.dataset.values || "[]");

  const modernColors = [
    "#3B82F6",
    "#10B981",
    "#F59E0B",
    "#EF4444",
    "#8B5CF6",
    "#EC4899",
    "#14B8A6",
    "#F97316",
    "#6366F1",
    "#84CC16",
  ];

  return new Chart(canvas.getContext("2d"), {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Categories per Floor",
          data: data,
          backgroundColor: modernColors.slice(2, data.length),
          borderRadius: 8,
          borderSkipped: false,
          hoverBackgroundColor: modernColors
            .slice(0, data.length)
            .map((color) => color + "CC"),
          hoverBorderWidth: 2,
          hoverBorderColor: "#ffffff",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "rgba(0, 0, 0, 0.9)",
          titleColor: "#ffffff",
          bodyColor: "#ffffff",
          borderColor: "rgba(255, 255, 255, 0.2)",
          borderWidth: 1,
          cornerRadius: 12,
          displayColors: true,
          titleFont: { size: 16, weight: "bold" },
          bodyFont: { size: 14 },
          padding: 12,
          callbacks: {
            label: function (context) {
              return `${context.parsed.y} categories`;
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: "rgba(0, 0, 0, 0.1)", drawBorder: false },
          ticks: {
            color: "#6B7280",
            font: { size: 12, weight: "500" },
            stepSize: 1,
          },
        },
        x: {
          grid: { display: false },
          ticks: {
            color: "#374151",
            font: { size: 12, weight: "600" },
            maxRotation: 45,
            minRotation: 0,
          },
        },
      },
      animation: {
        duration: 2000,
        easing: "easeOutBounce",
      },
      interaction: {
        intersect: false,
        mode: "index",
      },
    },
  });
}

// Counter animation
function animateCounters() {
  const counters = document.querySelectorAll(".number-counter");
  counters.forEach((counter) => {
    const target = parseInt(counter.textContent);
    const increment = target / 50;
    let current = 0;

    const timer = setInterval(() => {
      current += increment;
      counter.textContent = Math.floor(current);

      if (current >= target) {
        counter.textContent = target;
        clearInterval(timer);
      }
    }, 30);
  });
}

// Initialize animations
function initAnimations() {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }
      });
    },
    { threshold: 0.1, rootMargin: "0px 0px -50px 0px" }
  );

  document
    .querySelectorAll(".animate-fade-in, .animate-slide-up")
    .forEach((el) => {
      el.style.opacity = "0";
      el.style.transform = "translateY(20px)";
      el.style.transition = "opacity 0.6s ease, transform 0.6s ease";
      observer.observe(el);
    });
}

// Card hover effects
function initCardHoverEffects() {
  const cards = document.querySelectorAll(".card-hover-effect");
  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-8px) scale(1.02)";
      this.style.boxShadow = "0 25px 60px -12px rgba(0, 0, 0, 0.25)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
      this.style.boxShadow = "";
    });
  });
}

// Refresh button functionality
function initRefreshButton() {
  const refreshBtn = document.querySelector(
    'button[onclick="refreshActivity()"]'
  );
  if (refreshBtn) {
    refreshBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const icon = this.querySelector("i");
      icon.classList.add("fa-spin");
      setTimeout(() => {
        icon.classList.remove("fa-spin");
        location.reload();
      }, 1000);
    });
  }
}

// Chart loading indicators
function initChartLoadingIndicators() {
  document.querySelectorAll("canvas").forEach((canvas) => {
    const container = canvas.parentElement;
    container.style.position = "relative";

    const spinner = document.createElement("div");
    spinner.className = "absolute inset-0 flex items-center justify-center";
    spinner.innerHTML = `
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        `;
    container.appendChild(spinner);

    setTimeout(() => spinner.remove(), 1000);
  });
}

// Main initialization function
function initDashboard() {
  // Initialize charts
  initCategoryChart();
  initFloorChart();

  // Initialize other components
  initAnimations();
  initCardHoverEffects();
  initRefreshButton();
  initChartLoadingIndicators();

  // Start counter animation after a delay
  setTimeout(animateCounters, 1000);

  console.log("🚀 Dashboard initialized successfully!");
}

// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", initDashboard);

// Function for refresh button
function refreshActivity() {
  window.location.reload();
}