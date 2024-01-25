function toggleMenu() {
    var menu = document.getElementById("menu");
    if (menu.style.left === "0px") {
        menu.style.left = "-100%";
    } else {
        menu.style.left = "0";
    }
}