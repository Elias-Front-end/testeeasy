<?php
require 'config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $birth_date = trim($_POST['birth_date'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $marital_status = trim($_POST['marital_status'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cellphone = trim($_POST['cellphone'] ?? '');
    $landline = trim($_POST['landline'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $address_number = trim($_POST['address_number'] ?? '');
    $address_complement = trim($_POST['address_complement'] ?? '');
    $neighborhood = trim($_POST['neighborhood'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $how_met = trim($_POST['how_met'] ?? '');
    $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;
    $interests = isset($_POST['interests']) ? implode(',', $_POST['interests']) : '';
    
    // Upload de Foto
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photo_path = $targetPath;
        }
    }

    if (empty($name) || empty($email) || empty($cpf) || empty($terms_accepted)) {
        $message = 'Preencha todos os campos obrigatórios e aceite os termos.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'E-mail inválido.';
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, cpf, birth_date, gender, marital_status, profession, email, cellphone, landline, cep, address, address_number, address_complement, neighborhood, city, state, interests, how_met, terms_accepted, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $cpf, $birth_date, $gender, $marital_status, $profession, $email, $cellphone, $landline, $cep, $address, $address_number, $address_complement, $neighborhood, $city, $state, $interests, $how_met, $terms_accepted, $photo_path]);
            $message = 'Cadastro realizado com sucesso!';
            $messageType = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Este e-mail ou CPF já está cadastrado.';
            } else {
                $message = 'Erro ao salvar cadastro: ' . $e->getMessage();
            }
            $messageType = 'error';
        }
    }
}

