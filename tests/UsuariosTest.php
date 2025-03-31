<?php
use PHPUnit\Framework\TestCase;

class UsuariosTest extends TestCase {
    protected $conn;

    protected function setUp(): void {
        $this->conn = new PDO("mysql:host=localhost;dbname=biblioteca", "root", "");
    }

    public function testAdicionarUsuario() {
        // Teste para adicionar um usuário
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $result = $stmt->execute(['Usuario Teste', 'usuario@teste.com', password_hash('senha123', PASSWORD_DEFAULT)]);
        $this->assertTrue($result);
    }

    public function testBuscarUsuario() {
        // Teste para buscar um usuário
        $stmt = $this->conn->query("SELECT * FROM usuarios WHERE email = 'usuario@teste.com'");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($usuario);
    }

    protected function tearDown(): void {
        // Limpar o banco de dados após os testes
        $this->conn->exec("DELETE FROM usuarios WHERE email = 'usuario@teste.com'");
    }
} 