<?php

namespace SmartCast\Models;

/**
 * Vote Ledger Model - Blockchain-like immutable vote tracking
 */
class VoteLedger extends BaseModel
{
    protected $table = 'vote_ledger';
    protected $fillable = [
        'vote_id', 'transaction_id', 'tenant_id', 'event_id', 
        'contestant_id', 'quantity', 'hash'
    ];
    
    public function createLedgerEntry($voteId, $transactionId, $tenantId, $eventId, $contestantId, $quantity)
    {
        // Generate hash for integrity
        $hash = $this->generateVoteHash($voteId, $transactionId, $tenantId, $eventId, $contestantId, $quantity);
        
        return $this->create([
            'vote_id' => $voteId,
            'transaction_id' => $transactionId,
            'tenant_id' => $tenantId,
            'event_id' => $eventId,
            'contestant_id' => $contestantId,
            'quantity' => $quantity,
            'hash' => $hash
        ]);
    }
    
    private function generateVoteHash($voteId, $transactionId, $tenantId, $eventId, $contestantId, $quantity)
    {
        // Get previous hash for chaining
        $previousHash = $this->getLastHash($tenantId);
        
        // Create data string for hashing
        $data = implode('|', [
            $voteId,
            $transactionId,
            $tenantId,
            $eventId,
            $contestantId,
            $quantity,
            time(),
            $previousHash
        ]);
        
        // Generate SHA-256 hash
        return hash('sha256', $data);
    }
    
    private function getLastHash($tenantId)
    {
        $sql = "
            SELECT hash FROM {$this->table} 
            WHERE tenant_id = :tenant_id 
            ORDER BY created_at DESC 
            LIMIT 1
        ";
        
        $result = $this->db->selectOne($sql, ['tenant_id' => $tenantId]);
        
        return $result ? $result['hash'] : '0000000000000000000000000000000000000000000000000000000000000000';
    }
    
    public function verifyLedgerIntegrity($tenantId, $eventId = null)
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($eventId) {
            $sql .= " AND event_id = :event_id";
            $params['event_id'] = $eventId;
        }
        
        $sql .= " ORDER BY created_at ASC";
        
        $entries = $this->db->select($sql, $params);
        
        $errors = [];
        $previousHash = '0000000000000000000000000000000000000000000000000000000000000000';
        
        foreach ($entries as $entry) {
            // Recalculate hash
            $expectedHash = $this->calculateExpectedHash($entry, $previousHash);
            
            if ($entry['hash'] !== $expectedHash) {
                $errors[] = [
                    'entry_id' => $entry['id'],
                    'vote_id' => $entry['vote_id'],
                    'expected_hash' => $expectedHash,
                    'actual_hash' => $entry['hash'],
                    'error' => 'Hash mismatch - possible tampering'
                ];
            }
            
            $previousHash = $entry['hash'];
        }
        
