<?php

namespace SmartCast\Models;

/**
 * Public self-nomination submissions.
 */
class Nomination extends BaseModel
{
    protected $table = 'nominations';
    protected $fillable = [
        'tenant_id', 'event_id', 'category_id', 'category_name_snapshot',
        'name', 'bio', 'photo_url', 'email', 'phone', 'status',
        'contestant_id', 'reviewed_by', 'reviewed_at', 'rejection_reason',
        'submitter_ip', 'user_agent'
    ];

    public function getForTenant($tenantId, $eventId = null, $status = null)
    {
        $sql = "
            SELECT n.*, e.name as event_name, e.code as event_code,
                   cat.name as category_name, c.name as contestant_name
            FROM nominations n
            INNER JOIN events e ON n.event_id = e.id
            LEFT JOIN categories cat ON n.category_id = cat.id
            LEFT JOIN contestants c ON n.contestant_id = c.id
            WHERE n.tenant_id = :tenant_id
        ";
        $params = ['tenant_id' => $tenantId];

        if ($eventId) {
            $sql .= " AND n.event_id = :event_id";
            $params['event_id'] = $eventId;
        }

        if ($status) {
            $sql .= " AND n.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY FIELD(n.status, 'pending', 'approved', 'revoked', 'rejected'), n.created_at DESC";

        return $this->db->select($sql, $params);
    }

    public function findForTenant($id, $tenantId)
    {
        $sql = "
            SELECT n.*, e.name as event_name, e.code as event_code,
                   cat.name as category_name, c.name as contestant_name
            FROM nominations n
            INNER JOIN events e ON n.event_id = e.id
            LEFT JOIN categories cat ON n.category_id = cat.id
            LEFT JOIN contestants c ON n.contestant_id = c.id
            WHERE n.id = :id AND n.tenant_id = :tenant_id
        ";

        return $this->db->selectOne($sql, [
            'id' => $id,
            'tenant_id' => $tenantId
        ]);
    }

    public function countPendingForEvent($eventId)
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM nominations WHERE event_id = :event_id AND status = 'pending'",
            ['event_id' => $eventId]
        );

