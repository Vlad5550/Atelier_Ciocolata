// Initializare pentru varianta HTML: adauga produse demo in localStorage.
if(!localStorage.getItem("products")){
  const defaultProducts = [
    {
      id: 1,
      name: "Ciocolată Premium",
      price: 25,
      grams: 100,
      image: "https://via.placeholder.com/200",
      description: "100% Cacao Alcalină"
    }
  ];

  localStorage.setItem("products", JSON.stringify(defaultProducts));
}
