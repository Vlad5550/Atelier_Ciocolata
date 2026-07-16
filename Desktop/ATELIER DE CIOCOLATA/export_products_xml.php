<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

date_default_timezone_set("Europe/Bucharest");

// Exportul XML este permis doar utilizatorilor autentificati cu rol de admin.
requireLogin();

if (!isAdmin($pdo)) {
    http_response_code(403);
    header("Content-Type: text/plain; charset=utf-8");
    echo "Acces permis doar pentru admin.";
    exit;
}

function addTextNode(DOMDocument $doc, DOMElement $parent, string $name, $value): void
{
    // Functie ajutatoare pentru noduri XML de forma <nume>valoare</nume>.
    $node = $doc->createElement($name);
    $node->appendChild($doc->createTextNode((string) $value));
    $parent->appendChild($node);
}

// Toate produsele sunt exportate in ordine crescatoare dupa id.
$products = $pdo
    ->query("SELECT id, name, description, price, image, stock, created_at FROM products ORDER BY id ASC")
    ->fetchAll();

$doc = new DOMDocument("1.0", "UTF-8");
$doc->formatOutput = true;

$stylesheet = $doc->createProcessingInstruction(
    "xml-stylesheet",
    'type="text/xsl" href="xml/products.xsl"'
);
$doc->appendChild($stylesheet);

$root = $doc->createElement("products");
$root->setAttribute("generated_at", date("Y-m-d H:i:s"));
$doc->appendChild($root);

foreach ($products as $product) {
    $productNode = $doc->createElement("product");
    $productNode->setAttribute("id", (string) $product["id"]);

    foreach (["name", "description", "price", "image", "stock", "created_at"] as $field) {
        addTextNode($doc, $productNode, $field, $product[$field] ?? "");
    }

    $root->appendChild($productNode);
}

header("Content-Type: application/xml; charset=utf-8");
echo $doc->saveXML();
