<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3>About BloodLine</h3>
            <p>BloodLine connects blood donors with patients in urgent need. Your donation can save lives.</p>
        </div>
        <div class="footer-col">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('campaigns.index') }}">Campaigns</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Get Involved</h3>
            <ul>
                <li><a href="{{ route('register') }}">Become a Donor</a></li>
                <li><a href="{{ route('urgent.index') }}">Urgent Requests</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Contact Us</h3>
            <div class="contact-info">
                <p><i class="fas fa-phone"></i> +20-100-000-0000</p>
                <p><i class="fas fa-envelope"></i> info@bloodline.org</p>
                <p><i class="fas fa-map-marker-alt"></i> Luxor, Egypt</p>
            </div>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">&copy; {{ date('Y') }} BloodLine. All rights reserved.</div>
</footer>
