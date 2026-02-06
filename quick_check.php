<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$db = $app->make('db');

echo "Checking database data...\n\n";

$types = $db->table('types')->where('status', 1)->count();
$materials = $db->table('materials')->where('status', 1)->count();

echo "Active types: $types\n";
echo "Active materials: $materials\n\n";

if ($types == 0) {
    echo "⚠️  PROBLEM: No active types found!\n";
    echo "   Main page needs types with status=1 to display content.\n";
    echo "   Checking all types...\n";
    $allTypes = $db->table('types')->select('id', 'name', 'slug', 'status')->get();
    foreach ($allTypes as $t) {
        echo "   - {$t->name} (slug: {$t->slug}, status: " . ($t->status ? '1' : '0') . ")\n";
    }
}

if ($materials == 0) {
    echo "\n⚠️  PROBLEM: No active materials found!\n";
    echo "   Main page needs materials with status=1 to display content.\n";
}

if ($types > 0 && $materials > 0) {
    echo "✓ Database has data. The issue might be elsewhere.\n";
    echo "   Check browser console for JavaScript errors.\n";
}

