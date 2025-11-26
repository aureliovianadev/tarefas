<?php
require_once 'conexao.php';

$status_msg = "";
$status_type = "";

/* =========================================
   1. CADASTRAR TAREFA
========================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_tarefa'])) {

    $tarefa = $conn->real_escape_string($_POST['tarefa']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $prazo = $_POST['prazo'];
    $prioridade = $conn->real_escape_string($_POST['prioridade']);

    $prazo_sql = empty($prazo) ? NULL : $prazo;

    $sql = "INSERT INTO tarefas (tarefa, descricao, prazo, prioridade) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("ssss", $tarefa, $descricao, $prazo_sql, $prioridade);

        if ($stmt->execute()) {
            $status_msg = "✅ Tarefa '$tarefa' cadastrada com sucesso!";
            $status_type = "success";
        } else {
            $status_msg = "❌ Erro ao cadastrar tarefa: " . $stmt->error;
            $status_type = "error";
        }
    }
}

/* =========================================
   2. CONCLUIR TAREFA
========================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['concluir_tarefa'])) {

    $id_tarefa = intval($_POST['id_tarefa']);

    $sql_update = "UPDATE tarefas SET status = 'Concluída' WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update) {
        $stmt_update->bind_param("i", $id_tarefa);

        if ($stmt_update->execute()) {
            $status_msg = "✅ Tarefa #$id_tarefa marcada como Concluída!";
            $status_type = "success";
        } else {
            $status_msg = "❌ Erro ao atualizar tarefa: " . $stmt_update->error;
            $status_type = "error";
        }
    }
}

/* =========================================
   3. EXCLUIR TAREFA
========================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_tarefa'])) {

    $id_tarefa = intval($_POST['id_tarefa']);

    $sql_delete = "DELETE FROM tarefas WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id_tarefa);

        if ($stmt_delete->execute()) {
            $status_msg = "✅ Tarefa #$id_tarefa excluída com sucesso!";
            $status_type = "success";
        } else {
            $status_msg = "❌ Erro ao excluir tarefa: " . $stmt_delete->error;
            $status_type = "error";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão de Tarefas (CRUD)</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ebee;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Formulário */
        form {
            display: grid;
            grid-template-columns: repeat(2, 1fr) 150px;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .form-group { grid-column: span 1; }
        .form-full { grid-column: span 2; }
        input[type="text"], input[type="date"], select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 5px;
        }

        button.submit-btn {
            grid-column: 3 / 4;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
            align-self: end;
            padding: 10px;
        }
        button.submit-btn:hover { background-color: #219653; }

        /* Mensagens */
        .status { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Tabela */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td { border: 1px solid #ddd; padding: 12px; }
        th { background-color: #34495e; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }

        .prioridade-Alta { background-color: #f4cccc; color: #cc0000; padding: 4px; border-radius: 4px; }
        .prioridade-Média { background-color: #fff2cc; color: #ff9900; padding: 4px; border-radius: 4px; }
        .prioridade-Baixa { background-color: #d9ead3; color: #38761d; padding: 4px; border-radius: 4px; }

        .status-Concluída { text-decoration: line-through; color: #7f8c8d; }
        .status-Pendente { font-weight: bold; color: #2980b9; }

        .actions-form { display: inline-block; }
        .actions-form button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
        .btn-concluir { background-color: #2ecc71; color: white; }
        .btn-excluir { background-color: #e74c3c; color: white; }

    </style>

</head>
<body>

<div class="header">
    <h1>📝 Gestão de Tarefas</h1>
</div>

<div class="container">

<?php if ($status_msg): ?>
    <div class="status <?= $status_type ?>">
        <?= $status_msg ?>
    </div>
<?php endif ?>

<h2>Adicionar Nova Tarefa</h2>

<form method="post">
    <div class="form-group">
        <label for="tarefa">Tarefa:</label>
        <input type="text" id="tarefa" name="tarefa" required>
    </div>

    <div class="form-group">
        <label for="prazo">Prazo:</label>
        <input type="date" id="prazo" name="prazo">
    </div>

    <div class="form-full">
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao"></textarea>
    </div>

    <div class="form-group">
        <label for="prioridade">Prioridade:</label>
        <select id="prioridade" name="prioridade">
            <option value="Baixa">Baixa</option>
            <option value="Média" selected>Média</option>
            <option value="Alta">Alta</option>
        </select>
    </div>

    <button type="submit" name="cadastrar_tarefa" class="submit-btn">CADASTRAR</button>
</form>

<h2>Lista de Tarefas</h2>

<?php
$sql_select = "SELECT id, tarefa, descricao, prazo, prioridade, status FROM tarefas 
ORDER BY status DESC, FIELD(prioridade, 'Alta', 'Média', 'Baixa'), data_criacao DESC";

$result = $conn->query($sql_select);

if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<thead><tr><th>ID</th><th>Tarefa</th><th>Prazo</th><th>Prioridade</th><th>Status</th><th>Ações</th></tr></thead>";
    echo "<tbody>";

    while ($row = $result->fetch_assoc()) {

        $status_class = "status-" . $row["status"];
        $prioridade_class = "prioridade-" . $row["prioridade"];

        echo "<tr>";
        echo "<td>".$row["id"]."</td>";
        echo "<td class='$status_class'><strong>".$row["tarefa"]."</strong><br><small>".$row["descricao"]."</small></td>";
        echo "<td>".($row["prazo"] ? date('d/m/Y', strtotime($row["prazo"])) : "—")."</td>";
        echo "<td><span class='$prioridade_class'>".$row["prioridade"]."</span></td>";
        echo "<td>".$row["status"]."</td>";

        echo "<td>";

        if ($row["status"] == "Pendente") {
            echo "<form method='post' class='actions-form'>
                    <input type='hidden' name='id_tarefa' value='".$row["id"]."'>
                    <button type='submit' name='concluir_tarefa' class='btn-concluir'>Concluir</button>
                  </form>";
        }

        echo "<form method='post' class='actions-form' onsubmit=\"return confirm('Excluir tarefa?');\">
                <input type='hidden' name='id_tarefa' value='".$row["id"]."'>
                <button type='submit' name='excluir_tarefa' class='btn-excluir'>Excluir</button>
              </form>";

        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
} else {
    echo "<p style='text-align:center;color:#777;'>Nenhuma tarefa cadastrada.</p>";
}

$conn->close();
?>
</div>
</body>
</html>
