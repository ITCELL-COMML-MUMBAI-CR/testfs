<?php
/**
 * ShedModel - Manages sheds/terminals data for SAMPARK system
 * Handles railway shed information, locations, and operational details
 */

require_once 'BaseModel.php';

class ShedModel extends BaseModel {
    
    protected $table = 'sheds';
    protected $fillable = [
        'shed_name',
        'shed_code',
        'shed_type',
        'zone_code',
        'division_code',
        'state',
        'district',
        'address',
        'pincode',
        'contact_person',
        'contact_phone',
        'contact_email',
        'operational_hours',
        'facilities',
        'is_active',
        'latitude',
        'longitude',
        'sort_order'
    ];
    
    /**
     * Get all active sheds
     */
    public function getActiveSheds() {
        return $this->findAll(['is_active' => 1], 'shed_name ASC');
    }
    
    /**
     * Get sheds by zone
     */
    public function getShedsByZone($zoneCode) {
        return $this->findAll(['zone_code' => $zoneCode, 'is_active' => 1], 'shed_name ASC');
    }
    
    /**
     * Get sheds by division
     */
    public function getShedsByDivision($divisionCode) {
        return $this->findAll(['division_code' => $divisionCode, 'is_active' => 1], 'shed_name ASC');
    }
    
    /**
     * Get sheds by state
     */
    public function getShedsByState($state) {
        return $this->findAll(['state' => $state, 'is_active' => 1], 'shed_name ASC');
    }
    
    /**
     * Get sheds by type (freight, passenger, maintenance, etc.)
     */
    public function getShedsByType($type) {
        return $this->findAll(['shed_type' => $type, 'is_active' => 1], 'shed_name ASC');
    }
    
    /**
     * Search sheds by name, code, or location
     */
    public function searchSheds($query) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 AND (
                    shed_name LIKE ? OR 
                    shed_code LIKE ? OR 
                    state LIKE ? OR 
                    district LIKE ? OR
                    address LIKE ?
                )
                ORDER BY shed_name ASC";
        
        $searchTerm = '%' . $query . '%';
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Get shed by code
     */
    public function getShedByCode($shedCode) {
        return $this->findBy(['shed_code' => $shedCode, 'is_active' => 1]);
    }
    
