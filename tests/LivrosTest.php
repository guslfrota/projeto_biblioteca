<?php
use PHPUnit\Framework\TestCase;

class LivrosTest extends TestCase {
    protected $conn;

    protected function setUp(): void {
        // Configurar a conexão com o banco de dados para os testes
        $this->conn = new PDO("mysql:host=localhost;dbname=biblioteca", "root", "");
    }

    public function testAdicionarLivro() {
        // Teste para adicionar um livro
        $stmt = $this->conn->prepare("INSERT INTO livros (titulo, autor, editora, isbn, ano_publicacao, quantidade) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute(['Teste Livro', 'Autor Teste', 'Editora Teste', '1234567890123', 2023, 5]);
        $this->assertTrue($result);
    }

    public function testBuscarLivro() {
        // Teste para buscar um livro
        $stmt = $this->conn->query("SELECT * FROM livros WHERE titulo = 'Teste Livro'");
        $livro = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($livro);
    }

    protected function tearDown(): void {
        // Limpar o banco de dados após os testes
        $this->conn->exec("DELETE FROM livros WHERE titulo = 'Teste Livro'");
    }
} 