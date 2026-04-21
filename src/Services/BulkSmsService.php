<?php

namespace SmartCast\Services;

use SmartCast\Models\SmsTemplate;
use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\Transaction;
use SmartCast\Models\Vote;
use SmartCast\Services\SmsService;

/**
 * Bulk SMS Service for targeted messaging
 */
class BulkSmsService
{
    private $smsService;
    private $templateModel;
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    private $transactionModel;
    private $voteModel;
    
    public function __construct()
    {
        $this->smsService = new SmsService();
        $this->templateModel = new SmsTemplate();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->transactionModel = new Transaction();
        $this->voteModel = new Vote();
    }
    
    /**
     * Send bulk SMS to nominees based on performance
     */
    public function sendToNomineesByPerformance($eventId, $templateId, $performanceType = 'all', $options = [])
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            throw new \Exception('Event not found');
        }
        
        // Get nominees based on performance
        $nominees = $this->getNomineesByPerformance($eventId, $performanceType, $options);
        
        $results = [];
        foreach ($nominees as $nominee) {
            if (empty($nominee['phone'])) {
                $results[] = [
                    'nominee_id' => $nominee['id'],
                    'nominee_name' => $nominee['name'],
                    'success' => false,
                    'error' => 'No phone number available'
                ];
                continue;
            }
            
            // Prepare template variables
            $variables = [
                'event_name' => $event['name'],
                'nominee_name' => $nominee['name'],
                'category_name' => $nominee['category_name'] ?? 'Unknown',
                'vote_count' => $nominee['total_votes'] ?? 0,
                'voting_url' => APP_URL . "/events/{$event['slug']}/vote/{$nominee['slug']}",
                'current_position' => $nominee['position'] ?? 'N/A',
                'percentage' => $nominee['vote_percentage'] ?? '0'
            ];
            
            try {
                $message = $this->templateModel->processTemplate($templateId, $variables);
                $smsResult = $this->smsService->sendSms($nominee['phone'], $message);
                
                $results[] = [
                    'nominee_id' => $nominee['id'],
                    'nominee_name' => $nominee['name'],
                    'phone' => $nominee['phone'],
                    'success' => $smsResult['success'],
                    'message_sent' => $message,
                    'sms_details' => $smsResult
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'nominee_id' => $nominee['id'],
                    'nominee_name' => $nominee['name'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // Small delay to avoid overwhelming SMS gateway
            usleep(200000); // 0.2 seconds
        }
        
        return [
            'total_nominees' => count($nominees),
            'results' => $results,
            'summary' => $this->generateSummary($results)
        ];
    }
    
    /**
     * Send bulk SMS to voters based on voting patterns
     */
    public function sendToVotersByPattern($eventId, $templateId, $voterType = 'all', $options = [])
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            throw new \Exception('Event not found');
        }
        
        // Get voters based on pattern
        $voters = $this->getVotersByPattern($eventId, $voterType, $options);
        
        $results = [];
        foreach ($voters as $voter) {
            // Prepare template variables
            $variables = [
                'event_name' => $event['name'],
                'voter_name' => $voter['voter_name'] ?? 'Valued Voter',
                'total_votes_cast' => $voter['total_votes'] ?? 0,
                'total_amount_spent' => 'GH₵' . number_format($voter['total_amount'] ?? 0, 2),
                'favorite_nominee' => $voter['favorite_nominee'] ?? 'N/A',
                'voting_url' => APP_URL . "/events/{$event['slug']}/vote",
                'last_vote_date' => $voter['last_vote_date'] ?? 'N/A'
            ];
            
            try {
                $message = $this->templateModel->processTemplate($templateId, $variables);
                $smsResult = $this->smsService->sendSms($voter['phone'], $message);
                
                $results[] = [
                    'phone' => $voter['phone'],
                    'success' => $smsResult['success'],
                    'message_sent' => $message,
                    'sms_details' => $smsResult
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'phone' => $voter['phone'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // Small delay to avoid overwhelming SMS gateway
            usleep(200000); // 0.2 seconds
        }
        
        return [
            'total_voters' => count($voters),
            'results' => $results,
            'summary' => $this->generateSummary($results)
        ];
    }
    
    /**
     * Send bulk SMS to custom phone list
     */
    public function sendToCustomList($phoneNumbers, $templateId, $variables = [])
    {
        $results = [];
        
        foreach ($phoneNumbers as $phone) {
            try {
                $message = $this->templateModel->processTemplate($templateId, $variables);
                $smsResult = $this->smsService->sendSms($phone, $message);
                
                $results[] = [
                    'phone' => $phone,
                    'success' => $smsResult['success'],
                    'message_sent' => $message,
                    'sms_details' => $smsResult
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'phone' => $phone,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // Small delay to avoid overwhelming SMS gateway
            usleep(200000); // 0.2 seconds
        }
        
        return [
            'total_recipients' => count($phoneNumbers),
            'results' => $results,
            'summary' => $this->generateSummary($results)
        ];
    }
    
    /**
     * Get nominees by performance criteria
     */
    public function getNomineesByPerformance($eventId, $performanceType, $options = [])
    {
        $limit = $options['limit'] ?? null;
        $categoryId = $options['category_id'] ?? null;
        $minVotes = $options['min_votes'] ?? 0;
        $maxVotes = $options['max_votes'] ?? null;
        
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.slug,
                c.phone,
                cat.name as category_name,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                COUNT(DISTINCT t.id) as total_transactions,
                COALESCE(SUM(t.amount), 0) as total_revenue,
                ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(v.quantity), 0) DESC) as position,
                ROUND(
                    (COALESCE(SUM(v.quantity), 0) * 100.0 / 
                     NULLIF((SELECT SUM(v2.quantity) FROM votes v2 WHERE v2.event_id = :event_id), 0)), 2
                ) as vote_percentage
            FROM contestants c
            LEFT JOIN contestant_categories cc ON c.id = cc.contestant_id
            LEFT JOIN categories cat ON cc.category_id = cat.id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions t ON v.transaction_id = t.id AND t.status = 'success'
            WHERE c.event_id = :event_id AND c.active = 1
        ";
        
        $params = ['event_id' => $eventId];
        
        if ($categoryId) {
            $sql .= " AND cat.id = :category_id";
            $params['category_id'] = $categoryId;
        }
        
        $sql .= " GROUP BY c.id, c.name, c.slug, c.phone, cat.name";
        
        // Apply performance filters
        $havingConditions = [];
        if ($minVotes > 0) {
            $havingConditions[] = "COALESCE(SUM(v.quantity), 0) >= :min_votes";
            $params['min_votes'] = $minVotes;
        }
        if ($maxVotes !== null) {
            $havingConditions[] = "COALESCE(SUM(v.quantity), 0) <= :max_votes";
            $params['max_votes'] = $maxVotes;
        }
        
        if (!empty($havingConditions)) {
            $sql .= " HAVING " . implode(' AND ', $havingConditions);
        }
        
        // Apply performance type ordering
        switch ($performanceType) {
            case 'top_performers':
                $sql .= " ORDER BY total_votes DESC";
                break;
            case 'low_performers':
                $sql .= " ORDER BY total_votes ASC";
                break;
            case 'no_votes':
                $sql .= " HAVING total_votes = 0 ORDER BY c.name";
                break;
            default:
                $sql .= " ORDER BY total_votes DESC";
        }
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $this->contestantModel->getDatabase()->select($sql, $params);
    }
    
    /**
     * Get voters by pattern
     */
    public function getVotersByPattern($eventId, $voterType, $options = [])
    {
        $limit = $options['limit'] ?? null;
        $minAmount = $options['min_amount'] ?? 0;
        $maxAmount = $options['max_amount'] ?? null;
        $daysSince = $options['days_since'] ?? null;
        
        $sql = "
            SELECT 
                t.msisdn as phone,
                COUNT(DISTINCT t.id) as total_transactions,
                SUM(v.quantity) as total_votes,
                SUM(t.amount) as total_amount,
                MAX(t.created_at) as last_vote_date,
                c.name as favorite_nominee
            FROM transactions t
            INNER JOIN votes v ON t.id = v.transaction_id
            INNER JOIN contestants c ON v.contestant_id = c.id
            WHERE t.event_id = :event_id 
            AND t.status = 'success' 
            AND t.msisdn IS NOT NULL 
            AND t.msisdn != ''
        ";
        
        $params = ['event_id' => $eventId];
        
        if ($daysSince) {
            $sql .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL :days_since DAY)";
            $params['days_since'] = $daysSince;
        }
        
        $sql .= " GROUP BY t.msisdn, c.name";
        
        // Apply voter type filters
        $havingConditions = [];
        if ($minAmount > 0) {
            $havingConditions[] = "SUM(t.amount) >= :min_amount";
            $params['min_amount'] = $minAmount;
        }
        if ($maxAmount !== null) {
            $havingConditions[] = "SUM(t.amount) <= :max_amount";
            $params['max_amount'] = $maxAmount;
        }
        
        if (!empty($havingConditions)) {
            $sql .= " HAVING " . implode(' AND ', $havingConditions);
        }
        
        // Apply voter type ordering
        switch ($voterType) {
            case 'high_spenders':
                $sql .= " ORDER BY total_amount DESC";
                break;
            case 'frequent_voters':
                $sql .= " ORDER BY total_transactions DESC";
                break;
            case 'recent_voters':
                $sql .= " ORDER BY last_vote_date DESC";
                break;
            case 'inactive_voters':
                $sql .= " ORDER BY last_vote_date ASC";
                break;
            default:
                $sql .= " ORDER BY total_amount DESC";
        }
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $this->transactionModel->getDatabase()->select($sql, $params);
    }
    
    /**
     * Generate summary of bulk SMS results
     */
    private function generateSummary($results)
    {
        $total = count($results);
        $successful = count(array_filter($results, function($r) { return $r['success']; }));
        $failed = $total - $successful;
        
        return [
            'total_sent' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0
        ];
    }
    
    /**
     * Get available recipient groups for an event
     */
    public function getAvailableGroups($eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            throw new \Exception('Event not found');
        }
        
        // Get nominee statistics
        $nomineeStats = $this->getNomineeStats($eventId);
        
        // Get voter statistics
        $voterStats = $this->getVoterStats($eventId);
        
        return [
            'event' => $event,
            'nominee_groups' => [
                'all_nominees' => [
                    'name' => 'All Nominees',
                    'count' => $nomineeStats['total_nominees'],
                    'description' => 'All active nominees in this event'
                ],
                'top_performers' => [
                    'name' => 'Top Performers',
                    'count' => min(10, $nomineeStats['nominees_with_votes']),
                    'description' => 'Top 10 nominees by vote count'
                ],
                'low_performers' => [
                    'name' => 'Low Performers',
                    'count' => $nomineeStats['nominees_with_votes'],
                    'description' => 'Nominees with below-average votes'
                ],
                'no_votes' => [
                    'name' => 'No Votes Yet',
                    'count' => $nomineeStats['nominees_without_votes'],
                    'description' => 'Nominees who haven\'t received any votes'
                ]
            ],
            'voter_groups' => [
                'all_voters' => [
                    'name' => 'All Voters',
                    'count' => $voterStats['unique_voters'],
                    'description' => 'All users who have voted in this event'
                ],
                'high_spenders' => [
                    'name' => 'High Spenders',
                    'count' => $voterStats['high_spenders'],
                    'description' => 'Voters who spent above average amount'
                ],
                'frequent_voters' => [
                    'name' => 'Frequent Voters',
                    'count' => $voterStats['frequent_voters'],
                    'description' => 'Voters with multiple transactions'
                ],
                'recent_voters' => [
                    'name' => 'Recent Voters',
                    'count' => $voterStats['recent_voters'],
                    'description' => 'Voters who voted in the last 7 days'
                ]
            ]
        ];
    }
    
    /**
     * Get nominee statistics
     */
    private function getNomineeStats($eventId)
    {
        $sql = "
            SELECT 
                COUNT(c.id) as total_nominees,
                COUNT(CASE WHEN v.contestant_id IS NOT NULL THEN 1 END) as nominees_with_votes,
                COUNT(CASE WHEN v.contestant_id IS NULL THEN 1 END) as nominees_without_votes
            FROM contestants c
            LEFT JOIN votes v ON c.id = v.contestant_id
            WHERE c.event_id = :event_id AND c.active = 1
        ";
        
        return $this->contestantModel->getDatabase()->selectOne($sql, ['event_id' => $eventId]);
    }
    
    /**
     * Get voter statistics
     */
    private function getVoterStats($eventId)
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT t.msisdn) as unique_voters,
                COUNT(DISTINCT CASE WHEN t.amount > avg_amount.avg THEN t.msisdn END) as high_spenders,
                COUNT(DISTINCT CASE WHEN voter_counts.transaction_count > 1 THEN t.msisdn END) as frequent_voters,
                COUNT(DISTINCT CASE WHEN t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN t.msisdn END) as recent_voters
            FROM transactions t
            CROSS JOIN (
                SELECT AVG(amount) as avg 
                FROM transactions 
                WHERE event_id = :event_id1 AND status = 'success'
            ) avg_amount
            LEFT JOIN (
                SELECT msisdn, COUNT(*) as transaction_count
                FROM transactions 
                WHERE event_id = :event_id2 AND status = 'success'
                GROUP BY msisdn
            ) voter_counts ON t.msisdn = voter_counts.msisdn
            WHERE t.event_id = :event_id3 
            AND t.status = 'success' 
            AND t.msisdn IS NOT NULL 
            AND t.msisdn != ''
        ";
        
        return $this->transactionModel->getDatabase()->selectOne($sql, [
            'event_id1' => $eventId,
            'event_id2' => $eventId,
            'event_id3' => $eventId
        ]);
    }
}
