// Menu Toggle con Overlay para Móvil
const toggle = document.querySelector(".toggle");
const navigation = document.querySelector(".navigation");
const main = document.querySelector(".main");

// Crear overlay para móvil
let sidebarOverlay = document.querySelector(".sidebar-overlay");
if (!sidebarOverlay) {
    sidebarOverlay = document.createElement("div");
    sidebarOverlay.className = "sidebar-overlay";
    document.body.appendChild(sidebarOverlay);
}

if (toggle && navigation && main) {
    toggle.onclick = function () {
        navigation.classList.toggle("active");
        main.classList.toggle("active");
        
        // Activar overlay en móvil
        if (window.innerWidth <= 768) {
            sidebarOverlay.classList.toggle("active");
        }
    };
    
    // Cerrar sidebar al hacer clic en el overlay
    sidebarOverlay.onclick = function () {
        navigation.classList.remove("active");
        main.classList.remove("active");
        sidebarOverlay.classList.remove("active");
    };
    
    // Manejar cambio de tamaño de ventana
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            sidebarOverlay.classList.remove("active");
        }
    });
}


// ==================== LÓGICA DE MODALES GENERALES ====================

// Modal de Contacto
const contactModal = document.getElementById("contactModal");
const closeContact = document.getElementById("closeContact");

if (contactModal && closeContact) {
    // Estas funciones deben ser globales (window.) para que los botones 'onclick' del HTML puedan llamarlas.
    window.mostrarInfo = function () {
        contactModal.style.display = "flex";
    };

    closeContact.onclick = function () {
        contactModal.style.display = "none";
    };
}

// Modal de Cerrar Sesión
const logoutModal = document.getElementById("logoutModal");
const closeLogoutBtn = document.querySelector("#logoutModal .close-btn");
const cancelBtn = document.getElementById("cancelBtn");

if (logoutModal && closeLogoutBtn && cancelBtn) {
    // Estas funciones deben ser globales (window.) para que los botones 'onclick' del HTML puedan llamarlas.
    window.showLogoutModal = function () {
        logoutModal.style.display = "flex";
    };

    closeLogoutBtn.onclick = function () {
        logoutModal.style.display = "none";
    };

    cancelBtn.onclick = function () {
        logoutModal.style.display = "none";
    };
}

// Lógica de cierre general de modales
window.onclick = function (event) {
    if (contactModal && event.target === contactModal) {
        contactModal.style.display = "none";
    }
    if (logoutModal && event.target === logoutModal) {
        logoutModal.style.display = "none";
    }
};