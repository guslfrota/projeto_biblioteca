<?php
require_once 'includes/header.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Processar formulário de adição/edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' || $_POST['action'] == 'edit') {
            $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
            $autor = filter_input(INPUT_POST, 'autor', FILTER_SANITIZE_STRING);
            $editora = filter_input(INPUT_POST, 'editora', FILTER_SANITIZE_STRING);
            $isbn = filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_STRING);
            $ano_publicacao = filter_input(INPUT_POST, 'ano_publicacao', FILTER_VALIDATE_INT);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

            if ($_POST['action'] == 'add') {
                $stmt = $conn->prepare("INSERT INTO livros (titulo, autor, editora, isbn, ano_publicacao, quantidade) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titulo, $autor, $editora, $isbn, $ano_publicacao, $quantidade]);
            } else {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $stmt = $conn->prepare("UPDATE livros SET titulo = ?, autor = ?, editora = ?, isbn = ?, ano_publicacao = ?, quantidade = ? WHERE id = ?");
                $stmt->execute([$titulo, $autor, $editora, $isbn, $ano_publicacao, $quantidade, $id]);
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $stmt = $conn->prepare("DELETE FROM livros WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: livros.php');
        exit;
    }
}

// Processar filtros de busca
$where = [];
$params = [];
if (!empty($_GET['titulo'])) {
    $where[] = "titulo LIKE ?";
    $params[] = "%" . filter_input(INPUT_GET, 'titulo', FILTER_SANITIZE_STRING) . "%";
}
if (!empty($_GET['autor'])) {
    $where[] = "autor LIKE ?";
    $params[] = "%" . filter_input(INPUT_GET, 'autor', FILTER_SANITIZE_STRING) . "%";
}
if (!empty($_GET['editora'])) {
    $where[] = "editora LIKE ?";
    $params[] = "%" . filter_input(INPUT_GET, 'editora', FILTER_SANITIZE_STRING) . "%";
}
if (!empty($_GET['isbn'])) {
    $where[] = "isbn LIKE ?";
    $params[] = "%" . filter_input(INPUT_GET, 'isbn', FILTER_SANITIZE_STRING) . "%";
}
if (!empty($_GET['ano_publicacao'])) {
    $where[] = "ano_publicacao = ?";
    $params[] = filter_input(INPUT_GET, 'ano_publicacao', FILTER_VALIDATE_INT);
}

$sql = "SELECT * FROM livros";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY titulo";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gerenciamento de Livros</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#livroModal">
            <i class="fas fa-plus"></i> Novo Livro
        </button>
</div>

<!-- Formulário de Busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="titulo" placeholder="Título" value="<?php echo htmlspecialchars($_GET['titulo'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="autor" placeholder="Autor" value="<?php echo htmlspecialchars($_GET['autor'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="editora" placeholder="Editora" value="<?php echo htmlspecialchars($_GET['editora'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="isbn" placeholder="ISBN" value="<?php echo htmlspecialchars($_GET['isbn'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="ano_publicacao" placeholder="Ano de Publicação" value="<?php echo htmlspecialchars($_GET['ano_publicacao'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
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
                    <th>Ações</th>
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
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" onclick="editarLivro(<?php echo htmlspecialchars(json_encode($livro)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="excluirLivro(<?php echo $livro['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Livro -->
<div class="modal fade" id="livroModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Livro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id" id="id">
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="autor" class="form-label">Autor</label>
                        <input type="text" class="form-control" id="autor" name="autor" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editora" class="form-label">Editora</label>
                        <input type="text" class="form-control" id="editora" name="editora">
                    </div>
                    
                    <div class="mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" class="form-control" id="isbn" name="isbn">
                    </div>
                    
                    <div class="mb-3">
                        <label for="ano_publicacao" class="form-label">Ano de Publicação</label>
                        <input type="number" class="form-control" id="ano_publicacao" name="ano_publicacao">
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" value="1" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulário de Exclusão -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function editarLivro(livro) {
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = livro.id;
    document.getElementById('titulo').value = livro.titulo;
    document.getElementById('autor').value = livro.autor;
    document.getElementById('editora').value = livro.editora;
    document.getElementById('isbn').value = livro.isbn;
    document.getElementById('ano_publicacao').value = livro.ano_publicacao;
    document.getElementById('quantidade').value = livro.quantidade;
    
    new bootstrap.Modal(document.getElementById('livroModal')).show();
}

function excluirLivro(id) {
    if (confirm('Tem certeza que deseja excluir este livro?')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Limpar formulário ao abrir modal para novo livro
document.getElementById('livroModal').addEventListener('show.bs.modal', function (event) {
    // Verifica se o botão que acionou o modal foi o "Novo Livro"
    if (event.relatedTarget && event.relatedTarget.classList.contains('btn-primary')) {
        document.getElementById('action').value = 'add';
        document.getElementById('id').value = '';
        document.getElementById('titulo').value = '';
        document.getElementById('autor').value = '';
        document.getElementById('editora').value = '';
        document.getElementById('isbn').value = '';
        document.getElementById('ano_publicacao').value = '';
        document.getElementById('quantidade').value = '1';
    }
    // Caso contrário, o modal foi aberto pela função editarLivro e os dados já foram preenchidos
});
</script>

<?php require_once 'includes/footer.php'; ?>