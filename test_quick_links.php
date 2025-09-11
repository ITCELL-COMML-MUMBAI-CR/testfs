<?php
/**
 * Test script to check if quick links are loading properly
 * This helps debug why quick links might not appear on the home page
 */

require_once 'src/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<h2>SAMPARK Home Page Content Test</h2>";
    
    // Check if quick_links table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'quick_links'");
    $quickLinksExists = $stmt->rowCount() > 0;
    
    // Check if news table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'news'");
    $newsExists = $stmt->rowCount() > 0;
    
    if (!$quickLinksExists) {
        echo "<p style='color: red;'>❌ quick_links table does not exist</p>";
    } else {
        echo "<p style='color: green;'>✅ quick_links table exists</p>";
    }
    
    if (!$newsExists) {
        echo "<p style='color: red;'>❌ news table does not exist</p>";
    } else {
        echo "<p style='color: green;'>✅ news table exists</p>";
    }
    
    if (!$quickLinksExists && !$newsExists) {
        echo "<p style='color: red;'>❌ Required tables do not exist</p>";
        exit;
    }
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE quick_links");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Table Structure:</h3><ul>";
    foreach ($columns as $column) {
        echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
    
    // Check for data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM quick_links");
    $count = $stmt->fetch()['count'];
    
    echo "<p><strong>Total quick links: " . $count . "</strong></p>";
    
    if ($count == 0) {
        echo "<p style='color: orange;'>⚠️ No quick links found. Run the add_quick_links.sql script to add sample data.</p>";
        echo "<p><strong>To add sample data:</strong></p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin or your MySQL client</li>";
        echo "<li>Select the SAMPARK database</li>";
        echo "<li>Run the SQL from add_quick_links.sql file</li>";
        echo "</ol>";
    } else {
        echo "<p style='color: green;'>✅ Quick links data found</p>";
        
        // Show active quick links
        $stmt = $pdo->query("SELECT title, description, url, icon, is_active FROM quick_links WHERE is_active = 1 ORDER BY sort_order ASC");
        $links = $stmt->fetchAll();
        
        echo "<h3>Active Quick Links:</h3>";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 20px 0;'>";
        
        foreach ($links as $link) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #f9f9f9;'>";
            echo "<h4 style='margin: 0 0 8px 0; color: #0066cc;'>";
            if ($link['icon']) {
                echo "<i class='" . htmlspecialchars($link['icon']) . "' style='margin-right: 8px;'></i>";
            }
            echo htmlspecialchars($link['title']) . "</h4>";
            if ($link['description']) {
                echo "<p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>" . htmlspecialchars($link['description']) . "</p>";
            }
            echo "<a href='" . htmlspecialchars($link['url']) . "' target='_blank' style='color: #0066cc; text-decoration: none; font-size: 14px;'>";
            echo "🔗 " . htmlspecialchars($link['url']) . "</a>";
            echo "</div>";
        }
        
        echo "</div>";
        
        echo "<p style='color: green;'>✅ Quick links should now appear on the home page!</p>";
    }
    
    // Check News and Announcements
    if ($newsExists) {
        echo "<h3>News and Announcements:</h3>";
        
        // Check for news data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM news WHERE is_active = 1 AND show_on_homepage = 1");
        $newsCount = $stmt->fetch()['count'];
        
        echo "<p><strong>Active news items: " . $newsCount . "</strong></p>";
        
        if ($newsCount > 0) {
            // Show recent news items
            $stmt = $pdo->query("SELECT title, short_description, type, priority, publish_date FROM news WHERE is_active = 1 AND show_on_homepage = 1 ORDER BY priority DESC, publish_date DESC LIMIT 5");
            $newsItems = $stmt->fetchAll();
            
            echo "<div style='display: grid; grid-template-columns: 1fr; gap: 10px; margin: 20px 0;'>";
            
            foreach ($newsItems as $item) {
                $typeClass = '';
                $typeIcon = '';
                switch($item['type']) {
                    case 'news': $typeClass = 'primary'; $typeIcon = '📰'; break;
                    case 'announcement': $typeClass = 'info'; $typeIcon = '📢'; break;
                    case 'alert': $typeClass = 'warning'; $typeIcon = '⚠️'; break;
                    case 'update': $typeClass = 'success'; $typeIcon = '🔄'; break;
                }
                
                $priorityClass = '';
                switch($item['priority']) {
                    case 'urgent': $priorityClass = 'background: #ff6b6b; color: white;'; break;
                    case 'high': $priorityClass = 'background: #ffa726; color: white;'; break;
                    case 'medium': $priorityClass = 'background: #42a5f5; color: white;'; break;
                    case 'low': $priorityClass = 'background: #66bb6a; color: white;'; break;
                }
                
                echo "<div style='border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #f9f9f9;'>";
                echo "<div style='display: flex; justify-content: between; align-items: center; margin-bottom: 8px;'>";
                echo "<h4 style='margin: 0; color: #0066cc;'>" . $typeIcon . " " . htmlspecialchars($item['title']) . "</h4>";
                echo "<span style='font-size: 12px; padding: 4px 8px; border-radius: 4px; " . $priorityClass . "'>" . ucfirst($item['priority']) . "</span>";
                echo "</div>";
                if ($item['short_description']) {
                    echo "<p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>" . htmlspecialchars($item['short_description']) . "</p>";
                }
                echo "<small style='color: #999;'>📅 " . date('M d, Y', strtotime($item['publish_date'])) . " | 🏷️ " . ucfirst($item['type']) . "</small>";
                echo "</div>";
            }
            
            echo "</div>";
            
            echo "<p style='color: green;'>✅ News and announcements should now appear on the home page!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No news items found. Run the add_quick_links.sql script to add sample data.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration in src/config/database.php</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
h3 { color: #555; margin-top: 25px; }
ul { background: #f5f5f5; padding: 15px; border-radius: 5px; }
li { margin: 5px 0; }
code { background: #eee; padding: 2px 5px; border-radius: 3px; }
</style>