    /**
     * Get all zones with shed counts
     */
    public function getZonesWithCounts() {
        $sql = "SELECT zone_code, COUNT(*) as shed_count
                FROM {$this->table} 
                WHERE is_active = 1 
                GROUP BY zone_code 
                ORDER BY zone_code";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get all divisions with shed counts by zone
     */
    public function getDivisionsByZone($zoneCode) {
        $sql = "SELECT division_code, COUNT(*) as shed_count
                FROM {$this->table} 
                WHERE zone_code = ? AND is_active = 1 
                GROUP BY division_code 
                ORDER BY division_code";
        return $this->db->fetchAll($sql, [$zoneCode]);
    }
    
    /**
     * Get all states with shed counts
     */
    public function getStatesWithCounts() {
        $sql = "SELECT state, COUNT(*) as shed_count
                FROM {$this->table} 
                WHERE is_active = 1 
                GROUP BY state 
                ORDER BY state";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get shed statistics and ticket counts
     */
    public function getShedStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT s.id, s.shed_name, s.shed_code, s.zone_code, s.division_code,
                       COUNT(t.id) as ticket_count,
                       COUNT(CASE WHEN t.status = 'pending' THEN 1 END) as pending_count,
                       COUNT(CASE WHEN t.status = 'resolved' THEN 1 END) as resolved_count,
                       AVG(CASE WHEN t.status = 'resolved' THEN 
                           TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) 
                       END) as avg_resolution_hours
                FROM {$this->table} s 
                LEFT JOIN complaint_tickets t ON s.id = t.shed_id";
        
        $params = [];
        $conditions = ['s.is_active = 1'];
        
        if ($dateFrom) {
            $conditions[] = "(t.created_at >= ? OR t.created_at IS NULL)";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $conditions[] = "(t.created_at <= ? OR t.created_at IS NULL)";
            $params[] = $dateTo;
        }
        
        $sql .= " WHERE " . implode(' AND ', $conditions);
        $sql .= " GROUP BY s.id, s.shed_name, s.shed_code, s.zone_code, s.division_code 
                  ORDER BY ticket_count DESC, s.shed_name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get nearest sheds by coordinates (if lat/lng available)
     */
    public function getNearestSheds($latitude, $longitude, $radius = 50, $limit = 10) {
        $sql = "SELECT *, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) AS distance 
                FROM {$this->table} 
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND is_active = 1
                HAVING distance < ? 
                ORDER BY distance 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$latitude, $longitude, $latitude, $radius, $limit]);
    }
    
    /**
     * Get shed operational status
     */
    public function getShedOperationalStatus($shedId) {
        $shed = $this->find($shedId);
        if (!$shed) return null;
        
        // Check current time against operational hours
        $currentTime = date('H:i:s');
        $operationalHours = $shed['operational_hours'] ?? '24/7';
        
        return [
            'shed_id' => $shedId,
            'shed_name' => $shed['shed_name'],
            'operational_hours' => $operationalHours,
            'is_operational' => $this->isOperational($operationalHours),
            'current_time' => $currentTime
        ];
    }
    
    /**
     * Check if shed is currently operational based on hours
     */
    private function isOperational($operationalHours) {
        if ($operationalHours === '24/7' || empty($operationalHours)) {
            return true;
        }
        
        // Parse operational hours format like "06:00-22:00"
        if (preg_match('/(\d{2}:\d{2})-(\d{2}:\d{2})/', $operationalHours, $matches)) {
            $startTime = $matches[1];
            $endTime = $matches[2];
            $currentTime = date('H:i');
            
            return ($currentTime >= $startTime && $currentTime <= $endTime);
        }
        
        return true; // Default to operational if format not recognized
    }
    
    /**
     * Get shed contact information
     */
    public function getShedContact($shedId) {
        $sql = "SELECT shed_name, contact_person, contact_phone, contact_email, address, pincode
                FROM {$this->table} 
                WHERE id = ? AND is_active = 1";
        return $this->db->fetch($sql, [$shedId]);
    }
    
    /**
     * Get shed facilities list
     */
    public function getShedFacilities($shedId) {
        $shed = $this->find($shedId);
        if (!$shed || empty($shed['facilities'])) return [];
        
        // Assuming facilities are stored as JSON or comma-separated
        $facilities = $shed['facilities'];
        if (is_string($facilities)) {
            // Try to decode JSON first
            $decoded = json_decode($facilities, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            // Otherwise treat as comma-separated
            return array_map('trim', explode(',', $facilities));
        }
        
        return is_array($facilities) ? $facilities : [];
    }
    
    /**
     * Update shed facilities
     */
    public function updateFacilities($shedId, $facilities) {
        $facilitiesJson = is_array($facilities) ? json_encode($facilities) : $facilities;
        return $this->update($shedId, ['facilities' => $facilitiesJson]);
    }
    
    /**
     * Validate shed data
     */
    public function validateShed($data, $id = null) {
        $errors = [];
        
        // Required fields validation
        $required = ['shed_name', 'shed_code', 'shed_type', 'zone_code', 'division_code', 'state'];
        $errors = array_merge($errors, $this->validateRequired($data, $required));
        
        // Check for duplicate shed codes
        if (!empty($data['shed_code'])) {
            $conditions = ['shed_code' => $data['shed_code']];
            if ($id) {
                $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE shed_code = ? AND id != ?";
                $result = $this->db->fetch($sql, [$data['shed_code'], $id]);
            } else {
                $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table} WHERE shed_code = ?", [$data['shed_code']]);
            }
            
            if ($result && $result['count'] > 0) {
                $errors[] = "Shed code already exists";
            }
        }
        
        // Validate shed type
        $validTypes = ['freight', 'passenger', 'maintenance', 'goods', 'mixed', 'other'];
        if (!empty($data['shed_type']) && !in_array($data['shed_type'], $validTypes)) {
            $errors[] = "Invalid shed type";
        }
        
        // Validate email format if provided
        if (!empty($data['contact_email']) && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid contact email format";
        }
        
        // Validate pincode format if provided
        if (!empty($data['pincode']) && !preg_match('/^\d{6}$/', $data['pincode'])) {
            $errors[] = "Invalid pincode format (should be 6 digits)";
        }
        
        // Validate coordinates if provided
        if (!empty($data['latitude']) && (!is_numeric($data['latitude']) || $data['latitude'] < -90 || $data['latitude'] > 90)) {
            $errors[] = "Invalid latitude (should be between -90 and 90)";
        }
        
        if (!empty($data['longitude']) && (!is_numeric($data['longitude']) || $data['longitude'] < -180 || $data['longitude'] > 180)) {
            $errors[] = "Invalid longitude (should be between -180 and 180)";
        }
        
        return $errors;
    }
    
    /**
     * Create shed with validation
     */
    public function createShed($data) {
        $errors = $this->validateShed($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Set defaults
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['operational_hours'] = $data['operational_hours'] ?? '24/7';
        
        $result = $this->create($data);
        
        if ($result) {
            return ['success' => true, 'data' => $result];
        } else {
            return ['success' => false, 'errors' => ['Failed to create shed']];
        }
    }
    
    /**
     * Update shed with validation
     */
    public function updateShed($id, $data) {
        $errors = $this->validateShed($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $result = $this->update($id, $data);
        
        if ($result) {
            return ['success' => true, 'data' => $result];
        } else {
            return ['success' => false, 'errors' => ['Failed to update shed']];
        }
    }
    
    /**
     * Toggle shed active status
     */
    public function toggleStatus($id) {
        $shed = $this->find($id);
        if ($shed) {
            $newStatus = $shed['is_active'] ? 0 : 1;
            return $this->update($id, ['is_active' => $newStatus]);
        }
        return false;
    }
}