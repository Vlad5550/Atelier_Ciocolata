// Acest fisier controleaza autentificarea in varianta HTML + localStorage.
function getUsers() {
  return DB.getUsers();
}

function saveUsers(users) {
  DB.saveUsers(users);
}

function setCurrentUser(user) {
  DB.setCurrentUser(user);
}

function getCurrentUser() {
  return DB.getCurrentUser();
}

// 🔐 PROTECȚIE
function requireAuth() {
  if (!getCurrentUser()) {
    window.location.href = "auth.html";
  }
}

// 🔄 SWITCH UI
function toggleMode() {
  const login = document.getElementById("loginForm");
  const register = document.getElementById("registerForm");
  const text = document.getElementById("modeText");

  const isLogin = login.style.display !== "none";

  login.style.display = isLogin ? "none" : "block";
  register.style.display = isLogin ? "block" : "none";

  if (text) text.innerText = isLogin ? "Register" : "Login";
}

// 🚀 INIT
document.addEventListener("DOMContentLoaded", () => {

  const registerForm = document.getElementById("registerForm");
  const loginForm = document.getElementById("loginForm");

  // 🧾 REGISTER
  if (registerForm) {
    registerForm.addEventListener("submit", (e) => {
      e.preventDefault();

      // Creeaza un utilizator nou direct in localStorage.
      let users = getUsers();

      const username = document.getElementById("regUser").value.trim();
      const password = document.getElementById("regPass").value.trim();

      if (!username || !password) return alert("Completeaza toate campurile");

      if (users.find(u => u.username === username)) return alert("User existent");

      const newUser = {
        username,
        password,
        money: 500,
        cart: [],
        orders: [] 
      };

      users.push(newUser);
      saveUsers(users);

      setCurrentUser(newUser);

      alert("Register successful!");
      window.location.href = "shop.html";
    });
  }

  // 🔐 LOGIN
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();

      // Verifica username-ul si parola in lista salvata local.
      const username = document.getElementById("loginUser").value.trim();
      const password = document.getElementById("loginPass").value.trim();

      const user = getUsers().find(u =>
        u.username === username && u.password === password
      );

      if (!user) return alert("Date gresite");

      setCurrentUser(user);

      alert("Login successful!");
      window.location.href = "shop.html";
    });
  }
});
// 💰 ADAUGĂ BANI
function addMoney(amount){
  // Functie de test pentru alimentarea contului in varianta localStorage.
  let users = DB.getUsers();
  let current = DB.getCurrentUser();

  if(!current) return;

  let user = users.find(u => u.username === current.username);

  if(!user) return;

  user.money += Number(amount);

  DB.saveUsers(users);
  DB.setCurrentUser(user);

  alert("💰 Ai primit " + amount + " RON!");
}
