<?php

namespace SmartCast\Models;

/**
 * User Model
 */
class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = [
        'tenant_id', 'email', 'password_hash', 'role', 'active'
    ];
    
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        return $this->db->selectOne($sql, ['email' => $email]);
    }
    
    public function createUser($data)
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        return $this->create($data);
    }
    
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    public function getUsersByTenant($tenantId)
    {
        return $this->findAll(['tenant_id' => $tenantId, 'active' => 1]);
    }
    
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $userId]);
    }
    
    public function isOwner($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'owner';
    }
    
    public function isPlatformAdmin($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'platform_admin';
    }
    
    public function canManageEvent($userId, $eventId)
    {
        $user = $this->find($userId);
        if (!$user) return false;
        
        // Platform admins can manage all events
        if ($user['role'] === 'platform_admin') {
            return true;
        }
        
        // Check if user belongs to the same tenant as the event
        $eventModel = new Event();
        $event = $eventModel->find($eventId);
        
        return $event && $event['tenant_id'] === $user['tenant_id'] && 
               in_array($user['role'], ['owner', 'manager']);
    }
}
