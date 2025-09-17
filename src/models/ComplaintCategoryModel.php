<?php
/**
 * ComplaintCategoryModel - Manages complaint categories, types, and subtypes
 * Handles the hierarchical structure of complaints in SAMPARK system
 */

require_once 'BaseModel.php';

class ComplaintCategoryModel extends BaseModel {
    
    protected $table = 'complaint_categories';
    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'category_type',
        'priority_level',
        'is_active',
        'sort_order',
        'color_code',
        'icon_class'
    ];
    
    /**
     * Get all main categories (parent categories)
     */
    public function getMainCategories() {
        $sql = "SELECT * FROM {$this->table} WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order, name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get subcategories by parent category ID
     */
    public function getSubcategories($parentId) {
        $sql = "SELECT * FROM {$this->table} WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order, name";
        return $this->db->fetchAll($sql, [$parentId]);
    }
    
    /**
     * Get all categories with their subcategories in hierarchical format
     */
    public function getCategoriesHierarchy() {
        $categories = $this->getMainCategories();
        
        foreach ($categories as &$category) {
            $category['subcategories'] = $this->getSubcategories($category['id']);
        }
        
        return $categories;
    }
    
    /**
     * Get category by type (issue, damage, delay, etc.)
     */
    public function getCategoriesByType($type) {
        $sql = "SELECT * FROM {$this->table} WHERE category_type = ? AND is_active = 1 ORDER BY sort_order, name";
        return $this->db->fetchAll($sql, [$type]);
    }
    
    /**
     * Get category with its parent information
     */
    public function getCategoryWithParent($id) {
        $sql = "SELECT c.*, p.name as parent_name, p.category_type as parent_type
                FROM {$this->table} c 
                LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                WHERE c.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Get category path (breadcrumb)
     */
    public function getCategoryPath($id) {
        $path = [];
        $category = $this->find($id);
        
        while ($category) {
            array_unshift($path, $category);
            $category = $category['parent_id'] ? $this->find($category['parent_id']) : null;
        }
        
        return $path;
    }
    
    /**
     * Check if category has subcategories
     */
    public function hasSubcategories($id) {
        return $this->count(['parent_id' => $id, 'is_active' => 1]) > 0;
    }
    
    /**
     * Get category statistics (number of tickets per category)
     */
    public function getCategoryStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT c.id, c.name, c.category_type, 
                       COUNT(t.id) as ticket_count,
                       AVG(CASE WHEN t.status = 'resolved' THEN 
                           TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) 
                       END) as avg_resolution_hours
                FROM {$this->table} c 
                LEFT JOIN complaint_tickets t ON c.id = t.category_id";
        
        $params = [];
        $conditions = [];
        
        if ($dateFrom) {
            $conditions[] = "t.created_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $conditions[] = "t.created_at <= ?";
            $params[] = $dateTo;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " GROUP BY c.id, c.name, c.category_type ORDER BY ticket_count DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get most used categories
     */
    public function getPopularCategories($limit = 10) {
        $sql = "SELECT c.*, COUNT(t.id) as usage_count
                FROM {$this->table} c 
                LEFT JOIN complaint_tickets t ON c.id = t.category_id
                WHERE c.is_active = 1
                GROUP BY c.id 
                ORDER BY usage_count DESC, c.name
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Search categories by name or description
     */
    public function searchCategories($query, $categoryType = null) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 AND (name LIKE ? OR description LIKE ?)";
        
        $params = ['%' . $query . '%', '%' . $query . '%'];
        
        if ($categoryType) {
            $sql .= " AND category_type = ?";
            $params[] = $categoryType;
        }
        
        $sql .= " ORDER BY name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Update category sort order
     */
    public function updateSortOrder($id, $sortOrder) {
        return $this->update($id, ['sort_order' => $sortOrder]);
    }
    
    /**
     * Toggle category active status
     */
    public function toggleStatus($id) {
        $category = $this->find($id);
        if ($category) {
            $newStatus = $category['is_active'] ? 0 : 1;
            return $this->update($id, ['is_active' => $newStatus]);
        }
        return false;
    }
    
    
    /**
     * Validate category data before create/update
     */
    public function validateCategory($data, $id = null) {
        $errors = [];
        
        // Required fields validation
        $required = ['name', 'category_type'];
        $errors = array_merge($errors, $this->validateRequired($data, $required));
        
        // Check for duplicate names at same level
        if (!empty($data['name'])) {
            $conditions = [
                'name' => $data['name'],
                'parent_id' => $data['parent_id'] ?? null
            ];
            
            if ($id) {
                // For updates, exclude current record
                $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                        WHERE name = ? AND parent_id " . (is_null($data['parent_id'] ?? null) ? "IS NULL" : "= ?") . " AND id != ?";
                $params = [$data['name']];
                if (!is_null($data['parent_id'] ?? null)) {
                    $params[] = $data['parent_id'];
                }
                $params[] = $id;
                $result = $this->db->fetch($sql, $params);
            } else {
                // For new records
                if (is_null($data['parent_id'] ?? null)) {
                    $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table} WHERE name = ? AND parent_id IS NULL", [$data['name']]);
                } else {
                    $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table} WHERE name = ? AND parent_id = ?", [$data['name'], $data['parent_id']]);
                }
            }
            
            if ($result && $result['count'] > 0) {
                $errors[] = "Category name already exists at this level";
            }
        }
        
        // Validate category type
        $validTypes = ['issue', 'damage', 'delay', 'service', 'billing', 'other'];
        if (!empty($data['category_type']) && !in_array($data['category_type'], $validTypes)) {
            $errors[] = "Invalid category type";
        }
        
        // Validate priority level
        if (isset($data['priority_level']) && !in_array($data['priority_level'], [1, 2, 3, 4, 5])) {
            $errors[] = "Priority level must be between 1 and 5";
        }
        
        
        return $errors;
    }
    
    /**
     * Create category with validation
     */
    public function createCategory($data) {
        $errors = $this->validateCategory($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Set defaults
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['priority_level'] = $data['priority_level'] ?? 3;
        
        $result = $this->create($data);
        
        if ($result) {
            return ['success' => true, 'data' => $result];
        } else {
            return ['success' => false, 'errors' => ['Failed to create category']];
        }
    }
    
    /**
     * Update category with validation
     */
    public function updateCategory($id, $data) {
        $errors = $this->validateCategory($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $result = $this->update($id, $data);
        
        if ($result) {
            return ['success' => true, 'data' => $result];
        } else {
            return ['success' => false, 'errors' => ['Failed to update category']];
        }
    }
}