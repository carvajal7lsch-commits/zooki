<?php

class PasswordReset
{
    private $conn;
    private $table = "password_resets";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function invalidateTokensForEmail(string $email): bool
    {
        $query = "UPDATE {$this->table} SET used = 1 WHERE email = :email AND used = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }

    public function deleteExpiredTokens(): bool
    {
        $query = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    public function createToken(?string $documento, string $email, string $tokenHash, string $expiresAt): int
    {
        $query = "INSERT INTO {$this->table} (usuario_documento, email, token_hash, expires_at) VALUES (:documento, :email, :token_hash, :expires_at)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token_hash', $tokenHash);
        $stmt->bindParam(':expires_at', $expiresAt);
        $stmt->execute();
        return (int) $this->conn->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function markTokenUsed(int $id): bool
    {
        $query = "UPDATE {$this->table} SET used = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
