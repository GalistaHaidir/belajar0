document.addEventListener("DOMContentLoaded", function () {
  // Toggle Sidebar Desktop
  const sidebar = document.getElementById("sidebar");
  const toggleBtn = document.querySelector(".toggle-btn");

  toggleBtn?.addEventListener("click", function () {
    sidebar.classList.toggle("expand");

    // Optional: Ganti ikon arah panah
    const icon = document.getElementById("icon");
    if (sidebar.classList.contains("expand")) {
      icon.classList.remove("bi-arrow-right-short");
      icon.classList.add("bi-arrow-left-short");
    } else {
      icon.classList.remove("bi-arrow-left-short");
      icon.classList.add("bi-arrow-right-short");
    }
  });

  // Sudah ada: Untuk mobile sidebar
  const hamburgerToggle = document.getElementById("hamburger-toggle");
  const mobileSidebar = document.getElementById("mobile-sidebar");
  const overlay = document.getElementById("mobile-sidebar-overlay");

  hamburgerToggle?.addEventListener("click", function () {
    mobileSidebar.classList.add("show");
    overlay.classList.add("active");
  });

  overlay?.addEventListener("click", function () {
    mobileSidebar.classList.remove("show");
    overlay.classList.remove("active");
  });
});


new Chart(document.getElementById("bar-chart-grouped"), {
  type: 'bar',
  data: {
    labels: ["1900", "1950", "1999", "2050"],
    datasets: [
      {
        label: "Africa",
        backgroundColor: "#3e95cd",
        data: [133, 221, 783, 2478]
      }, {
        label: "Europe",
        backgroundColor: "#8e5ea2",
        data: [408, 547, 675, 734]
      }
    ]
  },
  options: {
    title: {
      display: true,
      text: 'Population growth (millions)'
    }
  }
});
