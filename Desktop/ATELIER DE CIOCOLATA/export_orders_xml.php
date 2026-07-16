<?php
require_once __DIR__ . "/php/db.php";
require_once __DIR__ . "/php/helpers.php";

date_default_timezone_set("Europe/Bucharest");

// Exportul comenzilor poate fi accesat doar de admin.
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

// Citeste comenzile impreuna cu username-ul clientului.
$orders = $pdo
    ->query(
        "SELECT o.id, o.total, o.status, o.created_at, u.id AS user_id, u.username
         FROM orders o
         JOIN users u ON u.id = o.user_id
         ORDER BY o.created_at DESC"
    )
    ->fetchAll();

$itemsByOrder = [];

if ($orders) {
    $orderIds = array_column($orders, "id");
    $placeholders = implode(",", array_fill(0, count($orderIds), "?"));

    // Citeste produsele din toate comenzile si le grupeaza dupa comanda.
    $stmt = $pdo->prepare(
        "SELECT oi.order_id, oi.product_id, oi.quantity, oi.price, p.name
         FROM order_items oi
         JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id IN ($placeholders)
         ORDER BY oi.id ASC"
    );
    $stmt->execute($orderIds);

    foreach ($stmt->fetchAll() as $item) {
        $itemsByOrder[(int) $item["order_id"]][] = $item;
    }
}

$doc = new DOMDocument("1.0", "UTF-8");
$doc->formatOutput = true;

// Versionarea XSL evita afisarea veche pastrata in cache de browser.
$xslVersion = (string) filemtime(__DIR__ . "/xml/orders.xsl");
$stylesheet = $doc->createProcessingInstruction(
    "xml-stylesheet",
    'type="text/xsl" href="xml/orders.xsl?v=' . $xslVersion . '"'
);
$doc->appendChild($stylesheet);

$root = $doc->createElement("orders");
$root->setAttribute("generated_at", date("Y-m-d H:i:s"));
$doc->appendChild($root);

foreach ($orders as $order) {
    // Fiecare comanda devine un nod <order> in XML.
    $orderNode = $doc->createElement("order");
    $orderNode->setAttribute("id", (string) $order["id"]);

    $customerNode = $doc->createElement("customer");
    $customerNode->setAttribute("id", (string) $order["user_id"]);

    $username = (string) $order["username"];
    // Doar NitaVlad este admin in export. Toti ceilalti sunt clienti.
    $role = $username === "NitaVlad" ? "admin" : "client";
    $roleLabel = $role === "admin" ? "Admin" : "Client";

    addTextNode($doc, $customerNode, "username", $username);
    addTextNode($doc, $customerNode, "role", $role);
    addTextNode($doc, $customerNode, "role_label", $roleLabel);

    $orderNode->appendChild($customerNode);

    foreach (["total", "status", "created_at"] as $field) {
        addTextNode($doc, $orderNode, $field, $order[$field]);
    }

    $itemsNode = $doc->createElement("items");

    foreach ($itemsByOrder[(int) $order["id"]] ?? [] as $item) {
        $itemNode = $doc->createElement("item");
        $itemNode->setAttribute("product_id", (string) $item["product_id"]);

        $lineTotal = (float) $item["price"] * (int) $item["quantity"];

        addTextNode($doc, $itemNode, "name", $item["name"]);
        addTextNode($doc, $itemNode, "quantity", $item["quantity"]);
        addTextNode($doc, $itemNode, "price", $item["price"]);
        addTextNode($doc, $itemNode, "line_total", number_format($lineTotal, 2, ".", ""));

        $itemsNode->appendChild($itemNode);
    }

    $orderNode->appendChild($itemsNode);
    $root->appendChild($orderNode);
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Content-Type: application/xml; charset=utf-8");
echo $doc->saveXML();
