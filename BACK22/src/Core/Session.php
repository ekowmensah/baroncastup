<?php

namespace SmartCast\Core;

/**
 * Session Management Class
 */
class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        } else {
            // Session already started, don't change the name
            // This prevents issues when session is already active with different name
        }
    }
    
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
    
    public function destroy()
    {
        session_destroy();
    }
    
    public function regenerate()
    {
        session_regenerate_id(true);
    }
    
    public function flash($key, $value = null)
    {
        if ($value === null) {
            // Get flash message
            $message = $this->get("flash_{$key}");
            $this->remove("flash_{$key}");
            return $message;
        } else {
            // Set flash message
            $this->set("flash_{$key}", $value);
        }
    }
    
    public function isLoggedIn()
    {
        return $this->has('user_id');
    }
    
    public function getUserId()
    {
        return $this->get('user_id');
    }
    
    public function getTenantId()
    {
        return $this->get('tenant_id');
    }
    
    public function getUserRole()
    {
        return $this->get('user_role');
    }
    
    public function isSuperAdmin()
    {
        $role = $this->get('user_role') ?? $this->get('role');
        return $role === 'platform_admin';
    }
    
    public function isAdmin()
    {
        $role = $this->get('user_role') ?? $this->get('role');
        return in_array($role, ['platform_admin', 'admin']);
    }
    
    /**
     * Generate or retrieve CSRF token
     */
    public function getCsrfToken()
    {
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->get('csrf_token');
    }
    
    /**
     * Validate CSRF token from request
     */
    public function validateCsrfToken($token)
    {
        $sessionToken = $this->get('csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Generate hidden input HTML for CSRF token
     */
    public function csrfField()
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($this->getCsrfToken()) . '">';
    }
}
