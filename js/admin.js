// Panou admin pentru varianta HTML + localStorage.
document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("add-product-form")
    .addEventListener("submit", addProduct);

  loadAdmin();
  loadUsers();
});


// 🛒 PRODUSE
function loadAdmin(){
  const products = DB.getProducts();
  const container = document.getElementById("admin-products");

  if(!container) return;

  container.innerHTML = "";

  if(products.length === 0){
    container.innerHTML = "<p>Nu există produse</p>";
    return;
  }

  products.forEach(p => {
    container.innerHTML += `
      <div class="card fade-in">
        <img src="${p.image}" style="max-height:120px">
        <h3>${p.name}</h3>
        <p>${p.price} RON</p>

        <button onclick="deleteProduct(${p.id})">
          Șterge produs
        </button>
      </div>
    `;
  });
}

// ➕ ADAUGĂ PRODUS
function addProduct(e){
  e.preventDefault();

  // Creeaza un produs nou si il salveaza in localStorage.
  let products = DB.getProducts();

  const newProduct = {
    id: Date.now(),
    name: document.getElementById("name").value,
    price: Number(document.getElementById("price").value),
    grams: Number(document.getElementById("grams").value),
    image: document.getElementById("image").value,
    description: document.getElementById("description").value
  };

  products.push(newProduct);
  DB.saveProducts(products);

  document.getElementById("add-product-form").reset();

  loadAdmin();
}

// 🗑 ȘTERGE PRODUS
function deleteProduct(id){
  let products = DB.getProducts().filter(p => p.id !== id);
  DB.saveProducts(products);

  loadAdmin();
}


// 👤 UTILIZATORI
function loadUsers(){
  // Afiseaza utilizatorii salvati local, util pentru testarea proiectului.
  let users = DB.getUsers();
  let container = document.getElementById("users-list");

  if(!container) return;

  container.innerHTML = "";

  if(users.length === 0){
    container.innerHTML = "<p>Nu există utilizatori</p>";
    return;
  }

  users.forEach((u, i) => {
    container.innerHTML += `
      <div class="card fade-in">
        <h3>👤 ${u.username}</h3>
        <p>💰 ${u.money} RON</p>
        <p>🛒 ${u.cart.length} produse în coș</p>

        <button onclick="deleteUser(${i})">
          Șterge utilizator
        </button>
      </div>
    `;
  });
}

// 🗑 ȘTERGE USER
function deleteUser(index){
  let users = DB.getUsers();
  let current = DB.getCurrentUser();

  let deleted = users[index];

  users.splice(index, 1);
  DB.saveUsers(users);

  if(current && current.username === deleted.username){
    DB.logout();
    window.location.href = "auth.html";
  }

  loadUsers();
}

function loadStats(){
  // Calculeaza statistici simple din datele locale.
  let users = DB.getUsers();
  let container = document.getElementById("stats");

  if(!container) return;

  let totalUsers = users.length;
  let totalMoney = users.reduce((sum,u)=>sum+u.money,0);
  let totalOrders = users.reduce((sum,u)=>sum+(u.orders?u.orders.length:0),0);

  container.innerHTML = `
    <div class="card">
      <p>👤 Utilizatori: ${totalUsers}</p>
      <p>💰 Bani total în sistem: ${totalMoney} RON</p>
      <p>🧾 Comenzi totale: ${totalOrders}</p>
    </div>
  `;
}

document.addEventListener("DOMContentLoaded", loadStats);
