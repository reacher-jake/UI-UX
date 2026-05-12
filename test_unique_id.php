<?php
function unique_id() {
    return bin2hex(random_bytes(10));
}

// Generate 10 IDs
$ids = [];
for ($i = 0; $i < 10; $i++) {
    $id = unique_id();
    $ids[] = $id;
    echo "ID $i: $id (Length: " . strlen($id) . ")\n";
}

// Check for duplicates
$unique_ids = array_unique($ids);
if (count($unique_ids) === count($ids)) {
    echo "All IDs are unique!\n";
} else {
    echo "Duplicates found!\n";
}
?>