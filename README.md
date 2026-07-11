# 🍫 Atelier Ciocolata

Platforma web dinamica pentru un magazin online de ciocolata artizanala, realizata ca **proiect de licenta**. Aplicatia acopera un flux e-commerce complet: autentificare, catalog de produse, cos de cumparaturi, plasare de comenzi si un panou de administrare cu export XML pentru un sistem ERP.

## 🧩 Tehnologii

- **PHP** (server-side, PDO pentru MySQL)
- **MySQL / MariaDB** (baza de date `ciocolata`)
- **JavaScript** (interactiuni in pagini)
- **HTML5 / CSS3** (interfata)
- **XML + XSL** (export si afisare date pentru ERP)

## ✨ Functionalitati

- 🔐 **Autentificare** — inregistrare si login cu parole salvate hash-uit (`password_hash` / `password_verify`), sesiuni PHP.
- 🛍️ **Magazin** — catalog de produse cu pret, stoc, descriere si imagine.
- 🛒 **Cos de cumparaturi** — adaugare/eliminare produse, calcul total.
- 📦 **Comenzi** — plasarea comenzii scade din soldul utilizatorului si se salveaza tranzactional (`orders` + `order_items`).
- 💰 **Portofel** — fiecare cont porneste cu un sold de `500.00 RON`.
- ⚙️ **Panou admin** — administrare produse (protejat prin rol `admin`).
- 📄 **Export ERP/XML** — exporta produsele si comenzile ca documente XML afisate cu XSL (`export_products_xml.php`, `export_orders_xml.php`).

## 🗄️ Structura bazei de date

Definitia completa se afla in [`schema.sql`](schema.sql). Tabele principale:

| Tabel | Rol |
|-------|-----|
| `users` | Conturi utilizatori (`username`, `password`, `money`, `role`) |
| `products` | Catalogul de produse (`name`, `description`, `price`, `stock`, `image`) |
| `orders` | Comenzi plasate (`user_id`, `total`, `status`) |
| `order_items` | Liniile fiecarei comenzi (`order_id`, `product_id`, `quantity`, `price`) |

## 📁 Structura proiectului

```
Aterier_Ciocolata/
├── index.php               # Pagina principala
├── auth.php                # Login / Register
├── logout.php              # Delogare
├── shop.php / shop.html    # Magazin (catalog produse)
├── cart.php / cart.html    # Cos de cumparaturi
├── orders.php / orders.html# Istoric comenzi
├── admin.php               # Panou administrare produse
├── erp.php                 # Pagina de export ERP
├── export_products_xml.php # Export XML produse
├── export_orders_xml.php   # Export XML comenzi
├── security.php            # Verificari legate de securitate
├── php/
│   ├── db.php              # Conexiunea PDO la MySQL
│   └── helpers.php         # Sesiuni, escaping, verificari rol/login
├── js/                     # Scripturi front-end
├── css/style.css           # Stilizare
├── xml/                    # Foi de stil XSL (products.xsl, orders.xsl)
├── schema.sql              # Definitia bazei de date
└── SECURITY_TESTS.md       # Metodologie de testare securitate
```

## 🚀 Instalare si rulare

### Cerinte
- PHP 7.4+ (recomandat 8.x)
- MySQL / MariaDB
- Optional: XAMPP (mediu recomandat pe Windows)

### Pasi

1. **Cloneaza** proiectul in directorul de web (ex. `C:\xampp\htdocs\` sau folderul de lucru).

2. **Creeaza baza de date** din schema:
   ```bash
   mysql -u root -p < schema.sql
   ```

3. **Configureaza conexiunea** in [`php/db.php`](php/db.php) daca datele difera de setarile implicite:
   ```php
   $host   = "localhost";
   $dbname = "ciocolata";
   $user   = "root";
   $pass   = "";
   ```

4. **Porneste aplicatia**:
   - cu XAMPP: acceseaza `http://localhost/Aterier_Ciocolata/`
   - sau cu serverul integrat PHP:
     ```bash
     php -S 127.0.0.1:8000
     ```
     apoi deschide `http://127.0.0.1:8000/`

5. **Creeaza un cont** din pagina Login / Register. Pentru acces la panoul admin si exporturile XML, ridica rolul din baza de date:
   ```sql
   UPDATE users SET role = 'admin' WHERE username = 'numele_tau';
   ```

## 🔒 Securitate

Masuri implementate (detalii in [`SECURITY_TESTS.md`](SECURITY_TESTS.md)):

- Parole stocate cu `password_hash()` si verificate cu `password_verify()`.
- Interogari SQL prin **prepared statements** (PDO).
- Iesire HTML protejata cu `htmlspecialchars()` (helper-ul `e()`).
- Pagini protejate prin `requireLogin()`.
- Panoul admin si exporturile XML verifica rolul `admin`.
- Comenzile sunt salvate in **tranzactie SQL**.

Testare efectuata cu OWASP ZAP, Burp Suite Community si `php -l` (lint).

## 📝 Note

- Fisierul de sesiuni (`php/sessions/`) este ignorat de Git (vezi `.gitignore`).
- `schema.sql` a fost reconstruit din interogarile aplicatiei.
