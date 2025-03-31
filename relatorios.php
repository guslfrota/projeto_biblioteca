<?php
require_once 'includes/header.php';
require_once 'vendor/autoload.php'; // Requer o TCPDF

// Verificar se usuário está logado
//if (!isset($_SESSION['usuario_id'])) {
//    header('Location: login.php');
//    exit;
//}

// Verificar se usuário é admin
//if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] != "admin") {
//    header('Location: index.php');
//    exit;
//}

// Gerar relatório PDF
if (isset($_GET['tipo']) && isset($_GET['gerar_pdf'])) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configurar documento
    $pdf->SetCreator('Sistema de Biblioteca');
    $pdf->SetAuthor('Biblioteca');
    $pdf->SetTitle('Relatório de Biblioteca');
    
    // Remover cabeçalho/rodapé padrão
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Adicionar página
    $pdf->AddPage();
    
    // Configurar fonte
    $pdf->SetFont('helvetica', '', 12);
    
    // Título
    $pdf->Cell(0, 10, 'Relatório de Biblioteca', 0, 1, 'C');
    $pdf->Ln(10);
    
    switch ($_GET['tipo']) {
        case 'livros_emprestados':
            // Buscar livros emprestados
            $stmt = $conn->query("
                SELECT l.titulo, l.autor, u.nome as usuario_nome, 
                       e.data_emprestimo, e.data_devolucao_prevista, e.status
                FROM emprestimos e
                JOIN livros l ON e.livro_id = l.id
                JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.status IN ('emprestado', 'atrasado')
                ORDER BY e.data_emprestimo DESC
            ");
            
            $pdf->Cell(0, 10, 'Livros Emprestados', 0, 1, 'L');
            $pdf->Ln(5);
            
            // Cabeçalho da tabela
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(60, 7, 'Livro', 1, 0, 'L', true);
            $pdf->Cell(40, 7, 'Usuário', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Data Empréstimo', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Data Prevista', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Status', 1, 1, 'L', true);
            
            // Dados
            while ($row = $stmt->fetch()) {
                $pdf->Cell(60, 6, $row['titulo'], 1);
                $pdf->Cell(40, 6, $row['usuario_nome'], 1);
                $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['data_emprestimo'])), 1);
                $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['data_devolucao_prevista'])), 1);
                $pdf->Cell(30, 6, ucfirst($row['status']), 1, 1);
            }
            break;
            
        case 'livros_atrasados':
            // Buscar livros atrasados
            $stmt = $conn->query("
                SELECT l.titulo, l.autor, u.nome as usuario_nome, 
                       e.data_emprestimo, e.data_devolucao_prevista
                FROM emprestimos e
                JOIN livros l ON e.livro_id = l.id
                JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.status = 'atrasado'
                ORDER BY e.data_devolucao_prevista ASC
            ");
            
            $pdf->Cell(0, 10, 'Livros Atrasados', 0, 1, 'L');
            $pdf->Ln(5);
            
            // Cabeçalho da tabela
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(60, 7, 'Livro', 1, 0, 'L', true);
            $pdf->Cell(40, 7, 'Usuário', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Data Empréstimo', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Data Prevista', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Dias Atraso', 1, 1, 'L', true);
            
            // Dados
            while ($row = $stmt->fetch()) {
                $dias_atraso = floor((time() - strtotime($row['data_devolucao_prevista'])) / (60 * 60 * 24));
                $pdf->Cell(60, 6, $row['titulo'], 1);
                $pdf->Cell(40, 6, $row['usuario_nome'], 1);
                $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['data_emprestimo'])), 1);
                $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['data_devolucao_prevista'])), 1);
                $pdf->Cell(30, 6, $dias_atraso . ' dias', 1, 1);
            }
            break;
            
        case 'livros_mais_emprestados':
            // Buscar livros mais emprestados
            $stmt = $conn->query("
                SELECT l.titulo, l.autor, COUNT(e.id) as total_emprestimos
                FROM livros l
                LEFT JOIN emprestimos e ON l.id = e.livro_id
                GROUP BY l.id
                ORDER BY total_emprestimos DESC
                LIMIT 10
            ");
            
            $pdf->Cell(0, 10, 'Top 10 - Livros Mais Emprestados', 0, 1, 'L');
            $pdf->Ln(5);
            
            // Cabeçalho da tabela
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(80, 7, 'Livro', 1, 0, 'L', true);
            $pdf->Cell(40, 7, 'Autor', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Total', 1, 1, 'L', true);
            
            // Dados
            while ($row = $stmt->fetch()) {
                $pdf->Cell(80, 6, $row['titulo'], 1);
                $pdf->Cell(40, 6, $row['autor'], 1);
                $pdf->Cell(30, 6, $row['total_emprestimos'], 1, 1);
            }
            break;
    }
    
    // Gerar PDF
    ob_end_clean();
    $pdf->Output('relatorio.pdf', 'D');
    exit;
}

// Buscar estatísticas para exibição
$stmt = $conn->query("SELECT COUNT(*) as total FROM livros");
$total_livros = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM emprestimos WHERE status = 'emprestado'");
$livros_emprestados = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM emprestimos WHERE status = 'atrasado'");
$livros_atrasados = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $stmt->fetch()['total'];
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Estatísticas</h5>
    </div>
    <div class="card-body">
        <div class="row">
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
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total de Usuários</h5>
                        <p class="card-text display-4"><?php echo $total_usuarios; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Relatórios</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Livros Emprestados</h5>
                        <p class="card-text">Lista todos os livros atualmente emprestados.</p>
                        <a href="?tipo=livros_emprestados&gerar_pdf=1" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Livros Atrasados</h5>
                        <p class="card-text">Lista todos os livros com devolução atrasada.</p>
                        <a href="?tipo=livros_atrasados&gerar_pdf=1" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Livros Mais Emprestados</h5>
                        <p class="card-text">Lista os 10 livros mais emprestados.</p>
                        <a href="?tipo=livros_mais_emprestados&gerar_pdf=1" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>