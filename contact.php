<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Description" content="Contactez-nous pour toute question ou demande d'information.">
    <title>SAFARI-RDC+ | Contact</title>
    <link rel="stylesheet" href="CSS/contact.css?v=2">
</head>
<body>

<?php
$pageTitle = "SAFARI-RDC+ | Accueil";
include 'include/header.php';
?>

    <main>
        <section class="contact-section">
            <h1>Contactez-nous</h1>
            <form id="contactForm" class="contact-form"  autocomplete="off">
                <input type="text" id="nom" placeholder="Votre nom" required>
                <input type="email" id="email" placeholder="Votre email" required>
                <textarea id="message" placeholder="Votre message" rows="5" required></textarea>
                <input type="submit" value="Envoyer">
            </form>
            <div id="formMessage"></div>
        </section>
    </main>


    <script src="JS/contact.js"></script>    

    <?php include 'include/footer.php'; ?>

</body>
</html>
