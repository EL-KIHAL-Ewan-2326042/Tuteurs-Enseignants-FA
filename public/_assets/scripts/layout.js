const toggleMenu = document.getElementById('toggleMenu');
const mainNav = document.getElementById('mainNav');

toggleMenu.onclick = function() {
    if (mainNav.style.display === 'none') {
        mainNav.style.display = 'flex';
    } else {
        mainNav.style.display = 'none';
    }
};