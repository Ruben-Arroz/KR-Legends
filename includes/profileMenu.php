<?php if (isset($_SESSION['user_email'])): ?>
    <!-- Modal Confirmar Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog logout-modal-dialog">
            <div class="modal-content logout-modal-content logout-modal-border">
                <div class="modal-header logout-modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirmar Logout</h5>
                    <button type="button" class="btn-close btn-close-white logout-btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
                </div>
                <div class="modal-body logout-modal-body">
                    Tem certeza de que deseja terminar a sessão?
                </div>
                <div class="modal-footer logout-modal-footer">
                    <!-- Botão Cancelar -->
                    <button type="button" class="btn btn-outline-light logout-btn-cancel"
                        data-bs-dismiss="modal">Cancelar</button>

                    <!-- Formulário para confirmar o logout -->
                    <form action="PHP/logout.php" method="POST" style="display:inline-block;">
                        <button type="submit" class="btn logout-btn-confirm-logout">Terminar Sessão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-menu-container">
        <!-- User profile name with link to profile page -->
        <a href="Utilizador/perfil.php" class="profile-name" title="Ver perfil">
            <?php
            // Verifique se o nome completo existe, caso contrário mostre o nome de usuário
            $displayName = !empty($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['user_name'];
            echo htmlspecialchars($displayName);
            ?>
        </a>
        <!-- Avatar dropdown trigger -->
        <div class="dropdown">
            <a class="profile-avatar-trigger" href="#" id="userMenu" role="button" data-bs-toggle="dropdown"
                aria-expanded="false" title="Menu do utilizador">
                <img src="Imagens/avatares/<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Avatar do utilizador"
                    class="rounded-circle">
            </a>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="userMenu">
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="Utilizador/perfil.php">
                        <i class="bi bi-person-circle me-2"></i> Perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="Utilizador/stats.php">
                        <i class="bi bi-bar-chart-line me-2"></i> Estatísticas
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="Utilizador/settings.php">
                        <i class="bi bi-gear me-2"></i> Definições
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="Suporte.php">
                        <i class="bi bi-question-circle me-2"></i> Ajuda & FAQ
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="Politica.php">
                        <i class="bi bi-file-earmark-text me-2"></i> Termos & Políticas
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <button class="dropdown-item d-flex align-items-center text-danger" data-bs-toggle="modal"
                        data-bs-target="#logoutModal" id="terminar">
                        <i class="bi bi-box-arrow-right me-2"></i> Terminar Sessão
                    </button>
                </li>
            </ul>
        </div>
        <!-- Ícone Administrativo (ADM ou SUPERADM) -->
        <?php
        if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['ADM', 'SUPERADM'], true)):
            if ($_SESSION['role'] === 'SUPERADM') {
                // Usa ícone de “gema” para SUPERADM (fa-solid fa-gem)
                $iconClass = 'fa-solid fa-gem';
                $ariaLabel = 'Área Super Admin';
            } else {
                // Usa ícone de “pessoa com gravata” para ADM (fa-solid fa-user-tie)
                $iconClass = 'fa-solid fa-user-tie';
                $ariaLabel = 'Área Admin';
            }
            // Link placeholder para a página administrativa
            $adminUrl = 'admin/modoADM.php';
            ?>
            <a href="<?= htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8') ?>" class="profile-admin-icon"
                title="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>"
                aria-label="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>">
                <i class="<?= $iconClass ?>" aria-hidden="true"></i>
            </a>
        <?php endif; ?>

    </div>
<?php else: ?>
    <a href="Login-Cadastro/Login.php" class="btn auth-btn auth-btn-login">
        <i class="bi bi-person"></i> <span data-translate="nav.login">Entrar</span>
    </a>
    <a href="Login-Cadastro/Cadastro.php" class="btn auth-btn auth-btn-register">
        <i class="bi bi-person-plus"></i> <span data-translate="nav.register">Criar Conta</span>
    </a>
<?php endif; ?>