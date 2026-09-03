<?php
$dir = 'resources/views/layouts/';
foreach(glob($dir.'*.blade.php') as $file) {
    $content = file_get_contents($file);
    if(strpos($content, '<x-system-announcement />') === false) {
        $search = '<div class="flex h-screen overflow-hidden">';
        $replace = "<div class=\"flex flex-col h-screen overflow-hidden\">\n        <x-system-announcement />\n        <div class=\"flex flex-1 overflow-hidden\">";
        
        $content = str_replace($search, $replace, $content);
        
        // Also need to close the extra div at the end of the body
        $search2 = "</body>";
        $replace2 = "    </div>\n</body>";
        $content = str_replace($search2, $replace2, $content);
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
