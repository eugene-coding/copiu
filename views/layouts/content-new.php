<?php

use app\models\Settings;
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;

/** @var $content mixed  */
?>

<div class="content-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12" style="padding: 0">
                <section class="content">
                    <?php try {
                        echo Alert::widget();
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    } ?>
                    <?= $content ?>
                </section>
            </div>
        </div>
    </div>
</div>
<style>
    .wrapper-menu {
        cursor: pointer;
    }
</style>
<script>
    $(window).on("scroll", function () {
        if ($(window).scrollTop() > 0) {
            $(".iq-top-navbar").addClass("fixed");
        } else {
            $(".iq-top-navbar").removeClass("fixed");
        }
    });

    $(window).on("scroll", function () {
        if ($(window).scrollTop() > 0) {
            $(".white-bg-menu").addClass("sticky-menu");
        } else {
            $(".white-bg-menu").removeClass("sticky-menu");
        }
    });

    /*---------------------------------------------------------------------
       Page Menu
       -----------------------------------------------------------------------*/
    jQuery(document).on("click", ".wrapper-menu", function () {
        jQuery(this).toggleClass("open");
    });

    jQuery(document).on("click", ".wrapper-menu", function () {
        jQuery("body").toggleClass("sidebar-main");
    });

    /*---------------------------------------------------------------------
         Close  navbar Toggle
         -----------------------------------------------------------------------*/

    jQuery(".close-toggle").on("click", function () {
        jQuery(".h-collapse.navbar-collapse").collapse("hide");
    });


    $('.alert').css({ 'opacity' : 1 });
</script>
