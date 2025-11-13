document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ Panel admin activo");

    // Alternar sidebar
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.createElement("button");
    toggleBtn.className = "btn btn-dark position-fixed top-0 start-0 m-2";
    toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
    document.body.appendChild(toggleBtn);

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("d-none");
    });
});
