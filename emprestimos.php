<?php
require_once 'includes/header.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Processar formulário de empréstimo/devolução
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'emprestar') {
            $livro_id = filter_input(INPUT_POST, 'livro_id', FILTER_VALIDATE_INT);
            $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
            $data_emprestimo = date('Y-m-d');
            $data_devolucao_prevista = date('Y-m-d', strtotime('+15 days')); // Prazo de 15 dias

            // Verificar se o livro está disponível
            $stmt = $conn->prepare("SELECT quantidade FROM livros WHERE id = ?");
            $stmt->execute([$livro_id]);
            $livro = $stmt->fetch();

            if ($livro['quantidade'] > 0) {
                // Registrar empréstimo
                $stmt = $conn->prepare("INSERT INTO emprestimos (livro_id, usuario_id, data_emprestimo, data_devolucao_prevista) VALUES (?, ?, ?, ?)");
                $stmt->execute([$livro_id, $usuario_id, $data_emprestimo, $data_devolucao_prevista]);

                // Atualizar quantidade do livro
                $stmt = $conn->prepare("UPDATE livros SET quantidade = quantidade - 1 WHERE id = ?");
                $stmt->execute([$livro_id]);
            }
        } elseif ($_POST['action'] == 'devolver') {
            $emprestimo_id = filter_input(INPUT_POST, 'emprestimo_id', FILTER_VALIDATE_INT);
            $livro_id = filter_input(INPUT_POST, 'livro_id', FILTER_VALIDATE_INT);
            $data_devolucao = date('Y-m-d');

            // Registrar devolução
            $stmt = $conn->prepare("UPDATE emprestimos SET data_devolucao_real = ?, status = 'devolvido' WHERE id = ?");
            $stmt->execute([$data_devolucao, $emprestimo_id]);

            // Atualizar quantidade do livro
            $stmt = $conn->prepare("UPDATE livros SET quantidade = quantidade + 1 WHERE id = ?");
            $stmt->execute([$livro_id]);
        }
        header('Location: emprestimos.php');
        exit;
    }
}

// Atualizar status dos empréstimos atrasados
$stmt = $conn->query("
    UPDATE emprestimos 
    SET status = 'atrasado' 
    WHERE status = 'emprestado' 
    AND data_devolucao_prevista < CURRENT_DATE
");

// Buscar empréstimos para exibição
$stmt = $conn->query("
    SELECT e.*, l.titulo, l.autor, u.nome as usuario_nome 
    FROM emprestimos e 
    JOIN livros l ON e.livro_id = l.id 
    JOIN usuarios u ON e.usuario_id = u.id 
    ORDER BY e.data_emprestimo DESC
");
$emprestimos = $stmt->fetchAll();

// Buscar livros disponíveis para empréstimo
$stmt = $conn->query("SELECT * FROM livros WHERE quantidade > 0 ORDER BY titulo");
$livros_disponiveis = $stmt->fetchAll();

// Buscar usuários para empréstimo
$stmt = $conn->query("SELECT * FROM usuarios ORDER BY nome");
$usuarios = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gerenciamento de Empréstimos</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#emprestimoModal">
        <i class="fas fa-plus"></i> Novo Empréstimo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Usuário</th>
                    <th>Data Empréstimo</th>
                    <th>Data Prevista</th>
                    <th>Data Devolução</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprestimos as $emprestimo): ?>
                    <tr class="<?php echo $emprestimo['status'] == 'atrasado' ? 'table-danger' : ''; ?>">
                        <td><?php echo htmlspecialchars($emprestimo['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($emprestimo['usuario_nome']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($emprestimo['data_emprestimo'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($emprestimo['data_devolucao_prevista'])); ?></td>
                        <td><?php echo $emprestimo['data_devolucao_real'] ? date('d/m/Y', strtotime($emprestimo['data_devolucao_real'])) : '-'; ?></td>
                        <td><?php echo ucfirst($emprestimo['status']); ?></td>
                        <td>
                            <?php if ($emprestimo['status'] == 'emprestado' || $emprestimo['status'] == 'atrasado'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="devolver">
                                    <input type="hidden" name="emprestimo_id" value="<?php echo $emprestimo['id']; ?>">
                                    <input type="hidden" name="livro_id" value="<?php echo $emprestimo['livro_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i> Devolver
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Empréstimo -->
<div class="modal fade" id="emprestimoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Empréstimo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="emprestar">
                    
                    <div class="mb-3">
                        <label for="livro_id" class="form-label">Livro</label>
                        <select class="form-select" id="livro_id" name="livro_id" required>
                            <option value="">Selecione um livro</option>
                            <?php foreach ($livros_disponiveis as $livro): ?>
                                <option value="<?php echo $livro['id']; ?>">
                                    <?php echo htmlspecialchars($livro['titulo']); ?> - 
                                    <?php echo htmlspecialchars($livro['autor']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="usuario_id" class="form-label">Usuário</label>
                        <select class="form-select" id="usuario_id" name="usuario_id" required>
                            <option value="">Selecione um usuário</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['id']; ?>">
                                    <?php echo htmlspecialchars($usuario['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Emprestar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>