<?php

namespace SmartCast\Models;

/**
 * Vote Bundle Model
 */
class VoteBundle extends BaseModel
{
    protected $table = 'vote_bundles';
    protected $fillable = [
        'event_id', 'name', 'votes', 'price', 'active'
    ];
    
    public function getBundlesByEvent($eventId, $tenantId = null)
    {
        if ($tenantId) {
            // Ensure tenant isolation - only get bundles for events owned by the tenant
            $sql = "
                SELECT vb.*, e.vote_price,
                       ROUND(((vb.votes * e.vote_price - vb.price) / (vb.votes * e.vote_price)) * 100, 0) as discount
                FROM vote_bundles vb
                INNER JOIN events e ON vb.event_id = e.id
                WHERE vb.event_id = :event_id 
                AND e.tenant_id = :tenant_id
                AND vb.active = 1
                ORDER BY vb.price ASC
            ";
            
            return $this->db->select($sql, [
                'event_id' => $eventId,
                'tenant_id' => $tenantId
            ]);
        }
        
        // For non-tenant specific queries, we need to calculate discount manually
        $bundles = $this->findAll([
            'event_id' => $eventId,
            'active' => 1
        ], 'price ASC');
        
        // Get event vote price to calculate discount
        $eventModel = new Event();
        $event = $eventModel->find($eventId);
        $votePrice = $event ? $event['vote_price'] : 0;
        
        // Calculate discount for each bundle
        foreach ($bundles as &$bundle) {
            $regularPrice = $bundle['votes'] * $votePrice;
            $savings = $regularPrice - $bundle['price'];
            $bundle['discount'] = $regularPrice > 0 ? round(($savings / $regularPrice) * 100, 0) : 0;
        }
        
        return $bundles;
    }
    
    public function getBundlesByTenant($tenantId)
    {
        $sql = "
            SELECT vb.*, e.name as event_name
            FROM vote_bundles vb
            INNER JOIN events e ON vb.event_id = e.id
            WHERE e.tenant_id = :tenant_id
            ORDER BY e.name, vb.price ASC
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
    
    public function getActiveBundles($eventId)
    {
        $sql = "
            SELECT vb.*, 
                   COUNT(t.id) as usage_count,
                   COALESCE(SUM(t.amount), 0) as total_revenue
            FROM vote_bundles vb
            LEFT JOIN transactions t ON vb.id = t.bundle_id AND t.status = 'success'
            WHERE vb.event_id = :event_id AND vb.active = 1
            GROUP BY vb.id
            ORDER BY vb.price ASC
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function createDefaultBundles($eventId)
    {
        $defaultBundles = [
            ['name' => '1 Vote', 'votes' => 1, 'price' => 1.00],
            ['name' => '5 Votes', 'votes' => 5, 'price' => 4.50],
            ['name' => '10 Votes', 'votes' => 10, 'price' => 8.00],
            ['name' => '25 Votes', 'votes' => 25, 'price' => 18.00]
        ];
        
        foreach ($defaultBundles as $bundle) {
            $bundle['event_id'] = $eventId;
            $bundle['active'] = 1;
            $this->create($bundle);
        }
    }
    
    public function activate($bundleId)
    {
        return $this->update($bundleId, ['active' => 1]);
    }
    
    public function deactivate($bundleId)
    {
        return $this->update($bundleId, ['active' => 0]);
    }
    
    public function getBundleStats($bundleId)
    {
        $sql = "
            SELECT 
                vb.*,
                COUNT(t.id) as total_purchases,
                COUNT(CASE WHEN t.status = 'success' THEN 1 END) as successful_purchases,
                COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN t.status = 'success' THEN vb.votes END), 0) as total_votes_sold
            FROM vote_bundles vb
            LEFT JOIN transactions t ON vb.id = t.bundle_id
            WHERE vb.id = :bundle_id
            GROUP BY vb.id
        ";
        
        return $this->db->selectOne($sql, ['bundle_id' => $bundleId]);
    }
}
