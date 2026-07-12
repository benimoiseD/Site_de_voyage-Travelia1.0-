<footer class="travelia-footer">
    <div class="footer-container">
        <!-- Logo Section -->
        <div class="footer-section footer-logo">
            <div class="footer-logo-content">
                <h3>Travelia</h3>
                <p>Explorez la RDC et l'Afrique de l'Est avec Travelia.</p>
            </div>
            <div class="footer-social">
                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
            </div>
        </div>


        <!-- Popular Destinations Section -->
        <div class="footer-section footer-destinations">
            <h4>Destinations populaires</h4>
            <ul class="footer-links">
                <li><a href="details_destination.php?id=1">Parc Virunga</a></li>
                <li><a href="details_destination.php?id=3">Lac Kivu</a></li>
                <li><a href="details_destination.php?id=5">Volcans</a></li>
                <li><a href="details_destination.php?id=7">Gombe</a></li>
                <li><a href="details_destination.php?id=6">Lac Tanganyika</a></li>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="footer-section footer-contact">
            <h4>Contact</h4>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Kinshasa, RDC</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>contact@travelia.com</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>+243 821474532</span>
            </div>
            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <span>Lun - Sam : 8h00 - 18h00</span>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <p>&copy; 2026 Travelia. Tous droits réservés.</p>
            <div class="footer-bottom-links">
                <a href="#">Conditions d'utilisation</a>
                <a href="#">Politique de confidentialité</a>
            </div>
        </div>
    </div>
</footer>

<style>
.travelia-footer {
    background: #1a1a1a;
    color: #fff;
    padding: 60px 0 0 0;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
}

.footer-section h4 {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #e97522;
}

.footer-logo-content h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 12px;
    color: #fff;
}

.footer-logo-content p {
    color: #aaa;
    line-height: 1.6;
    margin-bottom: 20px;
}

.footer-social {
    display: flex;
    gap: 12px;
}

.social-link {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: #e97522;
    transform: translateY(-3px);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #aaa;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #e97522;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    color: #aaa;
}

.contact-item i {
    color: #e97522;
    font-size: 1.1rem;
    width: 20px;
}

.footer-bottom {
    background: #111;
    padding: 20px 0;
    margin-top: 40px;
}

.footer-bottom-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.footer-bottom-content p {
    color: #888;
    margin: 0;
}

.footer-bottom-links {
    display: flex;
    gap: 24px;
}

.footer-bottom-links a {
    color: #888;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #e97522;
}

@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }

    .footer-bottom-links {
        justify-content: center;
    }
}
</style>