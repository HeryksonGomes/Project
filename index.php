<?php include __DIR__ . '/layouts/header.php'; ?>
<body>
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <section class="card consulta-card border-0 shadow-sm text-center" aria-labelledby="titulo">
            <div class="card-header bg-white py-4">
                <h1 id="titulo" class="h3 mb-0">Descubra seu signo:</h1>
            </div>
            <div class="card-body p-4 p-sm-5">
                <form id="signo-form" method="POST" action="show_zodiac_sign.php">
                    <div class="mb-3">
                        <label for="data_nascimento" class="form-label">Data de nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento"
                            class="form-control" required aria-describedby="data-ajuda">
                        <div id="data-ajuda" class="form-text">Informe sua data de nascimento completa.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Descobrir</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
