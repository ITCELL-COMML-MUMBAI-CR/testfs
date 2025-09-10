<?php
/**
 * NewsModel - Manages news and announcements for SAMPARK system
 * Handles system news, updates, and public announcements
 */

require_once 'BaseModel.php';

class NewsModel extends BaseModel {
    
    protected $table = 'news';
    protected $fillable = [
        'title',
        'content',
        'summary',
        'type',
        'status',
        'published_at',
        'expires_at',
        'featured',
        'image_url',
        'author_id',
        'view_count',
        'tags'
    ];
    
    // News types
    const TYPE_NEWS = 'news';
    const TYPE_UPDATE = 'update';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_ALERT = 'alert';
    
    // News status
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_SCHEDULED = 'scheduled';
    
    /**
     * Get published news articles
     */
    public function getPublishedNews($limit = 10, $type = null) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'published' 
                AND (published_at IS NULL OR published_at <= NOW())
                AND (expires_at IS NULL OR expires_at > NOW())";
        
        $params = [];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY featured DESC, published_at DESC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get featured news
     */
    public function getFeaturedNews($limit = 3) {
        return $this->findAll([
            'status' => self::STATUS_PUBLISHED,
            'featured' => 1
        ], 'published_at DESC', $limit);
    }
    
    /**
     * Get latest news by type
     */
    public function getNewsByType($type, $limit = 5) {
        return $this->getPublishedNews($limit, $type);
    }
    
    /**
     * Get recent announcements
     */
    public function getRecentAnnouncements($limit = 5) {
        return $this->getNewsByType(self::TYPE_ANNOUNCEMENT, $limit);
    }
    
    /**
     * Get maintenance alerts
     */
    public function getMaintenanceAlerts() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE type = ? AND status = 'published'
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY published_at DESC";
        
