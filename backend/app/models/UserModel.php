<?php
require_once __DIR__ . '/../core/Model.php';

class UserModel extends Model {
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT id, name, email, role, created_at FROM users WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }

    public function list(int $page, int $limit, string $search=''): array {
        $offset = ($page - 1) * $limit;
        if ($search !== '') {
            $like = "%$search%";
            $stmt = $this->db->prepare('SELECT COUNT(*) c FROM users WHERE name LIKE ? OR email LIKE ?');
            $stmt->bind_param('ss', $like, $like);
            $stmt->execute();
            $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);

            $stmt = $this->db->prepare('SELECT id, name, email, role, created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
            $stmt->bind_param('ssii', $like, $like, $limit, $offset);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $total = (int)($this->db->query('SELECT COUNT(*) c FROM users')->fetch_assoc()['c'] ?? 0);
            $stmt = $this->db->prepare('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?');
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return ['total'=>$total, 'items'=>$items];
    }

    public function create(string $name, string $email, string $role, ?string $password): int {
        $hash = $password !== null && $password !== ''
            ? password_hash($password, PASSWORD_DEFAULT)
            : password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, role, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('ssss', $name, $email, $role, $hash);
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function update(int $id, string $name, string $email, string $role, ?string $password): bool {
        if ($password !== null && $password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare('UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?');
            $stmt->bind_param('ssssi', $name, $email, $role, $hash, $id);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
            $stmt->bind_param('sssi', $name, $email, $role, $id);
        }
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id=?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
