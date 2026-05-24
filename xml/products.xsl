<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" encoding="UTF-8"/>

  <!-- XSL transforma XML-ul de produse intr-un tabel HTML pentru ERP. -->
  <xsl:template match="/">
    <html lang="ro">
      <head>
        <meta charset="UTF-8"/>
        <title>🍫 Produse XML</title>
        <style>
          body{font-family:Segoe UI,Arial,sans-serif;margin:24px;background:#f8f6fb;color:#1d1026}
          table{width:100%;border-collapse:collapse;background:white}
          th,td{padding:10px;border:1px solid #d8cfe3;text-align:left}
          th{background:#2b0040;color:white}
          h1{margin-bottom:6px}
          p{color:#594668}
        </style>
      </head>
      <body>
        <h1>🍫 Catalog produse</h1>
        <p>🕒 Generat la: <xsl:value-of select="products/@generated_at"/></p>

        <table>
          <thead>
            <tr>
              <th>🔢 ID</th>
              <th>🍫 Nume</th>
              <th>✨ Descriere</th>
              <th>💰 Pret</th>
              <th>📦 Stoc</th>
            </tr>
          </thead>
          <tbody>
            <xsl:for-each select="products/product">
              <!-- Fiecare produs din XML devine un rand in tabel. -->
              <tr>
                <td><xsl:value-of select="@id"/></td>
                <td><xsl:value-of select="name"/></td>
                <td><xsl:value-of select="description"/></td>
                <td><xsl:value-of select="price"/> RON</td>
                <td><xsl:value-of select="stock"/></td>
              </tr>
            </xsl:for-each>
          </tbody>
        </table>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
