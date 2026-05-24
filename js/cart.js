// Afiseaza cosul din localStorage pentru varianta HTML.
function loadCart(){
  const user = DB.getCurrentUser();
  if(!user) return;

  const container = document.getElementById("cart-items");

  let u = DB.getUsers().find(x => x.username === user.username);

  container.innerHTML = "";

  let total = 0;

  (u.cart || []).forEach((p, i) => {
    total += p.price * p.qty;

    container.innerHTML += `
      <div class="card cart-item">
        <img src="${p.image}">
        <div>
          <h3>${p.name}</h3>
          <p>${p.price} x ${p.qty}</p>
        </div>
        <button onclick="removeItem(${i})">❌</button>
      </div>
    `;
  });

  document.getElementById("total").innerText = total;
}

function removeItem(i){
  // Elimina produsul de pe pozitia primita din cos.
  let users = DB.getUsers();
  let user = DB.getCurrentUser();

  let u = users.find(x => x.username === user.username);

  u.cart.splice(i,1);

  DB.saveUsers(users);
  DB.setCurrentUser(u);

  loadCart();
}

function checkout(){
  // Verifica totalul, scade banii si salveaza comanda local.
  let users = DB.getUsers();
  let current = DB.getCurrentUser();

  if(!current) return;

  let user = users.find(u => u.username === current.username);

  if(!user || !user.cart || user.cart.length === 0){
    alert("Coș gol!");
    return;
  }

  let total = user.cart.reduce((sum, p) => sum + (p.price * p.qty), 0);

  if(user.money < total){
    alert("❌ Fonduri insuficiente!");
    return;
  }

  // 💰 SCĂDERE BANI
  user.money -= total;

  // 🧾 SALVARE COMANDĂ
  user.orders = user.orders || [];

  user.orders.push({
    date: new Date().toLocaleString(),
    total: total,
    items: user.cart
  });

  // 🛒 GOLIRE COȘ
  user.cart = [];

  DB.saveUsers(users);
  DB.setCurrentUser(user);

  alert("✅ Comandă finalizată!");

  loadCart();
}

loadCart();
