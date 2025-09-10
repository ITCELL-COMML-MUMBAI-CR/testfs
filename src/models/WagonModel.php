<?php
/**
 * WagonModel - Manages railway wagon information for SAMPARK system
 * Handles wagon types, specifications, and operational details
 */

require_once 'BaseModel.php';

class WagonModel extends BaseModel {
    
    protected $table = 'wagons';
    protected $fillable = [
        'wagon_number',
        'wagon_type',
        'wagon_subtype',
        'capacity_tonnes',
        'length_mm',
        'breadth_mm',
        'height_mm',
        'tare_weight',
        'max_speed',
        'manufacturer',
        'manufacture_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'current_location',
        'status',
        'owner_railway',
        'home_shed_id',
        'is_active',
        'remarks'
    ];
    
    /**
     * Get all wagon types
     */
    public function getWagonTypes() {
        $sql = "SELECT DISTINCT wagon_type, COUNT(*) as count 
                FROM {$this->table} 
                WHERE is_active = 1 
                GROUP BY wagon_type 
                ORDER BY wagon_type";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get wagon subtypes by type
     */
    public function getWagonSubtypes($wagonType) {
        $sql = "SELECT DISTINCT wagon_subtype, COUNT(*) as count 
                FROM {$this->table} 
                WHERE wagon_type = ? AND is_active = 1 
                GROUP BY wagon_subtype 
                ORDER BY wagon_subtype";
        return $this->db->fetchAll($sql, [$wagonType]);
    }
    
    /**
     * Get wagon by number
     */
    public function getWagonByNumber($wagonNumber) {
        return $this->findBy(['wagon_number' => $wagonNumber, 'is_active' => 1]);
    }
    
    /**
     * Search wagons by number, type, or location
     */
    public function searchWagons($query, $filters = []) {
        $sql = "SELECT w.*, s.shed_name as home_shed_name 
                FROM {$this->table} w
                LEFT JOIN sheds s ON w.home_shed_id = s.id
                WHERE w.is_active = 1 AND (
                    w.wagon_number LIKE ? OR 
                    w.wagon_type LIKE ? OR 
                    w.current_location LIKE ? OR
                    w.remarks LIKE ?
                )";
        
        $params = [];
        $searchTerm = '%' . $query . '%';
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        
        // Apply filters
        if (!empty($filters['wagon_type'])) {
            $sql .= " AND w.wagon_type = ?";
            $params[] = $filters['wagon_type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['owner_railway'])) {
            $sql .= " AND w.owner_railway = ?";
            $params[] = $filters['owner_railway'];
        }
        
        if (!empty($filters['home_shed_id'])) {
            $sql .= " AND w.home_shed_id = ?";
            $params[] = $filters['home_shed_id'];
        }
        
        $sql .= " ORDER BY w.wagon_number ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get wagons by type and status
     */
    public function getWagonsByTypeAndStatus($wagonType = null, $status = null) {
        $conditions = ['is_active' => 1];
        
        if ($wagonType) {
            $conditions['wagon_type'] = $wagonType;
        }
        
        if ($status) {
            $conditions['status'] = $status;
        }
        
        return $this->findAll($conditions, 'wagon_number ASC');
    }
    
    /**
     * Get wagons by home shed
     */
    public function getWagonsByShed($shedId) {
        $sql = "SELECT w.*, s.shed_name as home_shed_name 
                FROM {$this->table} w
                LEFT JOIN sheds s ON w.home_shed_id = s.id
                WHERE w.home_shed_id = ? AND w.is_active = 1 
                ORDER BY w.wagon_number ASC";
        return $this->db->fetchAll($sql, [$shedId]);
    }
    
    /**
     * Get wagons requiring maintenance
     */
    public function getWagonsForMaintenance($daysAhead = 30) {
        $futureDate = date('Y-m-d', strtotime("+{$daysAhead} days"));
        
        $sql = "SELECT w.*, s.shed_name as home_shed_name,
                       DATEDIFF(w.next_maintenance_date, CURDATE()) as days_to_maintenance
                FROM {$this->table} w
                LEFT JOIN sheds s ON w.home_shed_id = s.id
                WHERE w.is_active = 1 
                AND w.next_maintenance_date IS NOT NULL 
                AND w.next_maintenance_date <= ? 
                ORDER BY w.next_maintenance_date ASC";
        
        return $this->db->fetchAll($sql, [$futureDate]);
    }
    
    /**
     * Get overdue maintenance wagons
     */
    public function getOverdueMaintenanceWagons() {
        $today = date('Y-m-d');
        
        $sql = "SELECT w.*, s.shed_name as home_shed_name,
                       DATEDIFF(CURDATE(), w.next_maintenance_date) as days_overdue
                FROM {$this->table} w
                LEFT JOIN sheds s ON w.home_shed_id = s.id
                WHERE w.is_active = 1 
                AND w.next_maintenance_date IS NOT NULL 
                AND w.next_maintenance_date < ? 
                ORDER BY days_overdue DESC";
        
        return $this->db->fetchAll($sql, [$today]);
    }
    
    /**
     * Get wagon statistics
     */
    public function getWagonStats() {
        $sql = "SELECT 
                    COUNT(*) as total_wagons,
                    COUNT(CASE WHEN status = 'available' THEN 1 END) as available_wagons,
                    COUNT(CASE WHEN status = 'in_use' THEN 1 END) as in_use_wagons,
                    COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance_wagons,
                    COUNT(CASE WHEN status = 'out_of_service' THEN 1 END) as out_of_service_wagons,
                    AVG(capacity_tonnes) as avg_capacity,
                    SUM(capacity_tonnes) as total_capacity
                FROM {$this->table} 
                WHERE is_active = 1";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get wagon capacity analysis by type
     */
    public function getCapacityAnalysisByType() {
        $sql = "SELECT wagon_type,
                       COUNT(*) as wagon_count,
                       AVG(capacity_tonnes) as avg_capacity,
                       SUM(capacity_tonnes) as total_capacity,
                       MIN(capacity_tonnes) as min_capacity,
                       MAX(capacity_tonnes) as max_capacity
                FROM {$this->table} 
                WHERE is_active = 1 
                GROUP BY wagon_type 
                ORDER BY total_capacity DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get wagons by owner railway
     */
    public function getWagonsByOwner($ownerRailway = null) {
        if ($ownerRailway) {
            return $this->findAll(['owner_railway' => $ownerRailway, 'is_active' => 1], 'wagon_number ASC');
        } else {
            $sql = "SELECT owner_railway, COUNT(*) as wagon_count
                    FROM {$this->table} 
                    WHERE is_active = 1 
                    GROUP BY owner_railway 
                    ORDER BY wagon_count DESC";
            return $this->db->fetchAll($sql);
        }
    }
    
    /**
     * Update wagon location
     */
    public function updateLocation($wagonId, $location) {
        return $this->update($wagonId, ['current_location' => $location]);
    }
    
    /**
     * Update wagon status
     */
    public function updateStatus($wagonId, $status, $remarks = null) {
        $data = ['status' => $status];
        if ($remarks !== null) {
            $data['remarks'] = $remarks;
        }
        return $this->update($wagonId, $data);
    }
    
    /**
     * Update maintenance dates
     */
    public function updateMaintenanceDate($wagonId, $lastMaintenanceDate, $nextMaintenanceDate = null) {
        $data = ['last_maintenance_date' => $lastMaintenanceDate];
        
        if ($nextMaintenanceDate) {
            $data['next_maintenance_date'] = $nextMaintenanceDate;
        } else {
            // Calculate next maintenance date (assume 6 months interval)
            $data['next_maintenance_date'] = date('Y-m-d', strtotime($lastMaintenanceDate . ' +6 months'));
        }
        
        return $this->update($wagonId, $data);
    }
    
    /**
     * Get wagon utilization report
     */
    public function getUtilizationReport($dateFrom, $dateTo) {
        $sql = "SELECT w.wagon_type, w.status,
                       COUNT(*) as wagon_count,
                       AVG(w.capacity_tonnes) as avg_capacity,
                       COUNT(t.id) as associated_tickets
                FROM {$this->table} w
                LEFT JOIN complaint_tickets t ON FIND_IN_SET(w.wagon_number, t.wagon_numbers)
                    AND t.created_at BETWEEN ? AND ?
                WHERE w.is_active = 1
                GROUP BY w.wagon_type, w.status
                ORDER BY w.wagon_type, w.status";
        
        return $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
    }
    
    /**
     * Validate wagon data
     */
    public function validateWagon($data, $id = null) {
        $errors = [];
        
        // Required fields validation
        $required = ['wagon_number', 'wagon_type', 'capacity_tonnes', 'owner_railway'];
        $errors = array_merge($errors, $this->validateRequired($data, $required));
        
        // Check for duplicate wagon numbers
        if (!empty($data['wagon_number'])) {
            if ($id) {
                $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE wagon_number = ? AND id != ?";
                $result = $this->db->fetch($sql, [$data['wagon_number'], $id]);
            } else {
                $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table} WHERE wagon_number = ?", [$data['wagon_number']]);
            }
            
            if ($result && $result['count'] > 0) {
                $errors[] = "Wagon number already exists";
            }
            
            // Validate wagon number format (basic validation)
            if (!preg_match('/^[A-Z0-9\-\/]+$/', $data['wagon_number'])) {
                $errors[] = "Invalid wagon number format";
            }
        }
        
        // Validate numeric fields
        if (!empty($data['capacity_tonnes']) && (!is_numeric($data['capacity_tonnes']) || $data['capacity_tonnes'] <= 0)) {
            $errors[] = "Capacity must be a positive number";
        }
        
        if (!empty($data['tare_weight']) && (!is_numeric($data['tare_weight']) || $data['tare_weight'] <= 0)) {
            $errors[] = "Tare weight must be a positive number";
        }
        
        if (!empty($data['max_speed']) && (!is_numeric($data['max_speed']) || $data['max_speed'] <= 0)) {
            $errors[] = "Max speed must be a positive number";
        }
        
        // Validate dimensions
        $dimensions = ['length_mm', 'breadth_mm', 'height_mm'];
        foreach ($dimensions as $dimension) {
            if (!empty($data[$dimension]) && (!is_numeric($data[$dimension]) || $data[$dimension] <= 0)) {
                $errors[] = ucfirst(str_replace('_mm', '', $dimension)) . " must be a positive number";
            }
        }
        
        // Validate status
        $validStatuses = ['available', 'in_use', 'maintenance', 'out_of_service', 'scrapped'];
        if (!empty($data['status']) && !in_array($data['status'], $validStatuses)) {
            $errors[] = "Invalid wagon status";
        }
        
        // Validate dates
        if (!empty($data['manufacture_date']) && !strtotime($data['manufacture_date'])) {
            $errors[] = "Invalid manufacture date format";
        }
        
        if (!empty($data['last_maintenance_date']) && !strtotime($data['last_maintenance_date'])) {
            $errors[] = "Invalid last maintenance date format";
        }
        
        if (!empty($data['next_maintenance_date']) && !strtotime($data['next_maintenance_date'])) {
            $errors[] = "Invalid next maintenance date format";
        }
        
        // Validate maintenance date logic
        if (!empty($data['last_maintenance_date']) && !empty($data['next_maintenance_date'])) {
            if (strtotime($data['next_maintenance_date']) <= strtotime($data['last_maintenance_date'])) {
                $errors[] = "Next maintenance date must be after last maintenance date";
            }
        }
        
        return $errors;
    }
    
    /**
     * Create wagon with validation
     */
    public function createWagon($data) {
        $errors = $this->validateWagon($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Set defaults
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['status'] = $data['status'] ?? 'available';
        
        $result = $this->create($data);
        
        if ($result) {
            return ['success' => true, 'data' => $result];
        } else {
            return ['success' => false, 'errors' => ['Failed to create wagon']];
        }
    }
    
    /**
     * Update wagon with validation
     */
    public function updateWagon($id, $data) {
        $errors = $this->validateWagon($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $result = $this->update($id, $data);
        
        if ($result) {
            return ['success' => true, 'data' => $result];
        } else {
            return ['success' => false, 'errors' => ['Failed to update wagon']];
        }
    }
}