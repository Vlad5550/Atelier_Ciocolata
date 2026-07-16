// Afiseaza produsele salvate in localStorage pentru varianta HTML.
function loadProducts(){
  const products = DB.getProducts();
  const container = document.getElementById("product-list");

  container.innerHTML = "";

  products.forEach(p => {
    container.innerHTML += `
      <div class="card fade-in">
        <img src="${p.image}">
        <h3>${p.name}</h3>
        <p>${p.description}</p>
        <p>${p.grams}g</p>
        <b>${p.price} RON</b>

        <button onclick="addToCart(${p.id})">
          Adaugă în coș
        </button>
      </div>
    `;
  });
}

function addToCart(id){
  // Cauta produsul dupa id si il adauga in cosul utilizatorului curent.
  const user = DB.getCurrentUser();
  if(!user) return window.location.href = "auth.html";

  let users = DB.getUsers();
  let products = DB.getProducts();

  let u = users.find(x => x.username === user.username);
  let p = products.find(x => x.id === id);

  u.cart = u.cart || [];

  let existing = u.cart.find(x => x.id === id);

  if(existing){
    existing.qty++;
  } else {
    u.cart.push({
      id: p.id,
      name: p.name,
      price: p.price,
      image: p.image,
      qty: 1
    });
  }

  DB.saveUsers(users);
  DB.setCurrentUser(u);

  alert("Adaugat in cos");
}

loadProducts();
