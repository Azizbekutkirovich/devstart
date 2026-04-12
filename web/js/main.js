const burger = document.getElementById('burger');
const sideMenu = document.getElementById('sideMenu');
const overlay = document.getElementById('overlay');

burger.addEventListener('click', () => {
  burger.classList.toggle('active');
  sideMenu.classList.toggle('active');
  overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
  burger.classList.remove('active');
  sideMenu.classList.remove('active');
  overlay.classList.remove('active');
});

if (document.URL === "http://localhost/devstart/site/home") {
  document.getElementById("home").classList.add("active");
} else if (document.URL === "http://localhost/devstart/site/profile") {
  document.getElementById("profile").classList.add("active");
} else if (document.URL === "http://localhost/devstart/site/about-us") {
  document.getElementById("about-us").classList.add("active");
}
