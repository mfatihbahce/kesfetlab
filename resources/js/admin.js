// Kesfet Lab - Admin JavaScript
console.log("Kesfet Lab admin paneli yüklendi");

// Admin paneli fonksiyonları
function initAdminPanel() {
    console.log("Admin paneli başlatılıyor");
    
    // Sidebar toggle
    const sidebarToggle = document.querySelector(".sidebar-toggle");
    if (sidebarToggle) {
        sidebarToggle.addEventListener("click", function() {
            const sidebar = document.querySelector(".admin-sidebar");
            sidebar.classList.toggle("collapsed");
        });
    }
    
    // Tablo sıralama
    const tables = document.querySelectorAll(".sortable-table");
    tables.forEach(table => {
        const headers = table.querySelectorAll("th[data-sort]");
        headers.forEach(header => {
            header.addEventListener("click", function() {
                const column = this.dataset.sort;
                sortTable(table, column);
            });
        });
    });
}

function sortTable(table, column) {
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    
    rows.sort((a, b) => {
        const aValue = a.querySelector(`td[data-${column}]`).textContent;
        const bValue = b.querySelector(`td[data-${column}]`).textContent;
        return aValue.localeCompare(bValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Admin paneli başlatma
document.addEventListener("DOMContentLoaded", function() {
    initAdminPanel();
});