        return [
            'valid' => empty($errors),
            'total_entries' => count($entries),
            'errors' => $errors
        ];
    }
    
    private function calculateExpectedHash($entry, $previousHash)
    {
        $data = implode('|', [
            $entry['vote_id'],
            $entry['transaction_id'],
            $entry['tenant_id'],
            $entry['event_id'],
            $entry['contestant_id'],
            $entry['quantity'],
            strtotime($entry['created_at']),
            $previousHash
        ]);
        
        return hash('sha256', $data);
    }
    
    public function getLedgerByEvent($eventId)
    {
        $sql = "
            SELECT vl.*, v.created_at as vote_time, c.name as contestant_name, 
                   t.amount, t.msisdn
            FROM {$this->table} vl
            INNER JOIN votes v ON vl.vote_id = v.id
            INNER JOIN contestants c ON vl.contestant_id = c.id
            INNER JOIN transactions t ON vl.transaction_id = t.id
            WHERE vl.event_id = :event_id
            ORDER BY vl.created_at ASC
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getLedgerByContestant($contestantId)
    {
        $sql = "
            SELECT vl.*, v.created_at as vote_time, t.amount, t.msisdn
            FROM {$this->table} vl
            INNER JOIN votes v ON vl.vote_id = v.id
            INNER JOIN transactions t ON vl.transaction_id = t.id
            WHERE vl.contestant_id = :contestant_id
            ORDER BY vl.created_at ASC
        ";
        
        return $this->db->select($sql, ['contestant_id' => $contestantId]);
    }
    
    public function getTotalVotesFromLedger($eventId, $contestantId = null)
    {
        $sql = "SELECT SUM(quantity) as total FROM {$this->table} WHERE event_id = :event_id";
        $params = ['event_id' => $eventId];
        
        if ($contestantId) {
            $sql .= " AND contestant_id = :contestant_id";
            $params['contestant_id'] = $contestantId;
        }
        
        $result = $this->db->selectOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function getLedgerStats($tenantId, $eventId = null)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_entries,
                SUM(quantity) as total_votes,
                COUNT(DISTINCT event_id) as events_count,
                COUNT(DISTINCT contestant_id) as contestants_count,
                COUNT(DISTINCT transaction_id) as transactions_count,
                MIN(created_at) as first_entry,
                MAX(created_at) as last_entry
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($eventId) {
            $sql .= " AND event_id = :event_id";
            $params['event_id'] = $eventId;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    public function searchLedger($criteria)
    {
        $sql = "
            SELECT vl.*, v.created_at as vote_time, c.name as contestant_name,
                   e.name as event_name, t.amount, t.msisdn
            FROM {$this->table} vl
            INNER JOIN votes v ON vl.vote_id = v.id
            INNER JOIN contestants c ON vl.contestant_id = c.id
            INNER JOIN events e ON vl.event_id = e.id
            INNER JOIN transactions t ON vl.transaction_id = t.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($criteria['tenant_id'])) {
            $sql .= " AND vl.tenant_id = :tenant_id";
            $params['tenant_id'] = $criteria['tenant_id'];
        }
        
        if (isset($criteria['event_id'])) {
            $sql .= " AND vl.event_id = :event_id";
            $params['event_id'] = $criteria['event_id'];
        }
        
        if (isset($criteria['contestant_id'])) {
            $sql .= " AND vl.contestant_id = :contestant_id";
            $params['contestant_id'] = $criteria['contestant_id'];
        }
        
        if (isset($criteria['transaction_id'])) {
            $sql .= " AND vl.transaction_id = :transaction_id";
            $params['transaction_id'] = $criteria['transaction_id'];
        }
        
        if (isset($criteria['msisdn'])) {
            $sql .= " AND t.msisdn = :msisdn";
            $params['msisdn'] = $criteria['msisdn'];
        }
        
        if (isset($criteria['date_from'])) {
            $sql .= " AND vl.created_at >= :date_from";
            $params['date_from'] = $criteria['date_from'];
        }
        
        if (isset($criteria['date_to'])) {
            $sql .= " AND vl.created_at <= :date_to";
            $params['date_to'] = $criteria['date_to'];
        }
        
        $sql .= " ORDER BY vl.created_at DESC";
        
        if (isset($criteria['limit'])) {
            $sql .= " LIMIT " . (int)$criteria['limit'];
        }
        
        return $this->db->select($sql, $params);
    }
    
    public function exportLedger($tenantId, $eventId = null, $format = 'json')
    {
        $ledgerData = $this->searchLedger([
            'tenant_id' => $tenantId,
            'event_id' => $eventId
        ]);
        
        $export = [
            'exported_at' => date('Y-m-d H:i:s'),
            'tenant_id' => $tenantId,
            'event_id' => $eventId,
            'total_entries' => count($ledgerData),
            'integrity_check' => $this->verifyLedgerIntegrity($tenantId, $eventId),
            'entries' => $ledgerData
        ];
        
        switch ($format) {
            case 'json':
                return json_encode($export, JSON_PRETTY_PRINT);
                
            case 'csv':
                return $this->convertToCSV($ledgerData);
                
            default:
                return $export;
        }
    }
    
    private function convertToCSV($data)
    {
        if (empty($data)) {
            return '';
        }
        
        $csv = '';
        
        // Headers
        $headers = array_keys($data[0]);
        $csv .= implode(',', $headers) . "\n";
        
        // Data rows
        foreach ($data as $row) {
            $csv .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, $row)) . "\n";
        }
        
        return $csv;
    }
    
    public function getLedgerChain($tenantId, $limit = 100)
    {
        $sql = "
            SELECT id, vote_id, transaction_id, hash, created_at
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
            ORDER BY created_at DESC
            LIMIT {$limit}
        ";
        
        $entries = $this->db->select($sql, ['tenant_id' => $tenantId]);
        
        // Build chain visualization
        $chain = [];
        foreach ($entries as $entry) {
            $chain[] = [
                'id' => $entry['id'],
                'vote_id' => $entry['vote_id'],
                'transaction_id' => $entry['transaction_id'],
                'hash' => $entry['hash'],
                'short_hash' => substr($entry['hash'], 0, 8) . '...',
                'timestamp' => $entry['created_at']
            ];
        }
        
        return array_reverse($chain); // Show chronological order
    }
}
