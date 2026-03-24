<footer style="background: #dcdcdd; margin-top: 40px;">
    <p style="padding: 20px 0; text-align: center; margin: 0;">&copy; <?php echo date('Y'); ?> HCC Admin Dashboard. All
        rights reserved.</p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sidebar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.js"></script>
<script>
    $(document).ready(function () {
        $('.owl-carousel').owlCarousel({
            loop: true,
            margin: 10,
            dots: false,
            nav: true,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 1
                },
                1000: {
                    items: 1
                }
            }
        });
    });
</script>

</body>

</html>