        return (int)($result['count'] ?? 0);
    }

    public function hasDuplicatePendingOrApproved($eventId, $categoryId, $name, $email = null, $phone = null)
    {
        $params = [
            'event_id' => $eventId,
            'category_id' => $categoryId,
            'name' => strtolower(trim($name))
        ];

        $checks = ["LOWER(TRIM(name)) = :name"];

        if ($email) {
            $checks[] = "LOWER(TRIM(email)) = :email";
            $params['email'] = strtolower(trim($email));
        }

        if ($phone) {
            $checks[] = "REPLACE(REPLACE(phone, ' ', ''), '-', '') = :phone";
            $params['phone'] = str_replace([' ', '-'], '', trim($phone));
        }

        $sql = "
            SELECT COUNT(*) as count
            FROM nominations
            WHERE event_id = :event_id
            AND category_id = :category_id
            AND status IN ('pending', 'approved')
            AND (" . implode(' OR ', $checks) . ")
        ";

        $result = $this->db->selectOne($sql, $params);

        return (int)($result['count'] ?? 0) > 0;
    }

    public function approveAsContestant($id, $reviewedBy = null, $categoryId = null)
    {
        $startedTransaction = !$this->db->inTransaction();

        try {
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            $nomination = $this->find($id);
            if (!$nomination) {
                throw new \Exception('Nomination not found');
            }

            if (!in_array($nomination['status'], ['pending', 'rejected', 'revoked', 'approved'], true)) {
                throw new \Exception('This nomination cannot be approved');
            }

            $targetCategoryId = $categoryId ?: $nomination['category_id'];
            $category = $this->db->selectOne(
                "SELECT * FROM categories WHERE id = :id AND event_id = :event_id",
                [
                    'id' => $targetCategoryId,
                    'event_id' => $nomination['event_id']
                ]
            );

            if (!$category) {
                throw new \Exception('Please choose a valid category before approving this nomination');
            }

            $contestantModel = new Contestant();
            $contestantId = $nomination['contestant_id'] ?? null;

            if ($contestantId) {
                $existingContestant = $contestantModel->find($contestantId);
                if (!$existingContestant || (int)$existingContestant['tenant_id'] !== (int)$nomination['tenant_id']) {
                    $contestantId = null;
                }
            }

            $contestantData = [
                'tenant_id' => $nomination['tenant_id'],
                'event_id' => $nomination['event_id'],
                'name' => $nomination['name'],
                'bio' => $nomination['bio'] ?? '',
                'display_order' => 0,
                'active' => 1,
                'created_by' => $reviewedBy
            ];

            if (!empty($nomination['photo_url'])) {
                $contestantData['image_url'] = $nomination['photo_url'];
            }

            if ($contestantId) {
                $contestantModel->update($contestantId, $contestantData);
            } else {
                $contestantData['contestant_code'] = $contestantModel->generateContestantCode($nomination['tenant_id'], $nomination['event_id']);
                $contestantId = $contestantModel->create($contestantData);

                if (!$contestantId) {
                    throw new \Exception('Failed to create contestant');
                }
            }

            $contestantCategoryModel = new ContestantCategory();
            $this->db->update(
                'contestant_categories',
                ['active' => 0],
                'contestant_id = :contestant_id',
                ['contestant_id' => $contestantId]
            );
            $contestantCategoryModel->assignContestantToCategory($contestantId, $targetCategoryId);

            $this->update($id, [
                'status' => 'approved',
                'contestant_id' => $contestantId,
                'category_id' => $targetCategoryId,
                'category_name_snapshot' => $category['name'],
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => null
            ]);

            if ($startedTransaction) {
                $this->db->commit();
            }

            return $contestantId;
        } catch (\Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    public function reject($id, $reviewedBy, $reason)
    {
        return $this->update($id, [
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ]);
    }

    public function revokeApproval($id, $reviewedBy, $reason = 'Revoked by organizer')
    {
        $startedTransaction = !$this->db->inTransaction();

        try {
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            $nomination = $this->find($id);
            if (!$nomination) {
                throw new \Exception('Nomination not found');
            }

            if ($nomination['status'] !== 'approved') {
                throw new \Exception('Only approved nominations can be revoked');
            }

            if (!empty($nomination['contestant_id'])) {
                $this->db->update(
                    'contestants',
                    ['active' => 0],
                    'id = :id',
                    ['id' => $nomination['contestant_id']]
                );
                $this->db->update(
                    'contestant_categories',
                    ['active' => 0],
                    'contestant_id = :contestant_id',
                    ['contestant_id' => $nomination['contestant_id']]
                );
            }

            $this->update($id, [
                'status' => 'revoked',
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason
            ]);

            if ($startedTransaction) {
                $this->db->commit();
            }

            return true;
        } catch (\Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    public function updateDetails($id, $data)
    {
        $startedTransaction = !$this->db->inTransaction();

        try {
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            $nomination = $this->find($id);
            if (!$nomination) {
                throw new \Exception('Nomination not found');
            }

            $categoryId = (int)($data['category_id'] ?? $nomination['category_id']);
            $category = $this->db->selectOne(
                "SELECT * FROM categories WHERE id = :id AND event_id = :event_id",
                [
                    'id' => $categoryId,
                    'event_id' => $nomination['event_id']
                ]
            );

            if (!$category) {
                throw new \Exception('Please choose a valid category for this event');
            }

            $updateData = [
                'name' => trim($data['name'] ?? $nomination['name']),
                'bio' => trim($data['bio'] ?? ''),
                'email' => trim($data['email'] ?? ''),
                'phone' => trim($data['phone'] ?? ''),
                'category_id' => $categoryId,
                'category_name_snapshot' => $category['name']
            ];

            if (isset($data['photo_url'])) {
                $updateData['photo_url'] = $data['photo_url'];
            }

            if ($updateData['name'] === '') {
                throw new \Exception('Contestant name is required');
            }

            $this->update($id, $updateData);

            if (!empty($nomination['contestant_id'])) {
                $contestantData = [
                    'name' => $updateData['name'],
                    'bio' => $updateData['bio']
                ];

                if (isset($updateData['photo_url']) && $updateData['photo_url'] !== '') {
                    $contestantData['image_url'] = $updateData['photo_url'];
                }

                $this->db->update(
                    'contestants',
                    $contestantData,
                    'id = :id AND tenant_id = :tenant_id',
                    [
                        'id' => $nomination['contestant_id'],
                        'tenant_id' => $nomination['tenant_id']
                    ]
                );

                if ($nomination['status'] === 'approved' && (int)$nomination['category_id'] !== $categoryId) {
                    $this->db->update(
                        'contestant_categories',
                        ['active' => 0],
                        'contestant_id = :contestant_id',
                        ['contestant_id' => $nomination['contestant_id']]
                    );

                    $contestantCategoryModel = new ContestantCategory();
                    $contestantCategoryModel->assignContestantToCategory($nomination['contestant_id'], $categoryId);
                }
            }

            if ($startedTransaction) {
                $this->db->commit();
            }

            return true;
        } catch (\Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }
}
