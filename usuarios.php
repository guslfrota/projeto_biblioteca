<?php
require_once 'includes/header.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Processar formulário de adição/edição/exclusão
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add' || $_POST['action'] == 'edit') {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING);
        $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
        
        if ($_POST['action'] == 'add') {
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, endereco, telefone, email, senha, tipo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $endereco, $telefone, $email, $senha, $tipo]);
        } else {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!empty($_POST['senha'])) {
                $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, endereco = ?, telefone = ?, email = ?, senha = ?, tipo = ? WHERE id = ?");
                $stmt->execute([$nome, $endereco, $telefone, $email, $senha, $tipo, $id]);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, endereco = ?, telefone = ?, email = ?, tipo = ? WHERE id = ?");
                $stmt->execute([$nome, $endereco, $telefone, $email, $tipo, $id]);
            }
        }
    } elseif ($_POST['action'] == 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        // Verificar se é o usuário atualmente logado
        if ($id == $_SESSION['usuario_id']) {
            echo "<script>alert('Não é possível excluir o usuário atualmente logado!'); window.location.href='usuarios.php';</script>";
            exit;
        }
        
        // Verificar se o usuário tem empréstimos pendentes
        $stmt = $conn->prepare("SELECT COUNT(*) FROM emprestimos WHERE usuario_id = ? AND data_devolucao_real IS NULL AND status != 'devolvido'");
        $stmt->execute([$id]);
        $emprestimosPendentes = $stmt->fetchColumn();
        
        if ($emprestimosPendentes > 0) {
            echo "<script>alert('Não é possível excluir este usuário pois ele possui empréstimos pendentes!'); window.location.href='usuarios.php';</script>";
            exit;
        }
        
        // Se passou pelas verificações, proceder com a exclusão
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: usuarios.php');
    exit;
}

// Buscar usuários para exibição
$stmt = $conn->query("SELECT * FROM usuarios ORDER BY nome");
$usuarios = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gerenciamento de Usuários</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
        <i class="fas fa-plus"></i> Novo Usuário
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Endereço</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['telefone']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['endereco']); ?></td>
                        <td><?php echo ucfirst($usuario['tipo']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="excluirUsuario(<?php echo $usuario['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Usuário -->
<div class="modal fade" id="usuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id" id="id">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha">
                        <small class="text-muted">Deixe em branco para manter a senha atual ao editar</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <textarea class="form-control" id="endereco" name="endereco" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="usuario">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
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
function editarUsuario(usuario) {
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = usuario.id;
    document.getElementById('nome').value = usuario.nome;
    document.getElementById('email').value = usuario.email;
    document.getElementById('telefone').value = usuario.telefone;
    document.getElementById('endereco').value = usuario.endereco;
    document.getElementById('tipo').value = usuario.tipo;
    
    new bootstrap.Modal(document.getElementById('usuarioModal')).show();
}

function excluirUsuario(id) {
    // Verificação inicial do usuário logado no client-side (opcional)
    if (id == <?php echo $_SESSION['usuario_id']; ?>) {
        alert('Não é possível excluir o usuário atualmente logado!');
        return;
    }
    
    if (confirm('Tem certeza que deseja excluir este usuário?')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Limpar formulário ao abrir modal para novo usuário
document.getElementById('usuarioModal').addEventListener('show.bs.modal', function (event) {
    if (event.relatedTarget && event.relatedTarget.classList.contains('btn-primary')) {
        document.getElementById('action').value = 'add';
        document.getElementById('id').value = '';
        document.getElementById('nome').value = '';
        document.getElementById('email').value = '';
        document.getElementById('senha').value = '';
        document.getElementById('telefone').value = '';
        document.getElementById('endereco').value = '';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>