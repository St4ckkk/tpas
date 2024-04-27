// Handle sidebar links for active state
const sideLinks = document.querySelectorAll(
  ".sidebar .side-menu li a:not(.logout)"
);

sideLinks.forEach((item) => {
  item.addEventListener("click", () => {
    sideLinks.forEach((link) => link.parentElement.classList.remove("active"));
    item.parentElement.classList.add("active");
  });
});

// Toggle sidebar visibility
const menuBar = document.querySelector(".content nav .bx.bx-menu");
const sideBar = document.querySelector(".sidebar");

menuBar.addEventListener("click", () => {
  sideBar.classList.toggle("close");
});

// Toggle search form on mobile views
const searchBtn = document.querySelector(
  ".content nav form .form-input button"
);
const searchBtnIcon = searchBtn.querySelector(".bx");
const searchForm = document.querySelector(".content nav form");

searchBtn.addEventListener("click", (e) => {
  if (window.innerWidth < 576) {
    e.preventDefault();
    searchForm.classList.toggle("show");
    searchBtnIcon.classList.toggle("bx-search");
    searchBtnIcon.classList.toggle("bx-x");
  }
});

// Ensure UI elements adapt properly on window resize
window.addEventListener("resize", () => {
  if (window.innerWidth < 768) {
    sideBar.classList.add("close");
  } else {
    sideBar.classList.remove("close");
  }
  if (window.innerWidth > 576) {
    searchBtnIcon.classList.replace("bx-x", "bx-search");
    searchForm.classList.remove("show");
  }
});

// Theme toggler with local storage support
const toggler = document.getElementById("theme-toggle");

const currentTheme = localStorage.getItem("theme") || "light";
document.body.classList.toggle("dark", currentTheme === "dark");
toggler.checked = currentTheme === "dark";

toggler.addEventListener("change", () => {
  document.body.classList.toggle("dark", toggler.checked);
  localStorage.setItem("theme", toggler.checked ? "dark" : "light");
});
