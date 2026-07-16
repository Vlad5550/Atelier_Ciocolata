// Afiseaza istoricul comenzilor salvate in localStorage.
function loadOrders(){
  let user = DB.getCurrentUser();
  let container = document.getElementById("orders-list");

  if(!user || !container) return;

  container.innerHTML = "";

  if(!user.orders || user.orders.length === 0){
    container.innerHTML = "<p>Nu ai comenzi încă</p>";
    return;
  }

  user.orders.forEach(o => {
    container.innerHTML += `
      <div class="card fade-in">
        <h3>📅 ${o.date}</h3>
        <p>💰 Total: ${o.total} RON</p>
        <p>📦 Produse: ${o.items.length}</p>
      </div>
    `;
  });
}

document.addEventListener("DOMContentLoaded", loadOrders);
