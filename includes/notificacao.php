<?php
$paginaAtualToast = basename($_SERVER['SCRIPT_NAME']);
$notificacaoErro = $_SESSION['erro_toast'][$paginaAtualToast] ?? null;
unset($_SESSION['erro_toast'][$paginaAtualToast]);
?>
<?php if (is_string($notificacaoErro) && $notificacaoErro !== ''): ?>
    <script src="js/vendor/sweetalert2.all.min.js"></script>
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            text: <?php echo json_encode($notificacaoErro, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?>,
            showConfirmButton: false,
            showCloseButton: true,
            closeButtonAriaLabel: 'Fechar notificação',
            timer: 5000,
            timerProgressBar: true,
            background: '#121820',
            color: '#f4f6f8'
        });
    </script>
<?php endif; ?>
