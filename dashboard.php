<?php
require_once 'includes/header.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar se usuário é admin
//if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] != "admin") {
//    header('Location: index.php');
//    exit;
//}

// Buscar estatísticas
$stmt = $conn->query("SELECT COUNT(*) as total FROM livros");
$total_livros = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM emprestimos WHERE status = 'emprestado'");
$livros_emprestados = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM emprestimos WHERE status = 'atrasado'");
$livros_atrasados = $stmt->fetch()['total'];
?>

<h2>Dashboard</h2>
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Livros</h5>
                <p class="card-text display-4"><?php echo $total_livros; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Usuários</h5>
                <p class="card-text display-4"><?php echo $total_usuarios; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Livros Emprestados</h5>
                <p class="card-text display-4"><?php echo $livros_emprestados; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Livros Atrasados</h5>
                <p class="card-text display-4"><?php echo $livros_atrasados; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Últimos Empréstimos</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Livro</th>
                            <th>Usuário</th>
                            <th>Data Empréstimo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->query("
                            SELECT e.*, l.titulo, u.nome as usuario_nome 
                            FROM emprestimos e 
                            JOIN livros l ON e.livro_id = l.id 
                            JOIN usuarios u ON e.usuario_id = u.id 
                            ORDER BY e.data_emprestimo DESC LIMIT 5
                        ");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['usuario_nome']) . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['data_emprestimo'])) . "</td>";
                            echo "<td>" . ucfirst($row['status']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Livros Mais Emprestados</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Livro</th>
                            <th>Autor</th>
                            <th>Total Empréstimos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->query("
                            SELECT l.titulo, l.autor, COUNT(e.id) as total_emprestimos 
                            FROM livros l 
                            LEFT JOIN emprestimos e ON l.id = e.livro_id 
                            GROUP BY l.id 
                            ORDER BY total_emprestimos DESC LIMIT 5
                        ");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['autor']) . "</td>";
                            echo "<td>" . $row['total_emprestimos'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>