<?php

$tarefas = []; 

$acao = 'recuperar';
require './tarefa_controller.php';


$ordenacao = ($_GET['ordenacao'] ?? '');


$filtro = ($_GET['filtro'] ?? 'escolha uma opcao');


$tarefasFiltradas = [];
if ($filtro === 'pendentes') {
    $tarefasFiltradas = array_filter($tarefas, function ($tarefa) {
        return $tarefa->status === 'pendente';
    });
} else if ($filtro === 'realizado') {
    $tarefasFiltradas = array_filter($tarefas, function ($tarefa) {
        return $tarefa->status === 'realizado';
    });
} else if ($filtro === 'ja passou o prazo') {
    $tarefasFiltradas = array_filter($tarefas, function ($tarefa) {
        return $tarefa->status === 'pendente' && strtotime($tarefa->prazo) < time();
    });
} else {
    $tarefasFiltradas = $tarefas;
}


if ($ordenacao === 'data_cadastrado') {
    usort($tarefasFiltradas, 'compararDatas');
} else if ($ordenacao === 'prioridade') {
    usort($tarefasFiltradas, 'compararPrioridades');
} else {
    
    usort($tarefasFiltradas, 'compararTarefas');
}

function compararTarefas($a, $b)
{
    return strcmp($a->tarefa, $b->tarefa);
}


function compararDatas($a, $b)
{
    return strtotime($a->data_cadastrado) - strtotime($b->data_cadastrado);
}


function compararPrioridades($a, $b)
{
    return intval($a->prioridade) - intval($b->prioridade);
}

function traduzPrioridade($prioridade)
{
    switch ($prioridade) {
        case 3:
            return 'Emergência';
        case 2:
            return 'Urgente';
        case 1:
            return 'Alta';
        default:
            return 'Sem presa';
    }
}

?>

<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>App Lista Tarefas</title>

    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <style>
        .notification-container {
            left: 20px;
            bottom: 20px;
            max-height: 650px; 
            width: 1300px;
            overflow-y: auto; 
            overflow-x: auto; 
            white-space: nowrap; 
            z-index: 9999;
        }

        .notification {
            width: 400px;

            background-color: #f44336;
            color: white;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            margin-bottom: 10px;
            padding: 10px;
            cursor: pointer;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
            font-family: Arial, sans-serif;
        }

        .notification.show {
            opacity: 1;
        }
    </style>

    <script>
        function editar(id, txt_tarefa) {

            let form = document.createElement('form')
            form.action = 'tarefa_controller.php?acao=atualizar'
            form.method = 'post'
            form.className = 'row'

    
            let inputTarefa = document.createElement('input')
            inputTarefa.type = 'text'
            inputTarefa.name = 'tarefa'
            inputTarefa.className = 'col-9 form-control'
            inputTarefa.value = txt_tarefa

     
            let inputId = document.createElement('input')
            inputId.type = 'hidden'
            inputId.name = 'id'
            inputId.value = id

    
            let button = document.createElement('button')
            button.type = 'submit'
            button.className = 'col-3 btn btn-info'
            button.innerHTML = 'Atualizar'

          
            form.appendChild(inputTarefa)

            
            form.appendChild(inputId)

            
            form.appendChild(button)

            
            let tarefa = document.getElementById('tarefa_' + id)

            
            tarefa.innerHTML = ''

            
            tarefa.insertBefore(form, tarefa.firstChild)

        }

        function remover(id) {
            location.href = 'todas_tarefas.php?acao=remover&id=' + id;
        }

        function marcarRealizada(id) {
            location.href = 'todas_tarefas.php?acao=marcarRealizada&id=' + id;
        }

        function arquivar(id) {
            location.href = 'todas_tarefas.php?acao=arquivar&id=' + id;
        }
    </script>

    
</head>