        return $this->db->fetchAll($sql, [self::TYPE_MAINTENANCE]);
    }
    
    /**
     * Search news articles
     */
    public function searchNews($query, $type = null, $limit = 20) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'published'
                AND (title LIKE ? OR content LIKE ? OR summary LIKE ? OR tags LIKE ?)";
        
        $searchTerm = '%' . $query . '%';
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY published_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get news article with view count increment
     */
    public function getNewsWithView($id) {
        // Increment view count
        $this->db->query(
            "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?",
            [$id]
        );
        
        // Return the article
        return $this->find($id);
    }
    
    /**
     * Get popular news (by view count)
     */
    public function getPopularNews($limit = 10, $days = 30) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'published'
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY view_count DESC, published_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$days, $limit]);
    }
    
    /**
     * Get related news articles
     */
    public function getRelatedNews($id, $limit = 3) {
        $article = $this->find($id);
        if (!$article) return [];
        
        // Get articles with similar tags or same type
        $sql = "SELECT * FROM {$this->table} 
                WHERE id != ? AND status = 'published'
                AND (type = ? OR tags LIKE ?)
                ORDER BY published_at DESC
                LIMIT ?";
        
        $tagSearch = '%' . ($article['tags'] ?? '') . '%';
        
        return $this->db->fetchAll($sql, [
            $id, 
            $article['type'], 
            $tagSearch, 
            $limit
        ]);
    }
    
    /**
     * Create news article
     */
    public function createNews($data) {
        // Set defaults
        $data['status'] = $data['status'] ?? self::STATUS_DRAFT;
        $data['type'] = $data['type'] ?? self::TYPE_NEWS;
        $data['featured'] = $data['featured'] ?? 0;
        $data['view_count'] = 0;
        
        // Auto-publish if status is published and no published_at is set
        if ($data['status'] === self::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->create($data);
    }
    
    /**
     * Publish news article
     */
    public function publishNews($id, $publishAt = null) {
        $data = [
            'status' => self::STATUS_PUBLISHED,
            'published_at' => $publishAt ?? date('Y-m-d H:i:s')
        ];
        
        return $this->update($id, $data);
    }
    
    /**
     * Archive news article
     */
    public function archiveNews($id) {
        return $this->update($id, ['status' => self::STATUS_ARCHIVED]);
    }
    
    /**
     * Toggle featured status
     */
    public function toggleFeatured($id) {
        $article = $this->find($id);
        if ($article) {
            $newStatus = $article['featured'] ? 0 : 1;
            return $this->update($id, ['featured' => $newStatus]);
        }
        return false;
    }
    
    /**
     * Get news statistics
     */
    public function getNewsStats($days = 30) {
        $sql = "SELECT 
                    COUNT(*) as total_articles,
                    COUNT(CASE WHEN status = 'published' THEN 1 END) as published,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as drafts,
                    COUNT(CASE WHEN featured = 1 THEN 1 END) as featured,
                    AVG(view_count) as avg_views,
                    SUM(view_count) as total_views
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $this->db->fetch($sql, [$days]) ?: [];
    }
    
    /**
     * Get news by author
     */
    public function getNewsByAuthor($authorId, $limit = 20) {
        return $this->findAll(['author_id' => $authorId], 'created_at DESC', $limit);
    }
    
    /**
     * Get scheduled news
     */
    public function getScheduledNews() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'scheduled' 
                AND published_at <= NOW()
                ORDER BY published_at ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Process scheduled publications (cron job function)
     */
    public function processScheduledPublications() {
        $scheduledNews = $this->getScheduledNews();
        $published = 0;
        
        foreach ($scheduledNews as $article) {
            if ($this->update($article['id'], ['status' => self::STATUS_PUBLISHED])) {
                $published++;
            }
        }
        
        return $published;
    }
    
    /**
     * Clean up expired news
     */
    public function cleanupExpiredNews() {
        $sql = "UPDATE {$this->table} 
                SET status = 'archived' 
                WHERE status = 'published' 
                AND expires_at IS NOT NULL 
                AND expires_at < NOW()";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get news tags
     */
    public function getAllTags() {
        $sql = "SELECT tags FROM {$this->table} WHERE tags IS NOT NULL AND tags != ''";
        $results = $this->db->fetchAll($sql);
        
        $allTags = [];
        foreach ($results as $row) {
            $tags = explode(',', $row['tags']);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $allTags[$tag] = ($allTags[$tag] ?? 0) + 1;
                }
            }
        }
        
        // Sort by usage count
        arsort($allTags);
        
        return $allTags;
    }
    
    /**
     * Get news by tag
     */
    public function getNewsByTag($tag, $limit = 20) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'published' 
                AND tags LIKE ?
                ORDER BY published_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, ['%' . $tag . '%', $limit]);
    }
    
    /**
     * Validate news data
     */
    public function validateNews($data, $id = null) {
        $errors = [];
        
        // Required fields
        $required = ['title', 'content', 'type'];
        $errors = array_merge($errors, $this->validateRequired($data, $required));
        
        // Validate type
        $validTypes = [self::TYPE_NEWS, self::TYPE_UPDATE, self::TYPE_ANNOUNCEMENT, 
                      self::TYPE_MAINTENANCE, self::TYPE_ALERT];
        if (!empty($data['type']) && !in_array($data['type'], $validTypes)) {
            $errors[] = "Invalid news type";
        }
        
        // Validate status
        $validStatuses = [self::STATUS_DRAFT, self::STATUS_PUBLISHED, 
                         self::STATUS_ARCHIVED, self::STATUS_SCHEDULED];
        if (!empty($data['status']) && !in_array($data['status'], $validStatuses)) {
            $errors[] = "Invalid news status";
        }
        
        // Validate dates
        if (!empty($data['published_at']) && !strtotime($data['published_at'])) {
            $errors[] = "Invalid published date format";
        }
        
        if (!empty($data['expires_at']) && !strtotime($data['expires_at'])) {
            $errors[] = "Invalid expiry date format";
        }
        
        // Check title uniqueness
        if (!empty($data['title'])) {
            $conditions = ['title' => $data['title']];
            if ($id) {
                $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE title = ? AND id != ?";
                $result = $this->db->fetch($sql, [$data['title'], $id]);
            } else {
                $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table} WHERE title = ?", [$data['title']]);
            }
            
            if ($result && $result['count'] > 0) {
                $errors[] = "News title already exists";
            }
        }
        
        return $errors;
    }
}