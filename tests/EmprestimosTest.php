<?php
use PHPUnit\Framework\TestCase;

class EmprestimosTest extends TestCase {
    protected $conn;

    protected function setUp(): void {
        $this->conn = new PDO("mysql:host=localhost;dbname=biblioteca", "root", "");
    }

    public function testRegistrarEmprestimo() {
        // Teste para registrar um empréstimo
        $stmt = $this->conn->prepare("INSERT INTO emprestimos (livro_id, usuario_id, data_emprestimo, status) VALUES (?, ?, NOW(), 'emprestado')");
        $result = $stmt->execute([1, 1]); // Supondo que os IDs 1 existam
        $this->assertTrue($result);
    }

    protected function tearDown(): void {
        // Limpar o banco de dados após os testes
        $this->conn->exec("DELETE FROM emprestimos WHERE livro_id = 1 AND usuario_id = 1");
    }
} 