<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="img/logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
                App Lista Tarefas
            </a>
        </div>
    </nav>

    <div class="container app">
        <div class="row">
            <div class="col-sm-3 menu">
                <ul class="list-group">
                    <li class="list-group-item"><a href="index.php">Tarefas pendentes</a></li>
                    <li class="list-group-item"><a href="nova_tarefa.php">Nova tarefa</a></li>
                    <li class="list-group-item active"><a href="#">Todas tarefas</a></li>
                    <li class="list-group-item"><a href="arquivadas.php">Tarefas Arquivadas</a></li>

                </ul>
            </div>

            <div class="col-sm-9">
                <div class="container pagina">
                    <div class="row">
                        <div class="col">
                            <h4>Todas tarefas</h4>
                            <hr />


                            <form id="formFiltro" method="get">
                                <label for="filtro">Filtrar por:</label>
                                <select id="filtro" name="filtro" onchange="this.form.submit()">
                                    <option value="todas" <?php echo $filtro === 'todas' ? 'selected' : ''; ?>>Todas</option>
                                    <option value="pendentes" <?php echo $filtro === 'pendentes' ? 'selected' : ''; ?>>Pendentes</option>
                                    <option value="realizado" <?php echo $filtro === 'realizado' ? 'selected' : ''; ?>>Realizadas</option>
                                    <option value="vencidas" <?php echo $filtro === 'vencidas' ? 'selected' : ''; ?>>Ja pasou o prazo</option>
                                </select>

                                <label for="ordenacao">Ordenar por:</label>
                                <label for="ordenacao">Ordenar por:</label>
                                <select id="ordenacao" name="ordenacao" onchange="this.form.submit()">
                                    <option value="tarefa" <?php echo $ordenacao === 'tarefa' ? 'selected' : ''; ?>>Alfabética</option>
                                    <option value="categoria" <?php echo $ordenacao === 'categoria' ? 'selected' : ''; ?>>Categoria</option>
                                    <option value="prioridade" <?php echo $ordenacao === 'prioridade' ? 'selected' : ''; ?>>Prioridade</option>
                                    <option value="data_cadastrado" <?php echo $ordenacao === 'data_cadastrado' ? 'selected' : ''; ?>>Data de Cadastro</option>
                                </select>
                            </form>

                            <br />

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Tarefa</th>
                                        <th scope="col">Categoria</th>
                                        <th scope="col">Prioridade</th>

                                        <th scope="col">Prazo</th>
                                        <th scope="col">Data de Cadastro</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tarefasFiltradas as $tarefa) : ?>
                                        <tr>
                                            <td id="tarefa_<?php echo $tarefa->id; ?>"><?php echo $tarefa->tarefa; ?></td>
                                            <td><?php echo $tarefa->categoria; ?></td>
                                            <td><?php echo traduzPrioridade($tarefa->prioridade); ?></td>

                                            <td><?php echo $tarefa->prazo ? date('d/m/Y', strtotime($tarefa->prazo)) : 'Sem prazo'; ?></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($tarefa->data_cadastrado)); ?></td>
                                            <td>
                                                <button class="btn btn-warning" onclick="editar(<?php echo $tarefa->id; ?>, '<?php echo $tarefa->tarefa; ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger" onclick="remover(<?php echo $tarefa->id; ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <?php if ($tarefa->status === 'pendente') : ?>
                                                    <button class="btn btn-success" onclick="marcarRealizada(<?php echo $tarefa->id; ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-primary" onclick="arquivar(<?php echo $tarefa->id; ?>)">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php if ($tarefa->prazo && strtotime($tarefa->prazo) > time() && (strtotime($tarefa->prazo) - time() <= 86400)) : ?>
                                            <tr>
                                            </tr>
                                        <?php elseif ($tarefa->prazo && strtotime($tarefa->prazo) < time()) : ?>
                                            <tr>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var notificationContainer = document.createElement('div');
            notificationContainer.className = 'notification-container';

            document.body.appendChild(notificationContainer);

            function showNotification(message) {
                var notification = document.createElement('div');
                notification.className = 'notification';
                notification.textContent = message;
                notification.onclick = function () {
                    this.remove();
                };

                notificationContainer.appendChild(notification);

                setTimeout(function () {
                    notification.classList.add('show');
                    setTimeout(function () {
                        notification.classList.remove('show');
                        setTimeout(function () {
                            notification.remove();
                        }, 500);
                    }, 3000);
                }, 100);
            }

            
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php $position = 20; 
            ?>
            <?php foreach ($tarefasFiltradas as $tarefa) : ?>
                <?php if ($tarefa->prazo && strtotime($tarefa->prazo) > time() && (strtotime($tarefa->prazo) - time() <= 86400)) : ?>
                    <?php $position += 40; 
                    ?>
                <?php elseif ($tarefa->prazo && strtotime($tarefa->prazo) < time()) : ?>
                    <?php $position += 40; 
                    ?>
                <?php endif; ?>
            <?php endforeach; ?>

            function showNotification(message, position) {
                var notification = document.createElement('div');
                notification.className = 'notification';
                notification.textContent = message;
                notification.style.top = position + 'px'; 

                document.body.appendChild(notification);

                setTimeout(function() {
                    notification.classList.add('show');
                    setTimeout(function() {
                        notification.classList.remove('show');
                        setTimeout(function() {
                            document.body.removeChild(notification);
                        }, 500);
                    }, 3000);
                }, 100);
            }
        });
    </script>


    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>

</html>