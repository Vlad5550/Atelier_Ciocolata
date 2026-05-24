// Varianta JavaScript foloseste localStorage ca o baza de date locala in browser.
const DB = {
  // 👤 USERS
  getUsers: () => JSON.parse(localStorage.getItem("users")) || [],
  saveUsers: (u) => localStorage.setItem("users", JSON.stringify(u)),

  // 👤 CURRENT USER
  getCurrentUser: () => JSON.parse(localStorage.getItem("currentUser")),
  setCurrentUser: (u) => localStorage.setItem("currentUser", JSON.stringify(u)),

  // 🛒 PRODUCTS 
  getProducts: () => JSON.parse(localStorage.getItem("products")) || [],
  saveProducts: (p) => localStorage.setItem("products", JSON.stringify(p)),

  // 🔄 SYNC USER 
  syncCurrentUser: () => {
    // Sincronizeaza utilizatorul curent cu ultima versiune salvata in lista de useri.
    let users = DB.getUsers();
    let current = DB.getCurrentUser();

    if (!current) return;

    let updated = users.find(u => u.username === current.username);

    if (updated) {
      DB.setCurrentUser(updated);
    }
  }
};