// Buscar últimos cadastros
try {
    $stmt = $pdo->query("SELECT id, name, email, created_at, photo_path FROM users ORDER BY created_at DESC LIMIT 5");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Moderno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#64748b',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100 font-sans">

<div class="fixed top-4 right-4 z-50">
    <button id="theme-toggle" class="p-2 rounded-full bg-white dark:bg-gray-800 shadow-lg text-gray-600 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary">
        <i class="fas fa-moon dark:hidden"></i>
        <i class="fas fa-sun hidden dark:inline"></i>
    </button>
</div>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    
    <!-- Header -->
    <header class="text-center mb-10">
        <h1 class="text-4xl font-bold text-primary mb-2">Cadastro de Usuário</h1>
        <p class="text-gray-500 dark:text-gray-400">Preencha suas informações para criar sua conta.</p>
        
        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-6 dark:bg-gray-700 max-w-lg mx-auto">
            <div id="progress-bar" class="bg-primary h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1" id="progress-text">0% Completo</p>
    </header>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg flex items-center gap-3 <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-300'; ?>">
            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <form id="cadastroForm" action="index.php" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 space-y-8">
        
        <!-- 1. Informações Pessoais -->
        <section>
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 border-b pb-2 border-gray-100 dark:border-gray-700">
                <span class="bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                Informações Pessoais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Foto de Perfil -->
                <div class="md:col-span-2 flex justify-center mb-4">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-100 dark:border-gray-700 bg-gray-200 flex items-center justify-center">
                            <img id="photo-preview" src="https://via.placeholder.com/150?text=Foto" class="w-full h-full object-cover hidden">
                            <i id="photo-icon" class="fas fa-user text-4xl text-gray-400"></i>
                        </div>
                        <label for="photo" class="absolute bottom-0 right-0 bg-primary text-white p-2 rounded-full cursor-pointer hover:bg-blue-600 transition shadow-lg">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="photo" name="photo" accept="image/*" class="hidden" onchange="previewImage(this)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Nome Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border" placeholder="Seu nome">
                </div>
                
                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">CPF <span class="text-red-500">*</span></label>
                    <input type="text" name="cpf" id="cpf" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border" placeholder="000.000.000-00">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Data de Nascimento <span class="text-red-500">*</span></label>
                    <input type="date" name="birth_date" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Gênero <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                        <option value="">Selecione...</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                        <option value="outro">Outro</option>
                        <option value="prefero_nao_dizer">Prefiro não dizer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Estado Civil</label>
                    <select name="marital_status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                        <option value="">Selecione...</option>
                        <option value="solteiro">Solteiro(a)</option>
                        <option value="casado">Casado(a)</option>
                        <option value="divorciado">Divorciado(a)</option>
                        <option value="viuvo">Viúvo(a)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Profissão</label>
                    <input type="text" name="profession" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                </div>
            </div>
        </section>

        <!-- 2. Contato e Endereço -->
        <section>
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 border-b pb-2 border-gray-100 dark:border-gray-700">
                <span class="bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                Contato e Endereço
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">E-mail <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-3.5 text-gray-400"></i>
                        <input type="email" name="email" required class="pl-10 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border" placeholder="exemplo@email.com">
                    </div>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Celular <span class="text-red-500">*</span></label>
                    <input type="tel" name="cellphone" id="cellphone" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border" placeholder="(00) 00000-0000">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Telefone Fixo</label>
                    <input type="tel" name="landline" id="landline" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border" placeholder="(00) 0000-0000">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">CEP <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" name="cep" id="cep" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border" placeholder="00000-000">
                        <button type="button" id="btn-busca-cep" class="absolute right-2 top-1.5 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-600 dark:hover:bg-gray-500 px-3 py-1.5 rounded transition">Buscar</button>
                    </div>
                </div>

                <div class="form-group md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Endereço <span class="text-red-500">*</span></label>
                    <input type="text" name="address" id="address" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Número <span class="text-red-500">*</span></label>
                    <input type="text" name="address_number" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Complemento</label>
                    <input type="text" name="address_complement" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Bairro <span class="text-red-500">*</span></label>
                    <input type="text" name="neighborhood" id="neighborhood" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Cidade <span class="text-red-500">*</span></label>
                    <input type="text" name="city" id="city" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium mb-1">Estado <span class="text-red-500">*</span></label>
                    <input type="text" name="state" id="state" required maxlength="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border uppercase">
                </div>
            </div>
        </section>

        <!-- 3. Preferências -->
        <section>
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2 border-b pb-2 border-gray-100 dark:border-gray-700">
                <span class="bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400 w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                Preferências
            </h2>
            
            <div class="space-y-4">
                <div class="form-group">
                    <label class="block text-sm font-medium mb-2">Interesses</label>
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="interests[]" value="tecnologia" class="rounded text-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600">
                            <span>Tecnologia</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="interests[]" value="esportes" class="rounded text-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600">
                            <span>Esportes</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="interests[]" value="musica" class="rounded text-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600">
                            <span>Música</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="interests[]" value="viagens" class="rounded text-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600">
                            <span>Viagens</span>
                        </label>
                    </div>
                </div>

                <div class="form-group max-w-md">
                    <label class="block text-sm font-medium mb-1">Como nos conheceu?</label>
                    <select name="how_met" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary transition p-2.5 border">
                        <option value="">Selecione...</option>
                        <option value="google">Google</option>
                        <option value="redes_sociais">Redes Sociais</option>
                        <option value="indicacao">Indicação</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                    <label class="inline-flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="terms_accepted" required class="rounded text-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 w-5 h-5">
                        <span class="text-sm">Li e aceito os <a href="#" class="text-primary hover:underline">Termos de Uso</a> e <a href="#" class="text-primary hover:underline">Política de Privacidade</a>. <span class="text-red-500">*</span></span>
                    </label>
                </div>
            </div>
        </section>

        <!-- Botões -->
        <div class="flex items-center justify-between pt-6">
            <button type="button" id="save-draft" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm flex items-center gap-1">
                <i class="fas fa-save"></i> Salvar Rascunho
            </button>
            <button type="submit" id="submit-btn" class="bg-primary hover:bg-blue-600 text-white font-semibold py-3 px-8 rounded-lg shadow-lg transform hover:-translate-y-0.5 transition flex items-center gap-2">
                <span>Finalizar Cadastro</span>
                <div class="loading-spinner hidden"></div>
            </button>
        </div>
    </form>

    <!-- Listagem Rápida -->
    <div class="mt-16">
        <h3 class="text-2xl font-bold mb-6 border-l-4 border-primary pl-4">Últimos Cadastros</h3>
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 uppercase text-gray-500 dark:text-gray-300 font-semibold">
                    <tr>
                        <th class="p-4">Foto</th>
                        <th class="p-4">Nome</th>
                        <th class="p-4">E-mail</th>
                        <th class="p-4">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="p-4">
                                    <?php if ($u['photo_path']): ?>
                                        <img src="<?php echo htmlspecialchars($u['photo_path']); ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 font-medium"><?php echo htmlspecialchars($u['name']); ?></td>
                                <td class="p-4 text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="p-4 text-gray-500 dark:text-gray-400"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-400">Nenhum cadastro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        // Máscaras
        $('#cpf').mask('000.000.000-00');
        $('#cep').mask('00000-000');
        $('#cellphone').mask('(00) 00000-0000');
        $('#landline').mask('(00) 0000-0000');

        // Dark Mode
        const themeToggleBtn = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;
        
        // Check local storage or system preference
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }

        themeToggleBtn.addEventListener('click', function() {
            htmlElement.classList.toggle('dark');
            if (htmlElement.classList.contains('dark')) {
                localStorage.setItem('color-theme', 'dark');
            } else {
                localStorage.setItem('color-theme', 'light');
            }
        });

        // Busca CEP
        $('#btn-busca-cep').on('click', function() {
            let cep = $('#cep').val().replace(/\D/g, '');
            if (cep.length === 8) {
                const btn = $(this);
                btn.text('...').prop('disabled', true);
                
                $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                    if (!data.erro) {
                        $('#address').val(data.logradouro);
                        $('#neighborhood').val(data.bairro);
                        $('#city').val(data.localidade);
                        $('#state').val(data.uf);
                        $('#address_number').focus();
                        updateProgress();
                    } else {
                        alert('CEP não encontrado.');
                    }
                }).always(function() {
                    btn.text('Buscar').prop('disabled', false);
                });
            } else {
                alert('Digite um CEP válido.');
            }
        });

        // Barra de Progresso
        const formInputs = document.querySelectorAll('input, select');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        function updateProgress() {
            let total = 0;
            let filled = 0;
            
            // Campos relevantes para progresso
            const requiredFields = [
                'name', 'cpf', 'birth_date', 'gender', 'email', 
                'cellphone', 'cep', 'address', 'address_number', 
                'neighborhood', 'city', 'state'
            ];

            formInputs.forEach(input => {
                if (requiredFields.includes(input.name)) {
                    total++;
                    if (input.value.trim() !== '') {
                        filled++;
                    }
                }
            });

            // Checkbox termos
            total++;
            if (document.querySelector('input[name="terms_accepted"]').checked) filled++;

            const percentage = Math.round((filled / total) * 100);
            progressBar.style.width = percentage + '%';
            progressText.innerText = percentage + '% Completo';
        }

        formInputs.forEach(input => {
            input.addEventListener('input', updateProgress);
            input.addEventListener('change', updateProgress);
        });

        // Preview de Imagem
        window.previewImage = function(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#photo-preview').attr('src', e.target.result).removeClass('hidden');
                    $('#photo-icon').addClass('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Loading no Submit
        $('#cadastroForm').on('submit', function() {
            $('#submit-btn span').text('Enviando...');
            $('#submit-btn .loading-spinner').removeClass('hidden');
            $('#submit-btn').prop('disabled', true).addClass('opacity-75 cursor-not-allowed');
        });

        // Salvar Rascunho (Local Storage)
        $('#save-draft').on('click', function() {
            const formData = {};
            $('#cadastroForm').serializeArray().forEach(item => {
                formData[item.name] = item.value;
            });
            localStorage.setItem('cadastro_draft', JSON.stringify(formData));
            alert('Rascunho salvo no seu navegador!');
        });

        // Carregar Rascunho se existir
        const savedDraft = localStorage.getItem('cadastro_draft');
        if (savedDraft) {
            if(confirm('Existe um rascunho salvo. Deseja carregar?')) {
                const data = JSON.parse(savedDraft);
                for (let key in data) {
                    $(`[name="${key}"]`).val(data[key]);
                }
                updateProgress();
            }
        }
    });
</script>
</body>
</html>
