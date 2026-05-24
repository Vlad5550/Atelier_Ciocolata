# Testare securitate

Aplicatia foloseste baza de date `ciocolata`, sesiuni PHP si interogari PDO.

## Metode implementate

- Parole salvate cu `password_hash()`.
- Login verificat cu `password_verify()`.
- Interogari SQL cu prepared statements.
- Afisare protejata cu `htmlspecialchars()`.
- Paginile protejate folosesc `requireLogin()`.
- Exporturile ERP/XML si panoul admin folosesc verificarea rolului `admin`.
- Comenzile sunt salvate in tranzactie SQL.

## Programe specifice

1. OWASP ZAP
   - URL testat: `http://127.0.0.1:8000/`
   - Se ruleaza spider pentru descoperirea paginilor.
   - Se ruleaza active scan pentru formulare si parametri.

2. Burp Suite Community
   - Browserul este configurat prin proxy-ul Burp.
   - Se verifica cererile de login, register, add to cart si checkout.
   - Se confirma ca paginile admin si exporturile XML refuza accesul fara rol admin.

3. PHP lint
   - Comanda: `C:\xampp\php\php.exe -l nume_fisier.php`
   - Scop: identificarea erorilor de sintaxa PHP.
