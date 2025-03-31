<?php
use PHPUnit\Framework\TestCase;

class ConsultaTest extends TestCase {
    protected $conn;

    protected function setUp(): void {
        $this->conn = new PDO("mysql:host=localhost;dbname=biblioteca", "root", "");
    }

    public function testConsultaLivros() {
        // Teste para consultar livros
        $stmt = $this->conn->query("SELECT * FROM livros");
        $livros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertIsArray($livros);
    }
} 