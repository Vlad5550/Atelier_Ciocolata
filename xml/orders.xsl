<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" encoding="UTF-8"/>

  <!-- XSL transforma XML-ul de comenzi intr-o pagina HTML usor de citit. -->
  <xsl:template match="/">
    <html lang="ro">
      <head>
        <meta charset="UTF-8"/>
        <title>📦 Comenzi XML</title>
        <style>
          body{font-family:Segoe UI,Arial,sans-serif;margin:24px;background:#f8f6fb;color:#1d1026}
          table{width:100%;border-collapse:collapse;background:white;margin-bottom:24px}
          th,td{padding:10px;border:1px solid #d8cfe3;text-align:left}
          th{background:#2b0040;color:white}
          h1{margin-bottom:6px}
          h2{margin-top:24px}
          p{color:#594668}
        </style>
      </head>
      <body>
        <h1>📦 Export comenzi ERP</h1>
        <p>🕒 Generat la: <xsl:value-of select="orders/@generated_at"/></p>

        <!-- Se parcurge fiecare nod <order> generat de PHP. -->
        <xsl:for-each select="orders/order">
          <h2>📦 Comanda #<xsl:value-of select="@id"/></h2>
          <p>
            👤
            <xsl:value-of select="customer/role_label"/>:
            <xsl:value-of select="customer/username"/>
            | Tip:
            <xsl:value-of select="customer/role"/>
            | 📌 Status:
            <xsl:value-of select="status"/>
            | 💰 Total:
            <xsl:value-of select="total"/> RON
          </p>

          <table>
            <thead>
              <tr>
                <th>🍫 Produs</th>
                <th>🔢 Cantitate</th>
                <th>💰 Pret</th>
                <th>🧾 Total linie</th>
              </tr>
            </thead>
            <tbody>
              <xsl:for-each select="items/item">
                <!-- Fiecare produs comandat devine un rand in tabel. -->
                <tr>
                  <td><xsl:value-of select="name"/></td>
                  <td><xsl:value-of select="quantity"/></td>
                  <td><xsl:value-of select="price"/> RON</td>
                  <td><xsl:value-of select="line_total"/> RON</td>
                </tr>
              </xsl:for-each>
            </tbody>
          </table>
        </xsl:for-each>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
