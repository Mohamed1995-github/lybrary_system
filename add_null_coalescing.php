<?php
// Read the current file
$file_path = 'modules/items/list.php';
$content = file_get_contents($file_path);

// Add null coalescing operators
$content = str_replace("\$row['available_copies']", "\$row['available_copies'] ?? 0", $content);
$content = str_replace("\$row['copies']", "\$row['copies'] ?? 0", $content);

// Write the fixed content back
file_put_contents($file_path, $content);

echo "Added null coalescing operators to prevent warnings!\n";
?>
