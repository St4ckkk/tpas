// Document operation functions
const sideMenu = document.querySelector("aside");
const menuBtn = document.querySelector("#menu-btn");
const closeBtn = document.querySelector("#close-btn");
const themeToggler = document.querySelector(".theme-toggler");

// Check for saved theme preference and apply it
document.addEventListener("DOMContentLoaded", () => {
  const currentTheme = localStorage.getItem("theme") || "light";
  if (currentTheme === "dark") {
    document.body.classList.add("dark-theme-variables");
    themeToggler.querySelector("span:nth-child(1)").classList.remove("active");
    themeToggler.querySelector("span:nth-child(2)").classList.add("active");
  }

  // Handle clicks on the theme toggler
  themeToggler.addEventListener("click", () => {
    document.body.classList.toggle("dark-theme-variables");

    const isDarkMode = document.body.classList.contains("dark-theme-variables");
    themeToggler
      .querySelector("span:nth-child(1)")
      .classList.toggle("active", !isDarkMode);
    themeToggler
      .querySelector("span:nth-child(2)")
      .classList.toggle("active", isDarkMode);

    // Save the current theme preference to local storage
    localStorage.setItem("theme", isDarkMode ? "dark" : "light");
  });
});

// Sidebar toggle behavior remains the same
menuBtn.addEventListener("click", () => {
  sideMenu.style.display = "block";
});
closeBtn.addEventListener("click", () => {
  sideMenu.style.display = "none";
});

// Close the modal when clicking outside of it
window.onclick = function (event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
};
