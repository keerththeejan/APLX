<?php
require_once __DIR__ . '/../core/Model.php';

class ShipmentModel extends Model {
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }

    public function list(int $page, int $limit, string $search='', bool $all=false): array {
        $offset = ($page - 1) * $limit;
        $where = '';
        $params = [];
        $types = '';
        if ($search !== '') {
            $where = " WHERE tracking_number LIKE ? OR receiver_name LIKE ? OR sender_name LIKE ? OR origin LIKE ? OR destination LIKE ?";
            $like = "%$search%";
            $params = [$like,$like,$like,$like,$like];
            $types = 'sssss';
        }
        $sqlTotal = 'SELECT COUNT(*) c FROM shipments' . $where;
        $stmt = $this->db->prepare($sqlTotal);
        if ($types) { $stmt->bind_param($types, ...$params); }
        $stmt->execute();
        $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        if ($all) {
            $sql = 'SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments' . $where . ' ORDER BY updated_at DESC';
            $stmt = $this->db->prepare($sql);
            if ($types) { $stmt->bind_param($types, ...$params); }
        } else {
            $sql = 'SELECT id, tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at, updated_at FROM shipments' . $where . ' ORDER BY updated_at DESC LIMIT ? OFFSET ?';
            $stmt = $this->db->prepare($sql);
            if ($types) {
                $types2 = $types . 'ii';
                $stmt->bind_param($types2, ...array_merge($params, [$limit, $offset]));
            } else {
                $stmt->bind_param('ii', $limit, $offset);
            }
        }
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return ['total'=>$total, 'items'=>$items];
    }

    public function create(string $sender, string $receiver, string $origin, string $destination, float $weight, ?float $price, string $status='Booked'): array {
        $tracking = strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
        $stmt = $this->db->prepare('INSERT INTO shipments (tracking_number, sender_name, receiver_name, origin, destination, weight, price, status, created_at) VALUES (?,?,?,?,?,?,?,?, NOW())');
        $stmt->bind_param('sssssdss', $tracking, $sender, $receiver, $origin, $destination, $weight, $price, $status);
        $stmt->execute();
        return ['id' => $this->db->insert_id, 'tracking_number' => $tracking];
    }

    public function update(int $id, array $fields): bool {
        // Load current to merge
        $cur = $this->db->prepare('SELECT receiver_name, origin, destination, status, price, weight FROM shipments WHERE id=?');
        $cur->bind_param('i', $id);
        $cur->execute();
        $old = $cur->get_result()->fetch_assoc();
        if (!$old) return false;
        $receiver = trim($fields['receiver_name'] ?? $old['receiver_name']);
        $origin = trim($fields['origin'] ?? $old['origin']);
        $destination = trim($fields['destination'] ?? $old['destination']);
        $status = trim($fields['status'] ?? $old['status']);
        $price = array_key_exists('price', $fields) ? ( ($fields['price'] === '' || $fields['price'] === null) ? null : floatval($fields['price']) ) : $old['price'];
        $weight = array_key_exists('weight', $fields) ? floatval($fields['weight']) : $old['weight'];
        $stmt = $this->db->prepare('UPDATE shipments SET receiver_name=?, origin=?, destination=?, status=?, price=?, weight=? WHERE id=?');
        $stmt->bind_param('sssssdi', $receiver, $origin, $destination, $status, $price, $weight, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM shipments WHERE id=?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
