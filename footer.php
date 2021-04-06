
<div class="container bg-primary">
    <footer id="footer" class="bg-primary mt-2 mb-0 pt-2 ">
        <div class="row">
            <div class="col-lg-12">
                <ul class="list-unstyled">
                    <li class="float-lg-right"><a href="#top">Back to top</a></li>

                    <li><a href="#">Support</a></li>
                    <li><a href="Declaration">#</a></li>

                </ul>
                <p>Made by <a href="#">cct3000 & Thraxelon</a>.</p>
                <p>Code released under the <a href="#">MIT's Open Source License</a>.</p>


            </div>
        </div>

    </footer>
</div>

<script src="./docs/_vendor/jquery/dist/jquery.min.js"></script>
<script src="./docs/_vendor/popper.js/dist/umd/popper.min.js"></script>
<script src="./docs/_vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="./docs/_assets/js/custom.js"></script>
<script>
    $('.dropdown-menu a.dropdown-toggle').on('click', function (e) {
        if (!$(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
        }
        var $subMenu = $(this).next('.dropdown-menu');
        $subMenu.toggleClass('show');


        $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function (e) {
            $('.dropdown-submenu .show').removeClass('show');
        });


        return false;
    });
</script>