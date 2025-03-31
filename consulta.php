<?php
require_once 'includes/header.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Processar busca
$where = [];
$params = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['titulo'])) {
        $where[] = "l.titulo LIKE ?";
        $params[] = "%{$_GET['titulo']}%";
    }
    
    if (!empty($_GET['autor'])) {
        $where[] = "l.autor LIKE ?";
        $params[] = "%{$_GET['autor']}%";
    }
    
    if (!empty($_GET['isbn'])) {
        $where[] = "l.isbn LIKE ?";
        $params[] = "%{$_GET['isbn']}%";
    }
    
    if (!empty($_GET['editora'])) {
        $where[] = "l.editora LIKE ?";
        $params[] = "%{$_GET['editora']}%";
    }
    
    if (isset($_GET['disponivel']) && $_GET['disponivel'] == '1') {
        $where[] = "l.quantidade > 0";
    }
}

// Construir query
$sql = "SELECT l.*, 
        (SELECT COUNT(*) FROM emprestimos e WHERE e.livro_id = l.id AND e.status = 'emprestado') as emprestados
        FROM livros l";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY l.titulo";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll();
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Consulta de Livros</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo isset($_GET['titulo']) ? htmlspecialchars($_GET['titulo']) : ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="autor" class="form-label">Autor</label>
                <input type="text" class="form-control" id="autor" name="autor" value="<?php echo isset($_GET['autor']) ? htmlspecialchars($_GET['autor']) : ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo isset($_GET['isbn']) ? htmlspecialchars($_GET['isbn']) : ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="editora" class="form-label">Editora</label>
                <input type="text" class="form-control" id="editora" name="editora" value="<?php echo isset($_GET['editora']) ? htmlspecialchars($_GET['editora']) : ''; ?>">
            </div>
            
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input type="checkbox" class="form-check-input" id="disponivel" name="disponivel" value="1" <?php echo isset($_GET['disponivel']) && $_GET['disponivel'] == '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disponivel">Apenas disponíveis</label>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="consulta.php" class="btn btn-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Editora</th>
                    <th>ISBN</th>
                    <th>Ano</th>
                    <th>Quantidade</th>
                    <th>Emprestados</th>
                    <th>Disponíveis</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livros as $livro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($livro['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($livro['autor']); ?></td>
                        <td><?php echo htmlspecialchars($livro['editora']); ?></td>
                        <td><?php echo htmlspecialchars($livro['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($livro['ano_publicacao']); ?></td>
                        <td><?php echo htmlspecialchars($livro['quantidade']); ?></td>
                        <td><?php echo $livro['emprestados']; ?></td>
                        <td><?php echo $livro['quantidade'] - $livro['emprestados']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>