<?php
/**
 * Path Configuration Test
 * Tests the dynamic path detection system
 */

require_once 'src/config/Config.php';

echo "<h1>SAMPARK Path Configuration Test</h1>\n";
echo "<h2>Dynamic Path Detection Results:</h2>\n";

echo "<table border='1' cellpadding='5'>\n";
echo "<tr><th>Property</th><th>Value</th><th>Method</th></tr>\n";

echo "<tr>";
echo "<td>APP_URL</td>";
echo "<td>" . htmlspecialchars(Config::getAppUrl()) . "</td>";
echo "<td>Config::getAppUrl()</td>";
echo "</tr>\n";

echo "<tr>";
echo "<td>BASE_PATH</td>";
echo "<td>" . htmlspecialchars(Config::getBasePath()) . "</td>";
echo "<td>Config::getBasePath()</td>";
echo "</tr>\n";

echo "<tr>";
echo "<td>Upload Path (file system)</td>";
echo "<td>" . htmlspecialchars(Config::getUploadPath()) . "</td>";
echo "<td>Config::getUploadPath()</td>";
echo "</tr>\n";

echo "<tr>";
echo "<td>Upload Path (public URL)</td>";
echo "<td>" . htmlspecialchars(Config::getPublicUploadPath()) . "</td>";
echo "<td>Config::getPublicUploadPath()</td>";
echo "</tr>\n";

echo "</table>\n";

echo "<h2>Server Environment:</h2>\n";
echo "<table border='1' cellpadding='5'>\n";
echo "<tr><th>Variable</th><th>Value</th></tr>\n";

$serverVars = ['HTTP_HOST', 'SCRIPT_NAME', 'REQUEST_URI', 'DOCUMENT_ROOT', 'HTTPS'];
foreach ($serverVars as $var) {
    echo "<tr>";
    echo "<td>$var</td>";
    echo "<td>" . htmlspecialchars($_SERVER[$var] ?? 'Not set') . "</td>";
    echo "</tr>\n";
}

echo "</table>\n";

echo "<h2>Instructions:</h2>\n";
echo "<p><strong>For localhost (XAMPP):</strong> Keep the htaccess RewriteBase as <code>/testfs/public/</code></p>\n";
echo "<p><strong>For production server:</strong> Change htaccess RewriteBase to <code>/</code> or comment it out</p>\n";

echo "<h3>To switch between localhost and production:</h3>\n";
echo "<ol>\n";
echo "<li>Edit <code>public/.htaccess</code></li>\n";
echo "<li>For localhost: <code>RewriteBase /testfs/public/</code></li>\n";
echo "<li>For production: <code># RewriteBase /testfs/public/</code> (commented) and <code>RewriteBase /</code> (uncommented)</li>\n";
echo "</ol>\n";

?>