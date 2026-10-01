<?php
require_once __DIR__ . '/../config/db.php';

$res = mysqli_query($conn, "SELECT id, title, category, author, description FROM policy_records WHERE id IN (101, 107) ORDER BY id ASC");
while ($r = mysqli_fetch_assoc($res)) {
    echo "ID {$r['id']}: [{$r['category']}] {$r['title']}\n";
    echo "   Author: {$r['author']}\n";
    echo "   Desc: {$r['description']}\n\n";
}
